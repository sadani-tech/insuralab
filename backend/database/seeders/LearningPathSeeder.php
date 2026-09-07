<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\LearningPath;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class LearningPathSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            $this->path('dasar-dasar-asuransi', 'Dasar-Dasar Asuransi', 'Urutan fondasi untuk memahami risiko, cara kerja asuransi, polis, para pihak, manfaat, dan klaim.', 'beginner', 55, [
                ['article', 'apa-itu-risiko'], ['article', 'apa-itu-asuransi'], ['article', 'cara-kerja-asuransi'],
                ['article', 'premi-dan-kontribusi'], ['article', 'polis-asuransi'], ['article', 'manfaat-dan-uang-pertanggungan'],
                ['article', 'pemegang-polis-tertanggung-penerima-manfaat'], ['article', 'klaim'], ['quiz', 'dasar-dasar-asuransi'],
            ]),
            $this->path('memahami-proteksi-pribadi', 'Memahami Proteksi Pribadi', 'Pelajari bagaimana risiko jiwa, kesehatan, penyakit kritis, disabilitas, dan kecelakaan berdampak pada kondisi finansial.', 'beginner', 50, [
                ['article', 'asuransi-jiwa'], ['article', 'human-life-value'], ['article', 'asuransi-kesehatan'],
                ['article', 'penyakit-kritis'], ['article', 'total-permanent-disability'], ['article', 'perlindungan-kecelakaan'],
                ['article', 'rider-manfaat-tambahan'], ['quiz', 'asuransi-jiwa-dan-proteksi'],
            ]),
            $this->path('memahami-produk-modern', 'Memahami Produk Modern', 'Bangun fondasi sebelum mempelajari PAYDI: struktur perlindungan, komponen investasi, biaya, risiko, rider, dan keberlanjutan polis.', 'intermediate', 45, [
                ['article', 'apa-itu-asuransi'], ['article', 'paydi-unit-link'], ['article', 'premi-dan-kontribusi'],
                ['article', 'manfaat-dan-uang-pertanggungan'], ['article', 'rider-manfaat-tambahan'], ['article', 'polis-asuransi'],
                ['quiz', 'paydi-unit-link'],
            ]),
            $this->path('asuransi-syariah', 'Asuransi Syariah', 'Pahami prinsip saling membantu, risk sharing, dana tabarru’, ujrah, akad pengelolaan, dan surplus underwriting.', 'intermediate', 30, [
                ['article', 'apa-itu-risiko'], ['article', 'apa-itu-asuransi'], ['article', 'asuransi-syariah'],
                ['article', 'risk-sharing-vs-risk-transfer'], ['article', 'premi-dan-kontribusi'], ['quiz', 'asuransi-syariah'],
            ]),
            $this->path('operasi-dan-etika-asuransi', 'Insurance Operations & Ethics', 'Ikuti siklus pengajuan, underwriting, penerbitan, pelayanan, klaim, perilaku penjualan, dan perlindungan nasabah.', 'intermediate', 65, [
                ['article', 'proses-bisnis-asuransi'], ['article', 'underwriting'], ['article', 'polis-asuransi'], ['article', 'klaim'],
                ['article', 'peran-agen-asuransi'], ['article', 'etika-penjualan-asuransi'], ['article', 'dasar-aml-kyc'],
                ['article', 'perlindungan-data-nasabah'], ['quiz', 'underwriting-dan-proses-bisnis'], ['quiz', 'etika-dan-compliance'],
            ]),
        ];

        foreach ($definitions as $sortOrder => $definition) {
            $path = LearningPath::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'title' => $definition['title'],
                    'description' => $definition['description'],
                    'level' => $definition['level'],
                    'estimated_duration_minutes' => $definition['duration'],
                    'status' => ContentStatus::Published,
                    'sort_order' => $sortOrder,
                ],
            );

            $path->items()->delete();
            foreach ($definition['items'] as $position => [$type, $slug]) {
                $articleId = $type === 'article' ? Article::where('slug', $slug)->value('id') : null;
                $quizId = $type === 'quiz' ? Quiz::where('slug', $slug)->value('id') : null;
                if (($type === 'article' && ! $articleId) || ($type === 'quiz' && ! $quizId)) {
                    continue;
                }

                $path->items()->create([
                    'item_type' => $type,
                    'article_id' => $articleId,
                    'quiz_id' => $quizId,
                    'position' => $position,
                ]);
            }
        }

        LearningPath::query()
            ->whereNotIn('slug', collect($definitions)->pluck('slug'))
            ->update(['status' => ContentStatus::Archived]);
    }

    /** @return array<string, mixed> */
    private function path(string $slug, string $title, string $description, string $level, int $duration, array $items): array
    {
        return compact('slug', 'title', 'description', 'level', 'duration', 'items');
    }
}
