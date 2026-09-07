<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\Assessment;
use App\Models\LearningPath;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        Assessment::query()->where('slug', 'protection-needs-assessment')->delete();

        $assessment = Assessment::updateOrCreate(
            ['slug' => 'protection-needs'],
            [
                'title' => 'Kenali Topik Proteksi yang Relevan untuk Dipelajari',
                'description' => 'Refleksi singkat mengenai tanggung jawab finansial, perlindungan yang sudah dimiliki, dan dampak finansial beberapa risiko utama.',
                'intro' => 'Jawab beberapa pertanyaan sederhana mengenai kondisi finansial dan perlindungan yang sudah kamu miliki. InsuraLab akan membantu menunjukkan topik proteksi yang mungkin relevan untuk kamu pelajari lebih lanjut.',
                'disclaimer' => 'Assessment ini bersifat edukatif. Hasil menunjukkan topik proteksi yang mungkin relevan untuk dipelajari berdasarkan jawaban yang kamu berikan dan bukan merupakan analisis keuangan pribadi, penilaian risiko, atau rekomendasi untuk membeli produk tertentu.',
                'status' => ContentStatus::Published,
                'methodology_version' => '1.0',
                'rules' => [
                    'engine' => 'deterministic_weighted_relevance',
                    'normalization' => 'earned_over_applicable_maximum',
                    'thresholds' => ['worth_exploring' => 0.30, 'highly_relevant' => 0.60],
                    'output' => 'ranked_learning_topics_only',
                    'additional_learning_title' => 'Sudah punya perlindungan?',
                    'additional_learning_description' => 'Memahami polis yang sudah dimiliki sama pentingnya dengan mempertimbangkan perlindungan baru. Pelajari manfaat, limit, pengecualian, keberlanjutan, dan proses klaimnya.',
                    'forbidden_output' => ['product_recommendation', 'provider_recommendation', 'buy_instruction'],
                ],
            ],
        );

        $assessment->questions()->delete();
        $assessment->dimensions()->delete();

        $dimensionDefinitions = [
            'life' => ['Proteksi Jiwa', 'Mempelajari dampak finansial apabila pencari nafkah meninggal, terutama bagi tanggungan dan kewajiban yang tetap berjalan.', ['asuransi-jiwa', 'human-life-value', 'manfaat-dan-uang-pertanggungan', 'pemegang-polis-tertanggung-penerima-manfaat'], 'memahami-proteksi-pribadi'],
            'health' => ['Proteksi Kesehatan', 'Mempelajari cara perlindungan biaya medis bekerja, termasuk manfaat, limit, jaringan layanan, pengecualian, dan koordinasi perlindungan yang sudah ada.', ['asuransi-kesehatan', 'reimbursement-vs-lump-sum', 'polis-asuransi', 'klaim'], 'memahami-proteksi-pribadi'],
            'critical_illness' => ['Proteksi Penyakit Kritis', 'Mempelajari dampak penyakit serius terhadap penghasilan dan kebutuhan nonmedis, serta cara manfaat santunan sekaligus bekerja.', ['penyakit-kritis', 'reimbursement-vs-lump-sum', 'klaim'], 'memahami-proteksi-pribadi'],
            'disability' => ['Proteksi Disabilitas', 'Mempelajari dampak finansial ketika kemampuan mencari penghasilan berkurang secara permanen dan cara konsep TPD meresponsnya.', ['total-permanent-disability', 'human-life-value', 'underwriting'], 'memahami-proteksi-pribadi'],
            'accident' => ['Proteksi Kecelakaan', 'Mempelajari konsekuensi finansial akibat kejadian kecelakaan serta perbedaan manfaat meninggal dan disabilitas karena kecelakaan.', ['perlindungan-kecelakaan', 'total-permanent-disability', 'polis-asuransi'], 'memahami-proteksi-pribadi'],
        ];

        $dimensions = collect();
        foreach ($dimensionDefinitions as $key => [$name, $description, $articleSlugs, $learningPathSlug]) {
            $dimension = $assessment->dimensions()->create([
                'learning_path_id' => LearningPath::where('slug', $learningPathSlug)->value('id'),
                'key' => $key,
                'name' => $name,
                'description' => $description,
                'sort_order' => $dimensions->count(),
            ]);
            $this->syncArticles($dimension->learningArticles(), $articleSlugs);
            $dimensions->put($key, $dimension);
        }

        foreach ($this->questions() as $questionOrder => $definition) {
            $question = $assessment->questions()->create([
                'key' => $definition['key'],
                'prompt' => $definition['prompt'],
                'help_text' => $definition['help_text'] ?? null,
                'type' => 'single',
                'is_required' => true,
                'sort_order' => $questionOrder,
                'depends_on_question_key' => $definition['depends_on'] ?? null,
                'applicable_option_keys' => $definition['applicable_options'] ?? null,
            ]);

            foreach ($definition['options'] as $optionOrder => $optionDefinition) {
                $option = $question->options()->create([
                    'key' => $optionDefinition['key'],
                    'label' => $optionDefinition['label'],
                    'weight' => 0,
                    'protection_category_key' => null,
                    'metadata' => null,
                    'sort_order' => $optionOrder,
                ]);

                foreach ($optionDefinition['impacts'] ?? [] as $dimensionKey => $impact) {
                    $option->impacts()->create([
                        'assessment_dimension_id' => $dimensions->get($dimensionKey)->id,
                        'weight' => $impact[0],
                        'reason_code' => $impact[1],
                        'reason' => $impact[2],
                    ]);
                }

                $this->syncArticles($option->learningArticles(), $optionDefinition['articles'] ?? []);
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function questions(): array
    {
        return [
            [
                'key' => 'dependents',
                'prompt' => 'Apakah ada orang yang bergantung pada penghasilanmu?',
                'help_text' => 'Kamu tidak perlu menyebutkan siapa mereka.',
                'options' => [
                    ['key' => 'none', 'label' => 'Tidak'],
                    ['key' => 'one', 'label' => 'Ya, 1 orang', 'impacts' => ['life' => [2, 'HAS_DEPENDENTS', 'Kamu menunjukkan bahwa ada orang lain yang bergantung pada penghasilanmu.']]],
                    ['key' => 'two_to_three', 'label' => 'Ya, 2–3 orang', 'impacts' => ['life' => [3, 'HAS_DEPENDENTS', 'Kamu menunjukkan bahwa ada beberapa orang yang bergantung pada penghasilanmu.']]],
                    ['key' => 'more_than_three', 'label' => 'Ya, lebih dari 3 orang', 'impacts' => ['life' => [3, 'HAS_DEPENDENTS', 'Kamu menunjukkan bahwa ada beberapa orang yang bergantung pada penghasilanmu.']]],
                ],
            ],
            [
                'key' => 'income_role',
                'prompt' => 'Seberapa penting penghasilanmu bagi kebutuhan rumah tangga?',
                'options' => [
                    ['key' => 'not_primary', 'label' => 'Bukan sumber utama', 'impacts' => ['disability' => [1, 'INCOME_ROLE', 'Penghasilanmu tetap merupakan salah satu bagian dari ketahanan finansialmu.']]],
                    ['key' => 'one_of_sources', 'label' => 'Salah satu sumber penghasilan', 'impacts' => ['life' => [1, 'INCOME_ROLE', 'Penghasilanmu ikut mendukung kebutuhan rumah tangga.'], 'critical_illness' => [1, 'INCOME_ROLE', 'Gangguan penghasilan dapat memengaruhi sebagian kebutuhan rutin.'], 'disability' => [1, 'INCOME_ROLE', 'Kemampuan mencari penghasilan merupakan bagian dari ketahanan rumah tangga.']]],
                    ['key' => 'primary', 'label' => 'Sumber penghasilan utama', 'impacts' => ['life' => [3, 'PRIMARY_INCOME_SOURCE', 'Penghasilanmu berperan utama dalam memenuhi kebutuhan rumah tangga.'], 'critical_illness' => [2, 'PRIMARY_INCOME_SOURCE', 'Berhentinya penghasilan utama selama pemulihan dapat berdampak pada kebutuhan rutin.'], 'disability' => [3, 'PRIMARY_INCOME_SOURCE', 'Kemampuanmu bekerja berperan penting bagi penghasilan rumah tangga.']]],
                    ['key' => 'only_source', 'label' => 'Satu-satunya sumber penghasilan', 'impacts' => ['life' => [4, 'SOLE_INCOME_SOURCE', 'Penghasilanmu merupakan satu-satunya sumber untuk kebutuhan rumah tangga.'], 'critical_illness' => [3, 'SOLE_INCOME_SOURCE', 'Jeda bekerja selama pemulihan dapat menghentikan sumber penghasilan rumah tangga.'], 'disability' => [4, 'SOLE_INCOME_SOURCE', 'Kemampuanmu bekerja menopang seluruh penghasilan rumah tangga.']]],
                ],
            ],
            [
                'key' => 'emergency_buffer',
                'prompt' => 'Jika penghasilan utama berhenti, kira-kira berapa lama kebutuhan rutin dapat ditutupi dengan dana yang tersedia?',
                'options' => [
                    ['key' => 'under_one_month', 'label' => 'Kurang dari 1 bulan', 'impacts' => ['life' => [2, 'LIMITED_EMERGENCY_BUFFER', 'Dana yang tersedia mungkin hanya menopang kebutuhan rutin dalam waktu singkat.'], 'critical_illness' => [2, 'LIMITED_EMERGENCY_BUFFER', 'Jeda bekerja selama beberapa bulan dapat memberi tekanan pada dana yang tersedia.'], 'disability' => [2, 'LIMITED_EMERGENCY_BUFFER', 'Dana yang tersedia mungkin terbatas jika kemampuan bekerja berubah.']]],
                    ['key' => 'one_to_three', 'label' => '1–3 bulan', 'impacts' => ['life' => [1, 'SHORT_EMERGENCY_BUFFER', 'Dana yang tersedia memiliki jangka waktu terbatas jika penghasilan berhenti.'], 'critical_illness' => [2, 'SHORT_EMERGENCY_BUFFER', 'Masa pemulihan beberapa bulan dapat melampaui dana yang tersedia.'], 'disability' => [2, 'SHORT_EMERGENCY_BUFFER', 'Perubahan kemampuan bekerja dapat berlangsung lebih lama daripada dana yang tersedia.']]],
                    ['key' => 'three_to_six', 'label' => '3–6 bulan', 'impacts' => ['critical_illness' => [1, 'MODERATE_EMERGENCY_BUFFER', 'Masa pemulihan yang panjang masih dapat memengaruhi dana untuk kebutuhan rutin.'], 'disability' => [1, 'MODERATE_EMERGENCY_BUFFER', 'Dampak permanen pada kemampuan bekerja dapat berlangsung lebih lama dari dana cadangan.']]],
                    ['key' => 'six_to_twelve', 'label' => '6–12 bulan'],
                    ['key' => 'over_twelve', 'label' => 'Lebih dari 12 bulan'],
                    ['key' => 'unknown', 'label' => 'Tidak tahu', 'impacts' => ['life' => [1, 'UNKNOWN_BUFFER', 'Memahami berapa lama kebutuhan rutin dapat ditopang adalah fondasi untuk mempelajari proteksi jiwa.'], 'critical_illness' => [1, 'UNKNOWN_BUFFER', 'Memetakan dana untuk masa pemulihan dapat membantu memahami proteksi penyakit kritis.'], 'disability' => [1, 'UNKNOWN_BUFFER', 'Memetakan sumber dana alternatif dapat membantu memahami dampak perubahan kemampuan bekerja.']]],
                ],
            ],
            [
                'key' => 'health_coverage',
                'prompt' => 'Apakah kamu memiliki perlindungan untuk biaya perawatan medis?',
                'help_text' => 'BPJS dan perlindungan dari perusahaan merupakan bagian penting dari proteksi yang sudah dimiliki.',
                'options' => [
                    ['key' => 'none', 'label' => 'Belum', 'impacts' => ['health' => [3, 'NO_HEALTH_COVERAGE', 'Kamu menyampaikan belum memiliki perlindungan untuk biaya perawatan medis.']]],
                    ['key' => 'employer_or_bpjs', 'label' => 'Ada dari perusahaan / BPJS', 'impacts' => ['health' => [1, 'EXISTING_PUBLIC_OR_EMPLOYER_COVERAGE', 'Perlindungan dari BPJS atau perusahaan layak dipahami manfaat, limit, jaringan layanan, dan keberlanjutannya.']], 'articles' => ['polis-asuransi', 'asuransi-kesehatan', 'klaim']],
                    ['key' => 'private', 'label' => 'Ada asuransi kesehatan pribadi', 'impacts' => ['health' => [1, 'EXISTING_HEALTH_COVERAGE', 'Perlindungan kesehatan yang sudah dimiliki tetap penting untuk dipahami cara kerja dan batasannya.']], 'articles' => ['polis-asuransi', 'asuransi-kesehatan']],
                    ['key' => 'multiple', 'label' => 'Memiliki lebih dari satu sumber perlindungan', 'impacts' => ['health' => [1, 'MULTIPLE_HEALTH_COVERAGE', 'Memahami koordinasi manfaat membantu melihat cara beberapa sumber perlindungan bekerja bersama.']], 'articles' => ['polis-asuransi', 'asuransi-kesehatan', 'klaim']],
                    ['key' => 'unknown', 'label' => 'Tidak yakin dengan perlindungan yang dimiliki', 'impacts' => ['health' => [1, 'UNSURE_HEALTH_COVERAGE', 'Kamu dapat mulai dengan memahami manfaat dan batas perlindungan kesehatan yang sudah tersedia.']], 'articles' => ['polis-asuransi', 'asuransi-kesehatan', 'klaim']],
                ],
            ],
            [
                'key' => 'dependent_health_coverage',
                'prompt' => 'Apakah biaya kesehatan keluarga atau tanggunganmu juga memiliki perlindungan?',
                'depends_on' => 'dependents',
                'applicable_options' => ['one', 'two_to_three', 'more_than_three'],
                'options' => [
                    ['key' => 'none', 'label' => 'Belum', 'impacts' => ['health' => [2, 'DEPENDENTS_WITHOUT_HEALTH_COVERAGE', 'Biaya kesehatan tanggungan merupakan dampak finansial yang ingin kamu petakan.']]],
                    ['key' => 'partial', 'label' => 'Sebagian', 'impacts' => ['health' => [1, 'PARTIAL_DEPENDENT_COVERAGE', 'Sebagian tanggungan memiliki perlindungan; memahami cakupannya dapat membantu pembelajaran selanjutnya.']]],
                    ['key' => 'yes', 'label' => 'Ya', 'impacts' => ['health' => [1, 'DEPENDENT_COVERAGE_EXISTS', 'Perlindungan kesehatan keluarga yang sudah ada tetap perlu dipahami manfaat dan batasannya.']], 'articles' => ['polis-asuransi', 'asuransi-kesehatan']],
                    ['key' => 'unknown', 'label' => 'Tidak tahu', 'impacts' => ['health' => [1, 'UNKNOWN_DEPENDENT_COVERAGE', 'Memeriksa perlindungan kesehatan tanggungan dapat menjadi langkah belajar yang berguna.']], 'articles' => ['polis-asuransi', 'asuransi-kesehatan']],
                ],
            ],
            [
                'key' => 'financial_obligations',
                'prompt' => 'Apakah kamu memiliki kewajiban finansial jangka menengah atau panjang?',
                'help_text' => 'Misalnya cicilan rumah, pinjaman, biaya pendidikan, atau kebutuhan keluarga—tanpa menyebutkan nominal.',
                'options' => [
                    ['key' => 'none', 'label' => 'Tidak'],
                    ['key' => 'small', 'label' => 'Ada tetapi relatif kecil', 'impacts' => ['life' => [1, 'FINANCIAL_OBLIGATIONS', 'Sebagian kewajiban finansial tetap berjalan bila penghasilan terhenti.'], 'disability' => [1, 'FINANCIAL_OBLIGATIONS', 'Kewajiban yang berjalan dapat terdampak bila kemampuan bekerja berubah.']]],
                    ['key' => 'significant', 'label' => 'Ada dan cukup penting bagi kondisi finansial keluarga', 'impacts' => ['life' => [3, 'SIGNIFICANT_FINANCIAL_OBLIGATIONS', 'Kewajiban finansial penting dapat tetap berjalan ketika pencari nafkah meninggal.'], 'disability' => [2, 'SIGNIFICANT_FINANCIAL_OBLIGATIONS', 'Kewajiban finansial penting dapat tetap berjalan jika kemampuan bekerja berkurang.']]],
                ],
            ],
            [
                'key' => 'serious_illness_impact',
                'prompt' => 'Jika kamu tidak dapat bekerja selama beberapa bulan karena penyakit serius, seberapa besar dampaknya terhadap kondisi keuangan?',
                'help_text' => 'Pertanyaan ini membahas dampak finansial, bukan riwayat atau diagnosis kesehatan.',
                'options' => [
                    ['key' => 'small', 'label' => 'Kecil'],
                    ['key' => 'moderate', 'label' => 'Sedang', 'impacts' => ['critical_illness' => [2, 'MODERATE_INCOME_INTERRUPTION_IMPACT', 'Jeda bekerja beberapa bulan dapat memberi dampak sedang pada kondisi keuanganmu.'], 'disability' => [1, 'INCOME_INTERRUPTION_IMPACT', 'Perubahan kemampuan bekerja dapat memengaruhi arus penghasilanmu.']]],
                    ['key' => 'large', 'label' => 'Besar', 'impacts' => ['critical_illness' => [3, 'HIGH_INCOME_INTERRUPTION_IMPACT', 'Jeda bekerja beberapa bulan dapat memberi dampak besar pada kondisi keuanganmu.'], 'disability' => [2, 'HIGH_INCOME_INTERRUPTION_IMPACT', 'Berkurangnya kemampuan bekerja dapat memberi dampak besar pada penghasilanmu.']]],
                    ['key' => 'very_large', 'label' => 'Sangat besar', 'impacts' => ['critical_illness' => [4, 'VERY_HIGH_INCOME_INTERRUPTION_IMPACT', 'Jeda bekerja beberapa bulan dapat memberi dampak sangat besar pada kondisi keuanganmu.'], 'disability' => [2, 'VERY_HIGH_INCOME_INTERRUPTION_IMPACT', 'Berkurangnya kemampuan bekerja dapat memberi dampak besar pada penghasilanmu.']]],
                    ['key' => 'unknown', 'label' => 'Tidak tahu', 'impacts' => ['critical_illness' => [1, 'UNKNOWN_ILLNESS_IMPACT', 'Mempelajari kebutuhan selama masa pemulihan dapat membantu memetakan dampak finansial penyakit serius.'], 'disability' => [1, 'UNKNOWN_ILLNESS_IMPACT', 'Memetakan dampak perubahan kemampuan bekerja dapat menjadi fondasi pembelajaran.']]],
                ],
            ],
            [
                'key' => 'alternative_income',
                'prompt' => 'Jika kemampuan untuk bekerja berkurang secara permanen, apakah sumber pendapatan lain tersedia?',
                'options' => [
                    ['key' => 'adequate', 'label' => 'Ya, cukup memadai'],
                    ['key' => 'limited', 'label' => 'Ada tetapi terbatas', 'impacts' => ['disability' => [2, 'LIMITED_ALTERNATIVE_INCOME', 'Sumber pendapatan alternatifmu mungkin terbatas jika kemampuan bekerja berkurang.']]],
                    ['key' => 'none', 'label' => 'Tidak ada', 'impacts' => ['disability' => [4, 'NO_ALTERNATIVE_INCOME', 'Kamu menunjukkan tidak ada sumber pendapatan lain jika kemampuan bekerja berkurang.']]],
                    ['key' => 'unknown', 'label' => 'Tidak tahu', 'impacts' => ['disability' => [1, 'UNKNOWN_ALTERNATIVE_INCOME', 'Memetakan sumber pendapatan alternatif dapat menjadi fondasi pembelajaran proteksi disabilitas.']]],
                ],
            ],
            [
                'key' => 'accident_interest',
                'prompt' => 'Apakah dampak finansial kecelakaan menjadi topik yang ingin kamu pahami?',
                'help_text' => 'Assessment ini tidak menilai risiko pekerjaan atau aktivitasmu.',
                'options' => [
                    ['key' => 'not_much', 'label' => 'Tidak terlalu'],
                    ['key' => 'maybe', 'label' => 'Mungkin', 'impacts' => ['accident' => [2, 'ACCIDENT_LEARNING_INTEREST', 'Kamu tertarik memahami kemungkinan dampak finansial akibat kecelakaan.']]],
                    ['key' => 'yes', 'label' => 'Ya', 'impacts' => ['accident' => [3, 'ACCIDENT_LEARNING_INTEREST', 'Dampak finansial akibat kecelakaan merupakan topik yang ingin kamu pahami.']]],
                    ['key' => 'unknown', 'label' => 'Tidak yakin', 'impacts' => ['accident' => [1, 'UNKNOWN_ACCIDENT_INTEREST', 'Konsep dasar perlindungan kecelakaan dapat membantu kamu memahami ruang lingkupnya.']]],
                ],
            ],
            [
                'key' => 'insurance_understanding',
                'prompt' => 'Seberapa yakin kamu memahami perlindungan asuransi yang saat ini kamu miliki?',
                'options' => [
                    ['key' => 'no_insurance', 'label' => 'Belum memiliki asuransi', 'articles' => ['apa-itu-asuransi', 'polis-asuransi']],
                    ['key' => 'not_understood', 'label' => 'Tidak memahami', 'articles' => ['polis-asuransi', 'manfaat-dan-uang-pertanggungan', 'klaim']],
                    ['key' => 'slightly', 'label' => 'Sedikit memahami', 'articles' => ['polis-asuransi', 'manfaat-dan-uang-pertanggungan', 'klaim']],
                    ['key' => 'understood', 'label' => 'Cukup memahami'],
                    ['key' => 'very_understood', 'label' => 'Sangat memahami'],
                ],
            ],
            [
                'key' => 'primary_concern',
                'prompt' => 'Apa risiko finansial yang paling ingin kamu pahami?',
                'help_text' => 'Pilihan ini hanya memberi dorongan kecil dan tidak menentukan hasil sendirian.',
                'options' => [
                    ['key' => 'health_costs', 'label' => 'Biaya kesehatan', 'impacts' => ['health' => [1, 'PRIMARY_CONCERN', 'Biaya kesehatan adalah topik yang paling ingin kamu pahami.']]],
                    ['key' => 'income_loss', 'label' => 'Kehilangan penghasilan', 'impacts' => ['disability' => [1, 'PRIMARY_CONCERN', 'Dampak kehilangan penghasilan adalah topik yang paling ingin kamu pahami.']]],
                    ['key' => 'critical_illness', 'label' => 'Dampak penyakit kritis', 'impacts' => ['critical_illness' => [1, 'PRIMARY_CONCERN', 'Dampak penyakit kritis adalah topik yang paling ingin kamu pahami.']]],
                    ['key' => 'family_after_death', 'label' => 'Perlindungan keluarga jika saya meninggal', 'impacts' => ['life' => [1, 'PRIMARY_CONCERN', 'Keberlanjutan finansial keluarga adalah topik yang paling ingin kamu pahami.']]],
                    ['key' => 'accident', 'label' => 'Kecelakaan', 'impacts' => ['accident' => [1, 'PRIMARY_CONCERN', 'Dampak finansial kecelakaan adalah topik yang paling ingin kamu pahami.']]],
                    ['key' => 'unknown', 'label' => 'Belum tahu'],
                ],
            ],
        ];
    }

    private function syncArticles($relation, array $slugs): void
    {
        $ids = Article::query()->public()->general()->whereIn('slug', $slugs)->pluck('id', 'slug');
        $sync = [];
        foreach ($slugs as $order => $slug) {
            if ($ids->has($slug)) {
                $sync[$ids->get($slug)] = ['sort_order' => $order];
            }
        }
        $relation->sync($sync);
    }
}
