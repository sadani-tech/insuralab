<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->quizzes() as $data) {
            $quiz = Quiz::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => Category::where('slug', $data['category'])->value('id'),
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'level' => $data['level'],
                    'estimated_duration_minutes' => $data['duration'],
                    'status' => ContentStatus::Published,
                ],
            );

            $quiz->questions()->delete();
            foreach ($data['questions'] as $questionIndex => $questionData) {
                $question = $quiz->questions()->create([
                    'key' => $questionData['key'],
                    'prompt' => $questionData['prompt'],
                    'explanation' => $questionData['explanation'],
                    'type' => 'single',
                    'sort_order' => $questionIndex,
                ]);

                foreach ($questionData['options'] as $optionIndex => $option) {
                    $isCorrect = $option['key'] === $questionData['correct'];
                    $question->options()->create([
                        'key' => $option['key'],
                        'label' => $option['label'],
                        'is_correct' => $isCorrect,
                        'explanation' => ($isCorrect ? 'Tepat. ' : 'Belum tepat. ').$questionData['explanation'],
                        'sort_order' => $optionIndex,
                    ]);
                }

                $articleIds = Article::query()->whereIn('slug', $questionData['articles'])->pluck('id');
                $question->relatedArticles()->sync(
                    $articleIds->mapWithKeys(fn ($id, $index) => [$id => ['sort_order' => $index]]),
                );
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function quizzes(): array
    {
        return [
            $this->quiz('dasar-dasar-asuransi', 'Dasar-Dasar Asuransi', 'fundamentals', 'beginner', 8, 'Uji pemahaman tentang risiko, premi, polis, para pihak, uang pertanggungan, dan klaim.', [
                $this->q('risk', 'Apa yang terutama dikelola oleh asuransi?', 'financial-impact', 'Asuransi membantu mengelola dampak finansial dari peristiwa yang tidak pasti; asuransi tidak mencegah peristiwanya.', ['financial-impact' => 'Dampak finansial dari risiko', 'event-prevention' => 'Kemungkinan peristiwa terjadi', 'daily-costs' => 'Semua pengeluaran sehari-hari'], ['apa-itu-risiko', 'apa-itu-asuransi']),
                $this->q('risk-transfer', 'Apa konsep utama pengelolaan risiko dalam asuransi jiwa konvensional?', 'transfer', 'Risk transfer menggambarkan pengalihan sebagian dampak finansial risiko kepada perusahaan asuransi sesuai kontrak.', ['sharing' => 'Risk sharing', 'transfer' => 'Risk transfer', 'elimination' => 'Risk elimination'], ['cara-kerja-asuransi']),
                $this->q('premium', 'Apa fungsi utama premi dalam polis konvensional?', 'payment', 'Premi adalah pembayaran kontraktual untuk memperoleh perlindungan sesuai syarat polis, bukan jaminan pertumbuhan tabungan.', ['payment' => 'Pembayaran untuk perlindungan sesuai kontrak', 'claim-fee' => 'Biaya yang hanya dibayar saat klaim', 'guaranteed-saving' => 'Tabungan yang pasti bertambah'], ['premi-dan-kontribusi']),
                $this->q('policy', 'Dokumen apa yang menjadi dasar hak, kewajiban, manfaat, dan pengecualian?', 'policy', 'Polis adalah kontrak asuransi; brosur dan penjelasan lisan tidak menggantikan ketentuan polis.', ['brochure' => 'Brosur', 'policy' => 'Polis', 'agent-chat' => 'Pesan dari agen'], ['polis-asuransi']),
                $this->q('beneficiary', 'Siapa yang berhak menerima manfaat sesuai penunjukan polis?', 'beneficiary', 'Penerima manfaat adalah pihak yang ditunjuk untuk menerima manfaat sesuai ketentuan polis.', ['insured' => 'Selalu tertanggung', 'beneficiary' => 'Penerima manfaat', 'agent' => 'Agen'], ['pemegang-polis-tertanggung-penerima-manfaat']),
                $this->q('sum-assured', 'Apa arti uang pertanggungan?', 'benefit', 'Uang pertanggungan adalah nilai manfaat yang ditetapkan polis untuk risiko yang ditanggung, bukan saldo tabungan.', ['benefit' => 'Nilai manfaat sesuai polis', 'premiums' => 'Total premi yang dibayar', 'cash' => 'Saldo yang selalu bisa ditarik'], ['manfaat-dan-uang-pertanggungan']),
                $this->q('claim', 'Siapa yang membuat keputusan akhir atas klaim?', 'insurer', 'Perusahaan asuransi menilai bukti terhadap ketentuan polis; agen dapat membantu, tetapi tidak memutuskan atau menjamin klaim.', ['agent' => 'Agen', 'insurer' => 'Perusahaan asuransi', 'hospital' => 'Rumah sakit'], ['klaim', 'peran-agen-asuransi']),
            ]),
            $this->quiz('asuransi-jiwa-dan-proteksi', 'Asuransi Jiwa & Proteksi', 'life', 'beginner', 7, 'Refleksi konsep risiko kematian, tanggungan, HLV, penerima manfaat, dan uang pertanggungan.', [
                $this->q('death-risk', 'Risiko finansial apa yang terutama direspons asuransi jiwa?', 'income-loss', 'Asuransi jiwa membantu mengurangi dampak ekonomi kematian terhadap pihak yang bergantung pada tertanggung.', ['income-loss' => 'Hilangnya dukungan ekonomi', 'prevent-death' => 'Mencegah kematian', 'hospital-only' => 'Hanya tagihan rumah sakit'], ['asuransi-jiwa']),
                $this->q('dependants', 'Mengapa tanggungan relevan saat memahami proteksi jiwa?', 'impact', 'Tanggungan membantu menunjukkan siapa yang dapat mengalami gangguan finansial ketika penghasilan tertanggung berhenti.', ['impact' => 'Untuk memahami dampak kehilangan penghasilan', 'approval' => 'Untuk menjamin persetujuan', 'discount' => 'Untuk memastikan diskon premi'], ['asuransi-jiwa']),
                $this->q('hlv', 'Apa cara paling tepat memahami Human Life Value?', 'starting-point', 'HLV adalah titik awal perkiraan kehilangan ekonomi bagi tanggungan, bukan harga kehidupan atau jaminan uang pertanggungan.', ['starting-point' => 'Perkiraan awal dampak ekonomi', 'human-price' => 'Harga pasti kehidupan', 'approved-cover' => 'Nilai yang pasti disetujui'], ['human-life-value']),
                $this->q('roles', 'Apakah tertanggung selalu sama dengan penerima manfaat?', 'can-differ', 'Tertanggung adalah orang yang risikonya ditanggung; penerima manfaat adalah pihak yang menerima manfaat dan keduanya dapat berbeda.', ['always-same' => 'Ya, selalu sama', 'can-differ' => 'Tidak, dapat berbeda', 'agent-decides' => 'Tergantung keputusan agen'], ['pemegang-polis-tertanggung-penerima-manfaat']),
                $this->q('review-cover', 'Mengapa uang pertanggungan layak ditinjau berkala?', 'life-changes', 'Tanggung jawab, penghasilan, utang, aset, dan tujuan dapat berubah sehingga konteks kebutuhan finansial juga berubah.', ['life-changes' => 'Situasi finansial dapat berubah', 'claim-guarantee' => 'Agar klaim pasti diterima', 'maximize' => 'Karena harus selalu dimaksimalkan'], ['manfaat-dan-uang-pertanggungan', 'human-life-value']),
                $this->q('contract-limit', 'Apa batas penting manfaat proteksi jiwa?', 'terms', 'Manfaat hanya dibayar untuk kejadian yang memenuhi definisi, masa pertanggungan, dan ketentuan polis.', ['all-events' => 'Semua peristiwa selalu ditanggung', 'terms' => 'Mengikuti definisi dan ketentuan polis', 'verbal' => 'Dapat diubah dengan janji lisan'], ['asuransi-jiwa', 'polis-asuransi']),
            ]),
            $this->quiz('asuransi-syariah', 'Asuransi Syariah', 'sharia', 'intermediate', 7, 'Uji pemahaman tentang risk sharing, ta’awun, tabarru’, ujrah, akad, dan surplus underwriting.', [
                $this->q('risk-sharing', 'Konsep utama pengelolaan risiko asuransi syariah adalah…', 'sharing', 'Risiko dibagi di antara peserta melalui dana tabarru’, sementara perusahaan mengelola dana sesuai akad.', ['sharing' => 'Risk sharing', 'transfer' => 'Risk transfer sepenuhnya', 'elimination' => 'Risk elimination'], ['asuransi-syariah', 'risk-sharing-vs-risk-transfer']),
                $this->q('tabarru', 'Apa fungsi dana tabarru’?', 'mutual-fund', 'Dana tabarru’ digunakan untuk saling membantu peserta yang mengalami risiko yang ditanggung sesuai akad.', ['saving' => 'Tabungan pribadi bebas tarik', 'company-profit' => 'Seluruhnya laba perusahaan', 'mutual-fund' => 'Dana saling membantu peserta'], ['asuransi-syariah']),
                $this->q('taawun', 'Apa makna ta’awun?', 'mutual-help', 'Ta’awun berarti saling membantu dan menjadi semangat dasar mekanisme berbagi risiko.', ['management-fee' => 'Biaya pengelolaan', 'mutual-help' => 'Saling membantu', 'investment-switch' => 'Perpindahan investasi'], ['asuransi-syariah']),
                $this->q('ujrah', 'Apa yang dimaksud ujrah?', 'fee', 'Ujrah adalah imbalan atau biaya pengelolaan yang diterima pengelola sesuai akad.', ['claim' => 'Manfaat klaim', 'fee' => 'Imbalan pengelolaan', 'surplus' => 'Seluruh surplus underwriting'], ['asuransi-syariah']),
                $this->q('wakalah', 'Apa inti wakalah bil ujrah?', 'agency-fee', 'Peserta memberi kuasa kepada perusahaan untuk mengelola dana dengan imbalan ujrah sesuai akad.', ['agency-fee' => 'Kuasa pengelolaan dengan imbalan', 'free' => 'Pengelolaan selalu tanpa biaya', 'risk-transfer' => 'Pengalihan seluruh risiko'], ['asuransi-syariah']),
                $this->q('surplus', 'Apakah surplus underwriting otomatis menjadi milik penuh setiap peserta?', 'rules', 'Surplus underwriting dikelola atau dibagikan hanya sesuai akad, ketentuan produk, dan aturan syariah; bukan imbal hasil yang dijamin.', ['automatic' => 'Ya, selalu dibayar penuh', 'rules' => 'Tidak; mengikuti akad dan ketentuan', 'guaranteed' => 'Ya, sebagai hasil investasi dijamin'], ['asuransi-syariah']),
            ]),
            $this->quiz('paydi-unit-link', 'PAYDI / Unit Link', 'unit-link-paydi', 'intermediate', 9, 'Uji pemahaman tentang proteksi, investasi, risiko, biaya, penarikan, switching, dan keberlanjutan polis.', [
                $this->q('components', 'Apa dua komponen utama PAYDI?', 'both', 'PAYDI menggabungkan perlindungan asuransi dan komponen investasi yang masing-masing memiliki biaya dan risiko.', ['both' => 'Proteksi dan investasi', 'deposit-loan' => 'Deposito dan pinjaman', 'claim-cashback' => 'Klaim dan cashback dijamin'], ['paydi-unit-link']),
                $this->q('investment-risk', 'Bagaimana sifat nilai investasi PAYDI?', 'fluctuates', 'Nilai investasi dapat naik atau turun dan tidak dijamin kecuali kontrak secara eksplisit memberi jaminan.', ['always-up' => 'Selalu naik', 'fluctuates' => 'Dapat naik atau turun', 'same-premium' => 'Selalu sama dengan total premi'], ['paydi-unit-link']),
                $this->q('fees', 'Apa dampak biaya dalam PAYDI?', 'reduce', 'Biaya asuransi dan biaya lain dapat mengurangi nilai polis atau porsi yang diinvestasikan.', ['none' => 'Tidak pernah memengaruhi nilai', 'reduce' => 'Dapat mengurangi nilai polis', 'after-claim' => 'Hanya muncul setelah klaim'], ['paydi-unit-link']),
                $this->q('coi', 'Apa fungsi Cost of Insurance?', 'protection-cost', 'Cost of Insurance membiayai komponen perlindungan dan pembebanannya mengikuti ketentuan produk.', ['return' => 'Menjamin hasil investasi', 'protection-cost' => 'Membiayai perlindungan', 'withdrawal-only' => 'Hanya biaya penarikan'], ['paydi-unit-link']),
                $this->q('withdrawal', 'Apa dampak potensial penarikan nilai investasi?', 'sustainability', 'Penarikan mengurangi nilai yang tersedia dan dapat memengaruhi kemampuan polis membayar biaya selanjutnya.', ['free' => 'Tidak berdampak', 'more-cover' => 'Selalu menambah manfaat', 'sustainability' => 'Dapat mengurangi keberlanjutan'], ['paydi-unit-link']),
                $this->q('switching', 'Apa yang dilakukan switching?', 'move', 'Switching memindahkan alokasi antar pilihan investasi dan tidak menghapus risiko investasi.', ['guarantee' => 'Menjamin keuntungan', 'remove-fees' => 'Menghapus semua biaya', 'move' => 'Memindahkan alokasi investasi'], ['paydi-unit-link']),
                $this->q('premium-holiday', 'Apakah premium holiday berarti perlindungan gratis?', 'costs-continue', 'Premium holiday bukan asuransi gratis; biaya dapat terus mengurangi nilai polis dan memengaruhi keberlanjutannya.', ['free' => 'Ya, semua biaya berhenti', 'forever' => 'Ya, polis aktif selamanya', 'costs-continue' => 'Tidak; biaya dapat tetap berjalan'], ['paydi-unit-link']),
            ]),
            $this->quiz('underwriting-dan-proses-bisnis', 'Underwriting & Business Process', 'policy-process', 'intermediate', 7, 'Uji pemahaman dari pengajuan hingga polis aktif, penyerahan, layanan, dan bantuan klaim.', [
                $this->q('submission', 'Apa ciri pengajuan asuransi yang berkualitas?', 'quality', 'Pengajuan yang lengkap, akurat, dan konsisten membantu mengurangi penundaan serta kebutuhan perbaikan.', ['quality' => 'Lengkap, akurat, dan konsisten', 'fast' => 'Cepat walau belum lengkap', 'blank' => 'Formulir kosong bertanda tangan'], ['proses-bisnis-asuransi']),
                $this->q('underwriting', 'Siapa yang membuat keputusan underwriting akhir?', 'underwriter', 'Perusahaan melalui underwriter menilai risiko; agen mengumpulkan informasi tetapi tidak memutuskan.', ['agent' => 'Agen', 'underwriter' => 'Underwriter perusahaan', 'applicant' => 'Calon nasabah'], ['underwriting', 'peran-agen-asuransi']),
                $this->q('inforce', 'Apakah pembayaran pertama selalu berarti polis sudah inforce?', 'conditions', 'Syarat penerbitan, underwriting, pembayaran, dan tanggal efektif perlu terpenuhi sesuai prosedur.', ['always' => 'Ya, otomatis', 'conditions' => 'Tidak selalu; syarat aktivasi harus terpenuhi', 'verbal' => 'Aktif jika agen mengatakan demikian'], ['proses-bisnis-asuransi']),
                $this->q('delivery', 'Apa tujuan penyerahan polis selain memberikan dokumen?', 'understanding', 'Penyerahan membantu memastikan nasabah menerima dan memahami kontrak, manfaat, biaya, pengecualian, serta prosedur.', ['sales-end' => 'Mengakhiri semua layanan', 'guarantee' => 'Menjamin semua klaim', 'understanding' => 'Memastikan penerimaan dan pemahaman'], ['proses-bisnis-asuransi', 'polis-asuransi']),
                $this->q('servicing', 'Manakah contoh layanan setelah polis terbit?', 'official-change', 'Servicing mencakup perubahan data dan penerima manfaat melalui kanal resmi, pembayaran, tinjauan, serta dukungan proses.', ['verbal-change' => 'Mengubah kontrak secara lisan', 'official-change' => 'Membantu perubahan data secara resmi', 'hide-fees' => 'Mengabaikan informasi biaya'], ['proses-bisnis-asuransi']),
                $this->q('claim-help', 'Apa batas peran agen saat membantu klaim?', 'assist', 'Agen dapat membantu proses dan kelengkapan dokumen, tetapi keputusan akhir ada pada perusahaan.', ['approve' => 'Menyetujui klaim', 'assist' => 'Membantu tanpa memutuskan', 'alter' => 'Mengubah bukti'], ['klaim', 'peran-agen-asuransi']),
            ]),
            $this->quiz('etika-dan-compliance', 'Etika & Compliance', 'ethics-conduct', 'intermediate', 8, 'Uji pemahaman tentang mis-selling, fraud, AML/KYC, PEP, privasi, dan field underwriting.', [
                $this->q('mis-selling', 'Apa yang paling tepat menggambarkan mis-selling?', 'unsuitable', 'Mis-selling adalah penjualan yang tidak sesuai kebutuhan atau kemampuan nasabah, atau dilakukan dengan penjelasan yang menyesatkan.', ['unsuitable' => 'Penjualan yang tidak sesuai', 'rejected-claim' => 'Setiap klaim ditolak', 'price-change' => 'Setiap perubahan harga'], ['etika-penjualan-asuransi']),
                $this->q('fraud', 'Apa yang sebaiknya dilakukan saat melihat indikasi fraud?', 'report', 'Jangan menginvestigasi sendiri; catat seperlunya dan gunakan jalur pelaporan resmi.', ['self' => 'Menginvestigasi sendiri', 'ignore' => 'Mengabaikan', 'report' => 'Melapor lewat kanal resmi'], ['etika-penjualan-asuransi']),
                $this->q('aml', 'Apa tujuan dasar AML?', 'prevent', 'AML membantu mencegah dan mendeteksi penggunaan sistem keuangan untuk pencucian uang.', ['sales' => 'Meningkatkan target penjualan', 'prevent' => 'Mencegah dan mendeteksi pencucian uang', 'guarantee' => 'Menjamin setiap nasabah bebas risiko'], ['dasar-aml-kyc']),
                $this->q('kyc', 'Apa inti KYC?', 'identify', 'KYC mengenali dan memverifikasi identitas serta memahami tujuan dan profil hubungan atau transaksi.', ['collect-all' => 'Mengumpulkan semua data tanpa tujuan', 'social' => 'Menilai popularitas media sosial', 'identify' => 'Mengidentifikasi dan memverifikasi nasabah'], ['dasar-aml-kyc']),
                $this->q('pep', 'Apakah status PEP otomatis berarti seseorang melakukan kesalahan?', 'classification', 'PEP adalah klasifikasi risiko yang dapat memerlukan pemeriksaan lebih lanjut, bukan vonis atas kesalahan.', ['wrongdoing' => 'Ya, selalu', 'classification' => 'Tidak; ini klasifikasi risiko', 'skip' => 'Tidak, jadi KYC boleh dilewati'], ['dasar-aml-kyc']),
                $this->q('privacy', 'Apa contoh purpose limitation?', 'stated-purpose', 'Data hanya digunakan untuk tujuan sah yang telah dijelaskan, bukan untuk kepentingan lain tanpa dasar.', ['marketing' => 'Memakai data klaim untuk promosi pribadi', 'share' => 'Membagikan kepada siapa pun', 'stated-purpose' => 'Menggunakan sesuai tujuan yang dinyatakan'], ['perlindungan-data-nasabah']),
                $this->q('field-underwriting', 'Apa peran agen sebagai field underwriter?', 'accurate', 'Agen mengumpulkan informasi material dengan jujur, lengkap, dan akurat; keputusan akhir tetap pada underwriter.', ['decide' => 'Menentukan keputusan akhir', 'promise' => 'Menjanjikan persetujuan', 'accurate' => 'Mengumpulkan informasi akurat'], ['underwriting', 'peran-agen-asuransi']),
            ]),
        ];
    }

    /** @return array<string, mixed> */
    private function quiz(string $slug, string $title, string $category, string $level, int $duration, string $description, array $questions): array
    {
        return compact('slug', 'title', 'category', 'level', 'duration', 'description', 'questions');
    }

    /** @return array<string, mixed> */
    private function q(string $key, string $prompt, string $correct, string $explanation, array $options, array $articles): array
    {
        return [
            'key' => $key,
            'prompt' => $prompt,
            'correct' => $correct,
            'explanation' => $explanation,
            'articles' => $articles,
            'options' => collect($options)->map(fn ($label, $optionKey) => ['key' => $optionKey, 'label' => $label])->values()->all(),
        ];
    }
}
