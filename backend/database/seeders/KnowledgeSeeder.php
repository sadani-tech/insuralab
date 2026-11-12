<?php

namespace Database\Seeders;

use App\Models\GlossaryTerm;
use App\Models\KnowledgeEntry;
use App\Models\KnowledgeRelation;
use App\Models\Source;
use App\Models\SourcePassage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Idempotent insert of first-party learning material. Never imports restricted PDFs. */
class KnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/knowledge.json')), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($data) {
            $sources = [];
            foreach ($data['sources'] as $s) {
                $sources[$s['key']] = Source::firstOrCreate(['title' => $s['title']], [
                    'publisher' => $s['publisher'], 'url' => $s['url'], 'notes' => $s['notes'],
                    'access_level' => 'public', 'reference_period' => 'Catatan belajar November 2026; versi sumber perlu dicocokkan',
                ]);
            }
            $passages = [];
            foreach ($data['passages'] as $p) {
                $passages[$p['key']] = SourcePassage::firstOrCreate(['key' => $p['key']], [
                    'source_id' => $sources[$p['source']]->id, 'version' => $p['version'],
                    'locator' => $p['locator'], 'body' => $p['body'], 'published' => true,
                ]);
            }
            $terms = [];
            foreach ($data['nodes'] as $n) {
                $term = GlossaryTerm::firstOrNew(['slug' => $n['slug']]);
                // A term is the one canonical identity. Existing detailed prose is retained.
                if (! $term->exists) {
                    $term->fill(['term' => $n['title'], 'short_definition' => $n['summary'],
                        'definition' => $n['summary'], 'category' => $n['group'], 'reference_status' => 'needs_review']);
                }
                $meta = $term->metadata ?? [];
                $firstImport = ! array_key_exists('explorer_summary', $meta);
                if ($firstImport) {
                    $meta['explorer_group'] = $n['group'];
                    $meta['explorer_summary'] = $n['summary'];
                }
                $meta['explorer_title'] ??= $n['title'];
                $term->forceFill(['metadata' => $meta, 'explorer_published' => $firstImport ? true : $term->explorer_published])->save();
                $term->sources()->syncWithoutDetaching([$sources['editorial']->id => ['note' => 'Rujukan konsep: catatan editorial; belum ditinjau ahli independen.']]);
                $terms[$n['slug']] = $term;
            }
            foreach ($data['edges'] as $e) {
                KnowledgeRelation::firstOrCreate(['from_id' => $terms[$e['source']]->id, 'to_id' => $terms[$e['target']]->id], [
                    'source_passage_id' => $passages[$e['passage']]->id,
                    'label' => $e['label'], 'explanation' => $e['explanation'], 'published' => true,
                ]);
            }
            foreach ($data['entries'] as $e) {
                $entry = KnowledgeEntry::firstOrCreate(['slug' => $e['slug']], [
                    'kind' => $e['kind'], 'title' => $e['title'], 'summary' => $e['summary'],
                    'body' => $e['body'], 'steps' => $e['steps'], 'keywords' => $e['keywords'],
                    'source_passage_id' => $passages[$e['passage']]->id, 'published' => true,
                ]);
                $entry->terms()->syncWithoutDetaching(array_map(fn ($slug) => $terms[$slug]->id, $e['terms']));
            }
            $provider = \App\Models\Provider::firstOrCreate(['slug' => 'allianz-life-indonesia'], [
                'name' => 'PT Asuransi Allianz Life Indonesia', 'status' => 'active',
                'description' => 'Entitas penerbit yang tercantum pada salinan RIPLAY Allianz Preferred Medical. Ini identitas studi dokumen, bukan profil korporat terkini.',
                'metadata' => ['explorer_enabled' => true],
            ]);
            $category = \App\Models\ProductCategory::firstOrCreate(['slug' => 'medical-hospitalisation'], ['name' => 'Medical / Hospitalisation', 'kind' => 'base']);
            $product = \App\Models\Product::firstOrCreate(['slug' => 'allianz-preferred-medical'], [
                'name' => 'Allianz Preferred Medical', 'provider_id' => $provider->id,
                'product_category_id' => $category->id, 'status' => 'published',
                'short_description' => 'Studi baca struktur RIPLAY kesehatan, bukan rekomendasi pembelian atau konfirmasi manfaat terkini.',
                'description' => 'Gunakan panduan studi baca untuk menemukan penerbit, manfaat, risiko, biaya, dan ketentuan. Versi lokal perlu dicocokkan dengan dokumen resmi.',
                'reference_status' => 'needs_review', 'reference_period' => 'Salinan lokal; versi perlu dicocokkan',
                'metadata' => ['explorer_enabled' => true],
            ]);
            $provider->sources()->syncWithoutDetaching([$sources['apm']->id]);
            $product->sources()->syncWithoutDetaching([$sources['apm']->id]);
        });
    }
}
