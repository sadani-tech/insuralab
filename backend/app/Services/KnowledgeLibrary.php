<?php

namespace App\Services;

use App\Models\GlossaryTerm;
use App\Models\KnowledgeEntry;
use App\Models\KnowledgeRelation;
use App\Models\SourcePassage;

/** Public, bounded read model. It never reads agent records or calls an AI service. */
class KnowledgeLibrary
{
    public function snapshot(string $query = ''): array
    {
        $passages = SourcePassage::public()->with('source')->orderBy('id')->limit(100)->get();
        $passageIds = $passages->pluck('id');
        $terms = GlossaryTerm::withPublicEvidence()->where('explorer_published', true)
            ->whereNull('provider_id')
            ->whereHas('sources', fn ($q) => $q->where('access_level', 'public')->whereHas('passages', fn ($p) => $p->public()))
            ->orderBy('id')->limit(80)->get();
        $termIds = $terms->pluck('id');
        $entries = KnowledgeEntry::public()->with('terms:id,slug')
            ->when($query !== '', function ($q) use ($query) {
                $needle = '%'.addcslashes($query, '%_\\').'%';
                $q->where(fn ($q) => $q->where('title', 'ilike', $needle)
                    ->orWhere('summary', 'ilike', $needle)->orWhere('body', 'ilike', $needle)
                    ->orWhereRaw('keywords::text ILIKE ?', [$needle]));
            })->orderBy('id')->limit(80)->get();
        $relations = KnowledgeRelation::where('published', true)
            ->whereIn('from_id', $termIds)->whereIn('to_id', $termIds)
            ->whereIn('source_passage_id', $passageIds)->orderBy('id')->limit(200)->get();

        $extraNodes = collect();
        $extraEdges = collect();
        foreach ($passages->unique('source_id') as $passage) {
            $extraNodes->push($this->entityNode(-$passage->source_id, 'sumber-'.$passage->source_id, $passage->source->title, 'source', $passage->body, $passage->id));
        }
        foreach ([\App\Models\Provider::public()->where('metadata->explorer_enabled', true)->with('sources')->limit(10)->get(), \App\Models\Product::public()->where('metadata->explorer_enabled', true)->with('sources')->limit(20)->get()] as $entities) {
            foreach ($entities as $entity) {
                $passage = $passages->first(fn ($p) => $entity->sources->contains('id', $p->source_id));
                if (! $passage) {
                    continue;
                }
                $kind = $entity instanceof \App\Models\Provider ? 'provider' : 'product';
                $id = -($kind === 'provider' ? 100000 : 200000) - $entity->id;
                $extraNodes->push($this->entityNode($id, $kind.'-'.$entity->slug, $entity->name, $kind, $entity->short_description ?? $entity->description ?? '', $passage->id));
                $extraEdges->push(['id' => $id, 'source' => $id, 'target' => -$passage->source_id, 'label' => 'Tercantum dalam sumber', 'explanation' => 'Identitas entitas atau produk dibaca dari sumber ini. Baca versi dan keterbatasannya; hubungan ini bukan rekomendasi.', 'passage_id' => $passage->id]);
            }
        }

        $extraNodes = $extraNodes->take(40);
        $visibleExtraIds = $extraNodes->pluck('id');
        $extraEdges = $extraEdges->filter(fn ($edge) => $visibleExtraIds->contains($edge['source']) && $visibleExtraIds->contains($edge['target']))->take(40)->values();

        return [
            'nodes' => $terms->map(fn ($t) => [
                'id' => $t->id, 'slug' => $t->slug, 'title' => $t->metadata['explorer_title'] ?? $t->term,
                'summary' => $t->metadata['explorer_summary'] ?? $t->short_definition,
                'body' => $t->metadata['explorer_summary'] ?? $t->definition,
                'group' => $t->metadata['explorer_group'] ?? 'dasar',
                'aliases' => [$t->term, ...($t->metadata['aliases'] ?? [])],
                'reference_status' => 'needs_review',
                'reviewed_at' => null,
                'kind' => $t->slug === 'risiko' ? 'risk' : (($t->metadata['explorer_group'] ?? '') === 'proteksi' ? 'protection' : (($t->metadata['explorer_group'] ?? '') === 'proses' ? 'process' : 'concept')),
            ])->concat($extraNodes)->values(),
            'edges' => $relations->map(fn ($e) => [
                'id' => $e->id, 'source' => $e->from_id, 'target' => $e->to_id,
                'label' => $e->label, 'explanation' => $e->explanation,
                'passage_id' => $e->source_passage_id,
            ])->concat($extraEdges)->values(),
            'entries' => $entries->map(fn ($e) => [
                'slug' => $e->slug, 'kind' => $e->kind, 'title' => $e->title,
                'summary' => $e->summary, 'body' => $e->body, 'steps' => $e->steps ?? [],
                'keywords' => $e->keywords ?? [], 'terms' => $e->terms->whereIn('id', $termIds)->pluck('slug')->values(),
                'passage_id' => $e->source_passage_id,
            ])->values(),
            'passages' => $passages->map(fn ($p) => [
                'id' => $p->id, 'title' => $p->source->title, 'publisher' => $p->source->publisher,
                'url' => $this->safeUrl($p->source->url), 'version' => $p->version,
                'locator' => $p->locator, 'body' => $p->body,
            ])->values(),
            'ai' => ['available' => false, 'mode' => 'curated'],
            'limits' => ['nodes' => 120, 'edges' => 240, 'entries' => 80],
        ];
    }

    private function entityNode(int $id, string $slug, string $title, string $kind, string $body, int $passageId): array
    {
        return ['id' => $id, 'slug' => $slug, 'title' => $title, 'kind' => $kind,
            'summary' => mb_strimwidth($body, 0, 240, '…'), 'body' => $body, 'group' => 'proteksi', 'aliases' => [],
            'reference_status' => 'needs_review', 'reviewed_at' => null, 'passage_id' => $passageId];
    }

    private function safeUrl(?string $url): ?string
    {
        return $url && preg_match('#^https://#i', $url) ? $url : null;
    }
}
