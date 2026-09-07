<?php

namespace Database\Seeders;

use App\Enums\ContentLevel;
use App\Enums\ContentStatus;
use App\Enums\ReferenceStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\GlossaryTerm;
use App\Models\Provider;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The Learn corpus. Primary language: Bahasa Indonesia, with standard English
 * terms in parentheses (CONTENT_GUIDE §11). Article bodies use a small set of
 * Markdown directives the frontend renders as educational callouts:
 *
 *   :::definisi / :::penting / :::contoh / :::kesalahpahaman
 *   :::catatan-provider / :::referensi-historis / :::perlu-verifikasi
 *   :istilah[teks]{slug=underwriting}   -> inline glossary tooltip (curated)
 *
 * `key_takeaways` (JSON column) holds the "Poin Penting" list — data, not React.
 * `glossary` lists the term slugs explicitly linked to the article.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn (string $slug) => Category::where('slug', $slug)->firstOrFail()->id;
        $allianz = Provider::where('slug', 'allianz')->first();
        $source = Source::where('title', 'Allianz Basic Training – Dokumentasi Terstruktur')->first();

        foreach ($this->generalArticles() as $i => $data) {
            $body = trim($data['body']);
            $article = Article::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $cat($data['category']),
                    'provider_id' => null,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'body' => $body,
                    'key_takeaways' => $data['takeaways'],
                    'level' => $data['level'],
                    'status' => ContentStatus::Published,
                    'is_featured' => $data['featured'] ?? false,
                    'estimated_reading_minutes' => max(3, (int) ceil(str_word_count(strip_tags($body)) / 200)),
                    'published_at' => now()->subDays(60 - $i),
                    'last_reviewed_at' => now()->subDays(21),
                    'reference_status' => ReferenceStatus::Current,
                ],
            );

            $this->syncTags($article, $data['tags'] ?? []);
            $this->syncGlossary($article, $data['glossary'] ?? []);
        }

        if ($allianz) {
            foreach ($this->providerArticles() as $j => $pa) {
                $body = trim($pa['body']);
                $article = Article::updateOrCreate(
                    ['slug' => $pa['slug']],
                    [
                        'category_id' => $cat($pa['category']),
                        'provider_id' => $allianz->id,
                        'title' => $pa['title'],
                        'excerpt' => $pa['excerpt'],
                        'body' => $body,
                        'key_takeaways' => $pa['takeaways'],
                        'level' => ContentLevel::Advanced,
                        'status' => ContentStatus::Published,
                        'is_featured' => false,
                        'estimated_reading_minutes' => max(3, (int) ceil(str_word_count(strip_tags($body)) / 200)),
                        'published_at' => now()->subDays(10 - $j),
                        'last_reviewed_at' => now()->subDays(14),
                        'reference_period' => 'Allianz Basic Training – kompilasi Agustus 2026',
                        'reference_status' => $pa['status'],
                    ],
                );

                $this->syncTags($article, $pa['tags'] ?? []);
                $this->syncGlossary($article, $pa['glossary'] ?? []);

                if ($source) {
                    $article->sources()->syncWithoutDetaching([
                        $source->id => ['note' => 'Sumber pelatihan utama.'],
                    ]);
                }
            }
        }
    }

    /** @param array<int, string> $tagNames */
    private function syncTags(Article $article, array $tagNames): void
    {
        $ids = collect($tagNames)->map(fn (string $name) => Tag::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name],
        )->id)->all();

        $article->tags()->sync($ids);
    }

    /** @param array<int, string> $slugs */
    private function syncGlossary(Article $article, array $slugs): void
    {
        $ids = GlossaryTerm::whereIn('slug', $slugs)->pluck('id')->all();
        $article->glossaryTerms()->sync($ids);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function generalArticles(): array
    {
        return [
            [
                'slug' => 'apa-itu-risiko',
                'title' => 'Apa Itu Risiko?',
                'category' => 'fundamentals',
                'level' => ContentLevel::Beginner,
                'featured' => true,
                'excerpt' => 'Risiko adalah kemungkinan terjadinya peristiwa tidak pasti yang membuat kondisi keuangan Anda memburuk. Yang penting bukan peristiwanya, melainkan dampak finansialnya.',
                'tags' => ['dasar', 'risiko', 'mulai-dari-sini'],
                'glossary' => ['risiko', 'risk-transfer', 'risk-sharing'],
                'takeaways' => [
                    'Dalam asuransi, yang dinilai adalah dampak finansial risiko, bukan peristiwanya.',
                    'Risiko besar-tapi-jarang perlu ditangani berbeda dari risiko kecil-tapi-sering.',
                    'Langkah pertama sebelum membeli apa pun: sebutkan risiko yang ingin Anda lindungi.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Risiko** adalah kemungkinan terjadinya peristiwa tidak pasti yang meninggalkan Anda dalam kondisi keuangan lebih buruk — sakit, kecelakaan, meninggalnya pencari nafkah, atau tagihan rumah sakit yang lebih besar dari tabungan.
                :::

                ## Mengapa ini penting

                Setiap rencana asuransi yang baik dimulai dari satu pertanyaan sederhana: *risiko apa yang sedang saya coba lindungi?* Bila pertanyaan itu belum terjawab, membandingkan produk hanya membuat bingung. Dalam konteks asuransi, yang diperhitungkan bukan seberapa sering suatu peristiwa terjadi, melainkan **seberapa besar dampak finansialnya** dan apakah sebuah rumah tangga sanggup menyerapnya sendiri.

                ## Contoh

                :::contoh
                Sebuah keluarga bergantung pada satu gaji. Jika orang itu tidak dapat bekerja selama enam bulan, sewa rumah, biaya makan, dan uang sekolah tidak ikut berhenti. Selisih antara yang tetap harus dibayar dan yang kini masuk adalah **dampak finansial dari risiko** tersebut.
                :::

                ## Dua jenis risiko yang berbeda perlakuan

                | Jenis | Ciri | Cara menghadapinya |
                |---|---|---|
                | Kecil dan sering | Servis kendaraan, obat ringan | Ditanggung sendiri dari anggaran / dana darurat |
                | Besar dan jarang | Rawat inap besar, penyakit kritis, meninggal | Dialihkan atau dibagi lewat asuransi |

                Asuransi paling masuk akal untuk risiko besar-tapi-jarang. Untuk risiko kecil-tapi-sering, membeli pertanggungan sering kali lebih mahal daripada menanggungnya sendiri.

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Risiko berarti sedang sial."**

                Tidak. Untuk asuransi, yang dinilai adalah konsekuensi keuangan dari sebuah peristiwa yang tidak pasti — dan apakah rumah tangga Anda bisa bertahan menghadapinya — bukan soal keberuntungan.
                :::

                ## Pelajari selanjutnya

                Setelah memahami risiko, langkah berikutnya adalah melihat bagaimana asuransi memindahkan atau membagi dampak finansialnya, lewat :istilah[risk transfer]{slug=risk-transfer} pada asuransi konvensional atau :istilah[risk sharing]{slug=risk-sharing} pada asuransi syariah.
                MD,
            ],
            [
                'slug' => 'apa-itu-asuransi',
                'title' => 'Apa Itu Asuransi?',
                'category' => 'fundamentals',
                'level' => ContentLevel::Beginner,
                'featured' => true,
                'excerpt' => 'Asuransi adalah kontrak yang memindahkan dampak finansial dari risiko yang tidak pasti. Anda membayar premi; sebagai gantinya penanggung membayar manfaat yang ditentukan bila peristiwa yang dijamin terjadi.',
                'tags' => ['dasar', 'mulai-dari-sini'],
                'glossary' => ['premi', 'manfaat', 'risk-transfer', 'pengecualian'],
                'takeaways' => [
                    'Asuransi menukar biaya kecil yang pasti (premi) dengan perlindungan atas kerugian besar yang tidak pasti.',
                    'Asuransi bukan tabungan dan umumnya bukan investasi.',
                    'Sebagian besar polis hanya membayar bila peristiwa yang dijamin secara spesifik terjadi.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Asuransi** adalah kontrak yang memindahkan dampak finansial dari sebuah risiko yang tidak pasti. Anda membayar :istilah[premi]{slug=premi}; sebagai gantinya, penanggung membayar :istilah[manfaat]{slug=manfaat} yang telah ditentukan bila peristiwa yang dijamin terjadi.
                :::

                ## Bagaimana logikanya bekerja

                Banyak orang masing-masing membayar premi yang relatif kecil ke dalam satu kumpulan dana. Dalam satu tahun, hanya sebagian yang mengalami kerugian yang dijamin, dan kumpulan dana itu membayar mereka. Semua orang menukar **biaya kecil yang pasti** dengan **perlindungan atas kerugian besar yang tidak pasti**.

                :::contoh
                Seribu keluarga masing-masing menyisihkan sejumlah kecil dana setiap tahun. Dalam setahun, mungkin lima keluarga mengalami rawat inap besar. Dana bersama menutup biaya kelima keluarga itu. Empat ratus ribu rupiah premi setiap keluarga jauh lebih ringan daripada menanggung sendiri tagihan puluhan juta rupiah.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Kalau tidak pernah klaim, premi saya hangus dan rugi."**

                Premi membeli perlindungan selama periode tertentu, sama seperti Anda membayar untuk rasa aman, bukan menabung. Tidak mengajukan klaim berarti risiko yang Anda khawatirkan tidak terjadi — itu hasil yang baik, bukan kerugian.
                :::

                :::penting
                Asuransi bukan rekening tabungan dan umumnya bukan investasi. Sebagian besar polis hanya membayar bila peristiwa tertentu yang dijamin terjadi. Itu memang tujuannya, bukan kekurangannya.
                :::

                ## Yang perlu diperiksa pada polis mana pun

                - Apa persisnya peristiwa yang dijamin?
                - Apa saja yang dikecualikan ([pengecualian](/glossary/pengecualian))?
                - Bagaimana besar manfaat dihitung?
                - Siapa yang menerbitkan dan menanggung kontrak ini?

                ## Pelajari selanjutnya

                Berikutnya: bagaimana proses asuransi berjalan dari pengajuan sampai klaim, dan mengapa membayar premi pertama tidak otomatis berarti Anda sudah dijamin.
                MD,
            ],
            [
                'slug' => 'cara-kerja-asuransi',
                'title' => 'Bagaimana Asuransi Bekerja',
                'category' => 'fundamentals',
                'level' => ContentLevel::Beginner,
                'featured' => true,
                'excerpt' => 'Anda mengajukan, penanggung menilai risiko (underwriting), polis diterbitkan, Anda membayar premi, dan bila peristiwa yang dijamin terjadi Anda mengajukan klaim.',
                'tags' => ['dasar', 'mulai-dari-sini', 'proses'],
                'glossary' => ['underwriting', 'inforce', 'klaim', 'masa-tunggu'],
                'takeaways' => [
                    'Alur asuransi: ajukan → underwriting → polis terbit → bayar premi → klaim bila perlu.',
                    'Membeli polis dan membayar sekali tidak selalu berarti langsung dijamin.',
                    'Klaim dinilai terhadap ketentuan polis, bukan terhadap apa yang terasa adil.',
                ],
                'body' => <<<'MD'
                ## Konsep

                Alur asuransi memiliki bentuk yang tetap: Anda mengajukan, penanggung menilai risiko lewat :istilah[underwriting]{slug=underwriting}, polis diterbitkan, Anda membayar premi, dan bila peristiwa yang dijamin terjadi Anda mengajukan :istilah[klaim]{slug=klaim} lalu penanggung membayar manfaat yang ditentukan dalam kontrak.

                :::contoh
                Anda membeli pertanggungan penyakit kritis. Beberapa tahun kemudian Anda didiagnosis kondisi yang dijamin. Anda mengirim formulir klaim dan bukti medis; penanggung mencocokkan diagnosis dengan definisi di polis, lalu membayar santunan sekaligus.
                :::

                ## Kapan pertanggungan benar-benar mulai

                :::penting
                Membeli polis dan membayar satu kali tidak selalu berarti Anda langsung dijamin. Pertanggungan biasanya baru berlaku setelah underwriting, pembayaran, dan syarat tanggal efektif semuanya terpenuhi — keadaan ini disebut :istilah[inforce]{slug=inforce}.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Setelah polis keluar, semua kondisi langsung dijamin penuh."**

                Sering ada :istilah[masa tunggu]{slug=masa-tunggu} untuk kondisi tertentu, dan klaim tetap dinilai terhadap definisi di polis — bukan terhadap apa yang terasa adil atau apa yang dijanjikan secara lisan.
                :::

                ## Yang perlu diperiksa

                - Kapan pertanggungan benar-benar mulai berlaku?
                - Dokumen apa yang dibutuhkan sebuah klaim?
                - Apakah ada masa tunggu atau elimination period?

                ## Pelajari selanjutnya

                Berikutnya: apa isi sebuah polis, dan mengapa brosur bukan kontrak.
                MD,
            ],
            [
                'slug' => 'polis-asuransi',
                'title' => 'Polis Asuransi',
                'category' => 'policy-process',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Polis adalah kontrak lengkap: siapa yang dijamin, untuk apa, sebesar apa, berapa lama, apa yang dikecualikan, dan bagaimana klaim bekerja.',
                'tags' => ['dasar', 'polis', 'proses'],
                'glossary' => ['pemegang-polis', 'tertanggung', 'penerima-manfaat', 'uang-pertanggungan', 'pengecualian', 'riplay'],
                'takeaways' => [
                    'Semua yang menjadi kewajiban penanggung ditetapkan di polis, bukan di brosur.',
                    'Bila ilustrasi penjualan dan ketentuan polis berbeda, ketentuan polis yang berlaku.',
                    'Periksa nama, tanggal, UP, rider, dan pengecualian segera setelah polis terbit.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Polis** adalah keseluruhan kontrak asuransi: siapa yang dijamin, untuk apa, sebesar apa, untuk berapa lama, apa yang dikecualikan, dan bagaimana klaim bekerja. Segala yang menjadi kewajiban penanggung terhadap Anda didefinisikan di sana — bukan di brosur.
                :::

                ## Bagian-bagiannya

                - **Ringkasan polis (policy schedule).** Memuat :istilah[pemegang polis]{slug=pemegang-polis}, :istilah[tertanggung]{slug=tertanggung}, :istilah[penerima manfaat]{slug=penerima-manfaat}, :istilah[uang pertanggungan]{slug=uang-pertanggungan}, rider yang dilekatkan, premi, dan masa pertanggungan.
                - **Buku ketentuan.** Mendefinisikan setiap peristiwa yang dijamin dan setiap :istilah[pengecualian]{slug=pengecualian}.
                - **Endorsemen.** Perubahan resmi terhadap polis setelah terbit.

                :::penting
                Ilustrasi atau presentasi penjualan **bukan** kontrak. Bila keduanya berbeda dengan ketentuan polis, yang berlaku adalah ketentuan polis. Untuk penjelasan resmi produk, rujuk :istilah[RIPLAY]{slug=riplay} dan buku ketentuan.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Yang dijelaskan agen pasti sama dengan isi polis."**

                Belum tentu. Baca sendiri ringkasan polis dan buku ketentuan pada masa mempelajari polis (free-look). Bila ada yang tidak sesuai penjelasan, itulah waktunya menanyakan atau membatalkan.
                :::

                ## Yang perlu diperiksa

                - Apakah nama, tanggal, dan UP sesuai harapan Anda?
                - Kondisi apa saja yang dikecualikan?
                - Berapa lama masa mempelajari polis (free-look period)?

                ## Pelajari selanjutnya

                Berikutnya: peran pemegang polis, tertanggung, dan penerima manfaat — tiga pihak yang sering tertukar.
                MD,
            ],
            [
                'slug' => 'premi-dan-kontribusi',
                'title' => 'Premi dan Kontribusi',
                'category' => 'fundamentals',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Premi adalah pembayaran yang membuat proteksi tetap berlaku. Pada produk syariah, istilah yang setara adalah kontribusi, dan konsep di baliknya berbeda.',
                'tags' => ['dasar', 'premi', 'syariah'],
                'glossary' => ['premi', 'kontribusi', 'tabarru', 'biaya-asuransi', 'cuti-premi'],
                'takeaways' => [
                    'Besar premi mencerminkan risiko, usia, kesehatan, besar manfaat, dan biaya produk.',
                    'Pada asuransi syariah, sebagian "kontribusi" masuk ke dana tabarru untuk menolong peserta lain.',
                    'Cuti premi pada unit link bukan proteksi gratis — biaya tetap dapat dipotong dari nilai polis.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Premi** adalah pembayaran yang Anda lakukan — bulanan, tahunan, atau sekaligus — untuk menjaga proteksi tetap berlaku. Besarnya mencerminkan risiko yang dijamin, usia dan kesehatan tertanggung, besar manfaat, serta biaya yang melekat pada produk.
                :::

                ## Premi vs kontribusi

                Pada asuransi konvensional, premi dibayarkan untuk mengalihkan risiko ke perusahaan. Pada :istilah[asuransi syariah]{slug=asuransi-syariah}, istilah yang dipakai adalah :istilah[kontribusi]{slug=kontribusi}: sebagiannya masuk ke dana :istilah[tabarru']{slug=tabarru} yang dipakai untuk menolong peserta lain yang tertimpa risiko. Perbedaannya bukan sekadar kata — mekanismenya memang berbeda.

                :::contoh
                Dua orang membeli pertanggungan yang sama. Pemohon yang lebih tua, atau yang pekerjaannya berisiko tinggi, membayar lebih besar karena penanggung memperkirakan peluang klaim yang lebih tinggi.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Cuti premi (premium holiday) berarti proteksi jalan terus tanpa biaya."**

                Tidak. Pada polis unit link, :istilah[biaya asuransi]{slug=biaya-asuransi} dan biaya lain tetap dapat dipotong dari nilai polis selama :istilah[cuti premi]{slug=cuti-premi}, sehingga usia polis bisa memendek atau polis menjadi lapse.
                :::

                ## Yang perlu diperiksa

                - Apakah premi tetap (level) atau meningkat seiring waktu?
                - Apa yang terjadi jika satu pembayaran terlewat?
                - Untuk unit link: apakah biaya tetap dipotong saat cuti premi?

                ## Pelajari selanjutnya

                Berikutnya: manfaat dan uang pertanggungan — apa yang sebenarnya dibayar sebuah polis.
                MD,
            ],
            [
                'slug' => 'manfaat-dan-uang-pertanggungan',
                'title' => 'Manfaat dan Uang Pertanggungan',
                'category' => 'fundamentals',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Uang Pertanggungan (Sum Assured) adalah nilai yang ditetapkan polis. Manfaat yang benar-benar dibayar bisa berupa persentase dari UP, atau dibatasi oleh biaya yang layak diganti.',
                'tags' => ['dasar', 'manfaat', 'uang-pertanggungan'],
                'glossary' => ['uang-pertanggungan', 'manfaat', 'lump-sum', 'reimbursement', 'human-life-value'],
                'takeaways' => [
                    'UP adalah nilai acuan; pembayaran nyata bisa berupa persentase UP atau dibatasi biaya eligible.',
                    'Manfaat lump sum dibayar atas suatu pemicu; manfaat reimbursement mengikuti biaya nyata.',
                    'UP yang lebih besar tidak otomatis lebih baik — harus sesuai kebutuhan dan tetap terjangkau.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Uang Pertanggungan (UP / Sum Assured)** adalah nilai manfaat yang ditetapkan polis bila peristiwa yang dijamin terjadi. **Manfaat** adalah apa yang benar-benar dibayarkan atau disediakan — dan besarnya tidak selalu sama dengan UP.
                :::

                ## Dua cara manfaat dibayar

                | Model | Contoh | Cara kerja |
                |---|---|---|
                | :istilah[Lump sum]{slug=lump-sum} | Penyakit kritis, term life | Dibayar atas suatu pemicu (diagnosis, meninggal), berdasarkan persentase UP; tidak harus sama dengan tagihan |
                | :istilah[Reimbursement]{slug=reimbursement} | Asuransi biaya kesehatan | Mengganti biaya nyata yang layak diganti, sampai limit, setelah deductible/co-share |

                :::contoh
                Sebuah polis dengan UP jiwa Rp1.000.000.000 membayar penuh jumlah itu saat tertanggung meninggal dalam masa pertanggungan. Sebuah rider penyakit kritis "accelerated" mungkin membayar 50% UP lebih dulu — dan sisa UP jiwa berkurang menjadi Rp500.000.000.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Ambil UP sebesar-besarnya biar aman."**

                Manfaat jiwa adalah kompensasi atas **kerugian ekonomi bagi tanggungan**, bukan penilaian atas seseorang. UP sebaiknya cukup untuk menutup kebutuhan, tetapi tetap terjangkau agar polis bisa dijaga jangka panjang. Lihat :istilah[Human Life Value]{slug=human-life-value} sebagai titik awal.
                :::

                ## Pelajari selanjutnya

                Berikutnya: siapa pemegang polis, tertanggung, dan penerima manfaat.
                MD,
            ],
            [
                'slug' => 'pemegang-polis-tertanggung-penerima-manfaat',
                'title' => 'Pemegang Polis, Tertanggung, dan Penerima Manfaat',
                'category' => 'fundamentals',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Tiga peran yang sering tertukar. Pemegang polis memiliki kontrak, tertanggung adalah orang yang risikonya dijamin, penerima manfaat adalah pihak yang berhak menerima manfaat.',
                'tags' => ['dasar', 'polis'],
                'glossary' => ['pemegang-polis', 'tertanggung', 'penerima-manfaat', 'premi'],
                'takeaways' => [
                    'Pemegang polis menjalankan hak dan kewajiban kontrak; belum tentu orang yang dijamin.',
                    'Tertanggung adalah orang yang peristiwanya memicu klaim.',
                    'Penerima manfaat perlu dijaga tetap mutakhir dalam peninjauan polis tahunan.',
                ],
                'body' => <<<'MD'
                ## Tiga peran

                :::definisi
                - **:istilah[Pemegang polis]{slug=pemegang-polis}** — memiliki kontrak dan menjalankan hak serta kewajibannya (membayar :istilah[premi]{slug=premi}, mengubah data, mengajukan perubahan).
                - **:istilah[Tertanggung]{slug=tertanggung}** — orang yang risikonya dijamin; peristiwa yang menimpanya-lah yang memicu klaim.
                - **:istilah[Penerima manfaat]{slug=penerima-manfaat}** — pihak yang berhak menerima manfaat sesuai polis.
                :::

                :::contoh
                Seorang ibu (pemegang polis) membeli polis jiwa atas nama suaminya (tertanggung), dengan anak-anak sebagai penerima manfaat. Jika suami meninggal dalam masa pertanggungan, manfaat dibayarkan kepada anak-anak sesuai ketentuan polis.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Pemegang polis dan tertanggung selalu orang yang sama."**

                Sering sama, tetapi tidak harus. Orang tua bisa menjadi pemegang polis atas anak; perusahaan bisa menjadi pemegang polis atas karyawan kuncinya.
                :::

                ## Yang perlu diperiksa

                - Apakah nama ketiga peran sudah benar di ringkasan polis?
                - Apakah data penerima manfaat masih sesuai keadaan keluarga terkini?

                ## Pelajari selanjutnya

                Berikutnya: bagaimana klaim diajukan dan dinilai.
                MD,
            ],
            [
                'slug' => 'klaim',
                'title' => 'Klaim',
                'category' => 'policy-process',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Klaim adalah permintaan manfaat setelah peristiwa yang dijamin. Penanggung mencocokkan peristiwa dengan definisi polis dan membayar bila seluruh syarat terpenuhi.',
                'tags' => ['klaim', 'proses'],
                'glossary' => ['klaim', 'pengecualian', 'reimbursement', 'survival-period'],
                'takeaways' => [
                    'Klaim dinilai terhadap ketentuan polis — bukan terhadap apa yang terasa adil atau kata agen.',
                    'Setiap manfaat butuh bukti tertentu; ada tenggat pemberitahuan ke penanggung.',
                    'Agen boleh membantu menyiapkan klaim, tetapi tidak memutuskan atau menjamin hasilnya.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Klaim** adalah permintaan Anda atas manfaat setelah terjadi peristiwa yang dijamin. Anda mengirim formulir dan bukti; penanggung mencocokkan peristiwa dengan definisi di polis, lalu membayar bila seluruh syarat terpenuhi.
                :::

                :::contoh
                Setelah rawat inap, klaim biaya kesehatan membutuhkan rincian tagihan dan resume medis. Klaim penyakit kritis membutuhkan diagnosis spesialis yang sesuai dengan definisi kondisi di polis, dan kadang tertanggung harus melewati :istilah[survival period]{slug=survival-period}.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Kalau sudah bayar premi bertahun-tahun, klaim pasti cair."**

                Klaim dinilai terhadap **ketentuan polis** — definisi, limit, :istilah[pengecualian]{slug=pengecualian}, dan kondisi pertanggungan — bukan terhadap lamanya Anda membayar premi atau apa yang dijanjikan secara lisan.
                :::

                :::penting
                Agen dapat membantu menyiapkan dokumen klaim dan menjelaskan prosesnya, tetapi keputusan klaim ada pada penanggung. Tidak seorang pun boleh **menjamin** hasil klaim.
                :::

                ## Yang perlu diperiksa

                - Bukti apa yang dibutuhkan manfaat ini?
                - Adakah tenggat memberi tahu penanggung?
                - Apakah diagnosis memenuhi definisi di polis?

                ## Pelajari selanjutnya

                Berikutnya: underwriting — bagaimana penanggung menilai risiko sebelum polis terbit.
                MD,
            ],
            [
                'slug' => 'asuransi-jiwa',
                'title' => 'Asuransi Jiwa',
                'category' => 'life',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Asuransi jiwa membayar manfaat saat tertanggung meninggal. Tugasnya mengganti kerugian ekonomi bagi orang yang bergantung pada penghasilannya — bukan memberi harga pada nyawa.',
                'tags' => ['jiwa', 'proteksi'],
                'glossary' => ['uang-pertanggungan', 'human-life-value', 'penerima-manfaat', 'tpd'],
                'takeaways' => [
                    'Manfaat jiwa adalah kompensasi atas kerugian ekonomi bagi tanggungan.',
                    'Lebih banyak pertanggungan tidak otomatis lebih baik — sesuaikan dengan kebutuhan dan kemampuan.',
                    'Mulai dari pertanyaan: siapa yang bergantung pada penghasilan saya, dan untuk berapa lama?',
                ],
                'body' => <<<'MD'
                :::definisi
                **Asuransi jiwa** membayar manfaat ketika tertanggung meninggal (dan pada sebagian produk, atas peristiwa hidup lain yang dijamin). Tujuannya mengganti **kerugian ekonomi** bagi orang yang bergantung pada penghasilan tertanggung — bukan memberi harga pada seseorang.
                :::

                ## Mengapa ini penting

                Ketika pencari nafkah meninggal, penghasilan berhenti tetapi kewajiban tidak: cicilan, biaya hidup, pendidikan anak. Manfaat jiwa menyediakan dana untuk menggantikan penghasilan yang hilang selama periode tertentu.

                :::contoh
                Seorang orang tua yang menjadi tulang punggung keluarga mengambil pertanggungan agar, bila ia meninggal, keluarganya memiliki dana untuk menggantikan penghasilan beberapa tahun ke depan serta melunasi cicilan dan biaya pendidikan.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Semakin besar UP, semakin bagus."**

                Manfaat adalah kompensasi atas **kerugian ekonomi bagi tanggungan**. UP sebaiknya cukup namun tetap terjangkau agar polis bisa dijaga jangka panjang. Perkiraan kasarnya bisa dimulai dari :istilah[Human Life Value]{slug=human-life-value}.
                :::

                ## Yang perlu diperiksa

                - Siapa yang bergantung pada penghasilan Anda, dan untuk berapa lama?
                - Kewajiban apa yang akan tersisa?
                - Apakah UP cukup tetapi masih berkelanjutan?

                ## Pelajari selanjutnya

                Berikutnya: Human Life Value sebagai titik awal menghitung kebutuhan.
                MD,
            ],
            [
                'slug' => 'human-life-value',
                'title' => 'Human Life Value',
                'category' => 'life',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'HLV adalah perkiraan sederhana atas nilai ekonomi seseorang bagi keluarganya. Sebuah rumus pelatihan yang umum: penghasilan tahunan dikali sisa tahun produktif.',
                'tags' => ['jiwa', 'perencanaan'],
                'glossary' => ['human-life-value', 'uang-pertanggungan'],
                'takeaways' => [
                    'HLV hanya titik awal — bukan harga sebuah nyawa.',
                    'Rumus pelatihan yang umum: penghasilan tahunan × sisa tahun produktif.',
                    'Perencanaan nyata juga menimbang biaya hidup, utang, aset, inflasi, pendidikan, dan proteksi yang sudah ada.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Human Life Value (HLV)** adalah perkiraan sederhana atas nilai ekonomi seseorang bagi keluarganya. Sebuah rumus pelatihan yang umum: penghasilan tahunan dikali perkiraan sisa tahun produktif.
                :::

                :::contoh
                Seseorang berpenghasilan Rp180.000.000 per tahun dengan sekitar 20 tahun produktif tersisa memiliki HLV sederhana sebesar Rp180.000.000 × 20 = Rp3.600.000.000. Angka ini bulat dan hipotetis — dipakai untuk memberi gambaran, bukan kepastian.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"HLV berarti hidup saya bernilai sekian rupiah."**

                Tidak. HLV memperkirakan **kerugian ekonomi bagi tanggungan**, bukan nilai seseorang manusia. Ia juga hanya titik awal: perencanaan nyata menimbang biaya hidup, utang, aset, inflasi, biaya pendidikan, dan proteksi yang sudah dimiliki.
                :::

                ## Yang perlu diperiksa

                - Berapa periode produktif Anda yang realistis?
                - Kewajiban apa saja yang harus tertutup?
                - Proteksi apa yang sudah Anda miliki?

                ## Pelajari selanjutnya

                Berikutnya: asuransi kesehatan dan cara kerja penggantian biaya.
                MD,
            ],
            [
                'slug' => 'asuransi-kesehatan',
                'title' => 'Asuransi Kesehatan',
                'category' => 'health',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Asuransi kesehatan membantu membayar biaya rawat inap dan pengobatan yang memenuhi ketentuan polis. Umumnya membayar dengan basis penggantian (reimbursement) atas biaya yang layak diganti.',
                'tags' => ['kesehatan', 'proteksi'],
                'glossary' => ['reimbursement', 'deductible', 'co-share', 'cashless', 'masa-tunggu'],
                'takeaways' => [
                    'Asuransi kesehatan umumnya mengganti biaya eligible sampai limit, setelah deductible/co-share.',
                    '"Cashless" tidak berarti nol biaya pribadi.',
                    'Periksa limit, hak kamar, deductible/co-share, pengecualian, dan masa tunggu.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Asuransi kesehatan** membantu membayar biaya rawat inap dan pengobatan yang memenuhi ketentuan polis, melindungi tabungan dari tagihan besar. Umumnya membayar dengan basis :istilah[reimbursement]{slug=reimbursement} atas biaya yang layak diganti (eligible).
                :::

                :::contoh
                Setelah operasi, polis membayar biaya eligible sampai limit plan. :istilah[Deductible]{slug=deductible} atau :istilah[co-share]{slug=co-share} berarti Anda tetap menanggung bagian tertentu sendiri.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Kartu cashless berarti saya tidak pernah bayar apa pun."**

                Tidak. :istilah[Cashless]{slug=cashless} berarti penanggung menyelesaikan biaya eligible secara langsung. Kelebihan di atas limit, deductible, dan item non-eligible tetap bisa menjadi tanggungan Anda.
                :::

                :::kesalahpahaman
                **"Asuransi kesehatan menanggung semua biaya medis."**

                Tidak selalu. Manfaat bergantung pada ketentuan polis, limit, pengecualian, deductible/co-share, dan kondisi pertanggungan. Sebagian kondisi punya :istilah[masa tunggu]{slug=masa-tunggu}.
                :::

                ## Yang perlu diperiksa

                - Berapa limit plan dan hak kamar?
                - Adakah deductible atau co-share?
                - Pengobatan atau kondisi apa yang dikecualikan atau punya masa tunggu?

                ## Pelajari selanjutnya

                Berikutnya: penyakit kritis — mengapa santunannya berbentuk lump sum, bukan penggantian biaya.
                MD,
            ],
            [
                'slug' => 'penyakit-kritis',
                'title' => 'Penyakit Kritis (Critical Illness)',
                'category' => 'critical-illness',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Pertanggungan penyakit kritis membayar santunan sekaligus pada diagnosis kondisi serius yang didefinisikan polis. Uangnya bebas Anda gunakan untuk pengobatan, pemulihan, atau mengganti penghasilan.',
                'tags' => ['penyakit-kritis', 'proteksi'],
                'glossary' => ['penyakit-kritis', 'lump-sum', 'survival-period', 'elimination-period', 'manfaat-akselerasi'],
                'takeaways' => [
                    'Santunan penyakit kritis berbentuk lump sum, tidak bergantung tagihan rumah sakit.',
                    'Jumlah kondisi yang "dijamin" bukan ukuran kemudahan klaim — tiap kondisi punya definisi ketat.',
                    'Perhatikan apakah manfaat mengurangi UP jiwa (accelerated) atau tidak.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Pertanggungan penyakit kritis** membayar :istilah[santunan sekaligus]{slug=lump-sum} pada diagnosis kondisi serius yang didefinisikan polis — kerap serangan jantung, stroke, atau kanker pada tingkat keparahan tertentu. Uangnya bebas Anda gunakan.
                :::

                :::contoh
                Seseorang didiagnosis kondisi yang dijamin dan menerima santunan sekaligus. Berbeda dari klaim biaya kesehatan, pembayaran ini adalah manfaat tetap dan tidak bergantung pada tagihan rumah sakit.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Produk yang 'menjamin 100+ kondisi' pasti lebih mudah klaimnya."**

                Jumlah kondisi bukan ukuran kemudahan klaim, dan produk tidak membayar 100+ kali. Setiap kondisi punya definisi ketat, sering dengan ambang keparahan, :istilah[survival period]{slug=survival-period}, atau :istilah[elimination period]{slug=elimination-period}.
                :::

                ## Yang perlu diperiksa

                - Kondisi dan tingkat keparahan apa yang dijamin?
                - Apakah ada survival period atau masa tunggu?
                - Apakah pembayaran mengurangi UP jiwa (:istilah[accelerated]{slug=manfaat-akselerasi}) atau tidak?

                ## Pelajari selanjutnya

                Lihat perbedaan [reimbursement vs lump sum](/learn/reimbursement-vs-lump-sum), lalu [Total Permanent Disability](/learn/total-permanent-disability).
                MD,
            ],
            [
                'slug' => 'total-permanent-disability',
                'title' => 'Total Permanent Disability (TPD)',
                'category' => 'disability-accident',
                'level' => ContentLevel::Intermediate,
                'excerpt' => 'Pertanggungan TPD membayar manfaat ketika tertanggung mengalami cacat tetap, sesuai definisi polis, yang mengakhiri kemampuannya mencari nafkah.',
                'tags' => ['disabilitas', 'proteksi', 'penghasilan'],
                'glossary' => ['tpd', 'lump-sum', 'manfaat-akselerasi'],
                'takeaways' => [
                    'TPD merespons hilangnya kemampuan mencari nafkah secara permanen, bukan cacat sementara.',
                    'Definisi bervariasi: "own occupation", "any occupation", atau daftar kehilangan tertentu.',
                    'Perhatikan apakah strukturnya standalone atau accelerated terhadap UP jiwa.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Total Permanent Disability (TPD)** membayar manfaat ketika tertanggung mengalami cacat tetap — sesuai definisi polis — yang mengakhiri kemampuannya mencari nafkah, baik karena penyakit maupun kecelakaan.
                :::

                :::contoh
                Seseorang kehilangan fungsi kedua tangan secara permanen akibat kecelakaan dan tidak lagi dapat menjalankan pekerjaannya. Manfaat TPD menyediakan dana untuk biaya hidup dan penyesuaian terhadap keadaan baru.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"TPD sama saja dengan tidak bisa kerja sementara karena sakit."**

                Tidak. TPD tentang kehilangan **permanen** kemampuan mencari nafkah. Definisinya ketat dan berbeda-beda antara "own occupation", "any occupation", dan daftar kehilangan tertentu.
                :::

                ## Yang perlu diperiksa

                - Definisi disabilitas mana yang dipakai polis?
                - Apakah struktur manfaat standalone atau :istilah[accelerated]{slug=manfaat-akselerasi} terhadap UP jiwa?
                - Bukti dan masa tunggu apa yang berlaku?

                ## Pelajari selanjutnya

                Berikutnya: perlindungan kecelakaan dan cara kerja tabel persentase manfaatnya.
                MD,
            ],
            [
                'slug' => 'perlindungan-kecelakaan',
                'title' => 'Perlindungan Kecelakaan (Accident)',
                'category' => 'disability-accident',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Pertanggungan kecelakaan membayar manfaat tambahan untuk meninggal atau cedera akibat kecelakaan. Manfaat cacat sebagian biasanya mengikuti tabel persentase per jenis cedera.',
                'tags' => ['kecelakaan', 'proteksi'],
                'glossary' => ['addb', 'lump-sum', 'pengecualian'],
                'takeaways' => [
                    'Pertanggungan kecelakaan hanya merespons peristiwa yang memenuhi definisi "kecelakaan" pada polis.',
                    'Manfaat cacat sebagian mengikuti tabel persentase per kondisi.',
                    'Penyebab yang berkaitan dengan penyakit ditangani oleh asuransi kesehatan atau penyakit kritis.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Pertanggungan kecelakaan** membayar manfaat tambahan untuk meninggal atau cedera tubuh akibat kecelakaan. Manfaat cacat sebagian biasanya ditetapkan lewat tabel persentase yang terkait cedera tertentu. Contoh produknya adalah :istilah[ADDB]{slug=addb}.
                :::

                :::contoh
                Kecelakaan yang dijamin dan menyebabkan kehilangan permanen satu anggota gerak membayar persentase tertentu dari UP sesuai tabel manfaat; meninggal karena kecelakaan membayar jumlah penuh.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Semua cedera atau musibah otomatis dijamin pertanggungan kecelakaan."**

                Tidak. Pertanggungan ini hanya merespons peristiwa yang memenuhi definisi "kecelakaan" pada polis. Penyebab yang berkaitan dengan penyakit ditangani oleh asuransi kesehatan atau penyakit kritis, bukan di sini.
                :::

                ## Yang perlu diperiksa

                - Bagaimana polis mendefinisikan "kecelakaan"?
                - Apa isi tabel persentase manfaatnya?
                - Aktivitas atau penyebab apa yang dikecualikan?

                ## Pelajari selanjutnya

                Berikutnya: rider — cara menambah lapisan proteksi di atas polis dasar.
                MD,
            ],
            [
                'slug' => 'rider-manfaat-tambahan',
                'title' => 'Rider (Manfaat Tambahan)',
                'category' => 'policy-process',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Rider adalah pertanggungan tambahan yang dilekatkan pada polis dasar — misalnya penyakit kritis, disabilitas, kecelakaan, atau proteksi pembayar premi.',
                'tags' => ['rider', 'polis'],
                'glossary' => ['rider', 'polis-dasar', 'manfaat-akselerasi', 'manfaat-non-akselerasi'],
                'takeaways' => [
                    'Rider hanya berlaku selama polis dasar yang kompatibel aktif.',
                    'Tidak semua rider cocok untuk semua produk; "ambil semua rider" bukan tujuan.',
                    'Sebagian rider mengurangi manfaat dasar ketika membayar (accelerated).',
                ],
                'body' => <<<'MD'
                :::definisi
                **Rider** adalah pertanggungan tambahan opsional yang dilekatkan pada :istilah[polis dasar]{slug=polis-dasar} — misalnya penyakit kritis, disabilitas, kecelakaan, atau proteksi pembayar premi. Rider hanya ada selama polis dasar aktif dan harus kompatibel dengannya.
                :::

                :::contoh
                Sebuah polis jiwa menjadi dasar. Pemegang polis menambahkan rider penyakit kritis dan rider pembayar premi, sehingga satu kontrak kini menjamin meninggal, diagnosis serius, dan kelanjutan pembayaran premi bila pembayar mengalami risiko.
                :::

                ## Kesalahpahaman umum

                :::kesalahpahaman
                **"Semakin banyak rider, semakin lengkap dan pasti lebih baik."**

                Tidak setiap rider cocok untuk setiap polis dasar, dan "ambil semua rider" bukan tujuan. Ketersediaan bergantung pada produk, usia, underwriting, dan jalur distribusi; prioritas mengikuti kebutuhan dan keterjangkauan. Sebagian rider bersifat :istilah[accelerated]{slug=manfaat-akselerasi} (mengurangi UP dasar saat membayar), sebagian :istilah[non-accelerated]{slug=manfaat-non-akselerasi}.
                :::

                ## Yang perlu diperiksa

                - Apakah rider kompatibel dengan polis dasar Anda?
                - Apakah ia mengurangi manfaat dasar saat membayar?
                - Apakah masih terjangkau dalam jangka panjang?

                ## Pelajari selanjutnya

                Berikutnya: asuransi syariah dan bagaimana risk sharing berbeda dari risk transfer.
                MD,
            ],
            [
                'slug' => 'reimbursement-vs-lump-sum',
                'title' => 'Reimbursement vs Lump Sum',
                'category' => 'health',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Manfaat asuransi dibayar dengan salah satu dari dua cara. Reimbursement mengganti biaya eligible sampai limit; lump sum membayar jumlah tetap atas suatu pemicu.',
                'tags' => ['manfaat', 'kesehatan'],
                'glossary' => ['reimbursement', 'lump-sum', 'indemnity', 'deductible', 'co-share'],
                'takeaways' => [
                    'Reimbursement mengikuti biaya nyata; lump sum membayar jumlah tetap atas suatu pemicu.',
                    'Keduanya menyelesaikan masalah berbeda: menutup tagihan vs mengganti penghasilan.',
                    'Lapisan proteksi yang baik sering menggabungkan keduanya.',
                ],
                'body' => <<<'MD'
                :::definisi
                Manfaat asuransi dibayar dengan salah satu dari dua cara. **:istilah[Reimbursement]{slug=reimbursement}** (indemnitas) mengganti biaya eligible Anda sampai limit, setelah :istilah[deductible]{slug=deductible} atau :istilah[co-share]{slug=co-share}. **:istilah[Lump sum]{slug=lump-sum}** membayar jumlah tetap atas suatu pemicu, tanpa melihat besarnya tagihan.
                :::

                | Model | Contoh | Cara kerja |
                |---|---|---|
                | Reimbursement / indemnitas | Asuransi biaya kesehatan | Membayar biaya eligible, limit, deductible/co-share, sesuai ketentuan |
                | Lump sum / santunan | Penyakit kritis, TPD, term life | Membayar atas pemicu, berdasarkan persentase UP; tidak harus sama dengan tagihan |

                :::kesalahpahaman
                **"Santunan lump sum berarti untung berlebih; reimbursement lebih 'pelit'."**

                Keduanya menyelesaikan masalah yang berbeda: menutup tagihan versus mengganti penghasilan dan mendanai pemulihan. Lapisan proteksi yang baik sering menggabungkan keduanya.
                :::

                ## Yang perlu diperiksa

                - Apakah manfaat ini mengikuti biaya nyata atau membayar jumlah tetap?
                - Limit, deductible, atau definisi apa yang berlaku?
                - Apakah lapisan proteksi Anda menutup tagihan **dan** penghasilan?

                ## Pelajari selanjutnya

                Berikutnya: asuransi syariah dan prinsip berbagi risiko.
                MD,
            ],
            [
                'slug' => 'asuransi-syariah',
                'title' => 'Asuransi Syariah',
                'category' => 'sharia',
                'level' => ContentLevel::Intermediate,
                'excerpt' => 'Asuransi syariah (takaful) dibangun atas prinsip tolong-menolong dan berbagi risiko, bukan pengalihan risiko. Peserta menyetor ke dana tabarru yang dipakai membantu peserta yang tertimpa kerugian.',
                'tags' => ['syariah', 'proteksi'],
                'glossary' => ['asuransi-syariah', 'tabarru', 'taawun', 'ujrah', 'wakalah-bil-ujrah', 'surplus-underwriting', 'kontribusi'],
                'takeaways' => [
                    'Asuransi syariah berbasis risk sharing lewat dana tabarru, bukan risk transfer.',
                    'Operator mengelola dana atas dasar akad dan menerima ujrah.',
                    'Bukan sekadar "asuransi konvensional dengan istilah berbeda" — mekanismenya memang berbeda.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Asuransi syariah (takaful)** dibangun atas prinsip :istilah[tolong-menolong]{slug=taawun} dan **berbagi risiko** alih-alih pengalihan risiko. Peserta menyetor :istilah[kontribusi]{slug=kontribusi} ke dana :istilah[tabarru']{slug=tabarru} yang dipakai membantu peserta lain yang tertimpa kerugian yang dijamin; operator mengelola dana atas dasar akad.
                :::

                ## Empat konsep kunci

                1. **Risk sharing** — risiko ditanggung kolektif oleh peserta lewat dana tabarru'.
                2. **Akad** — ada akad pengelolaan/komersial (tijarah) dan akad tabarru'.
                3. **Pengelolaan kontribusi** — alokasi dan pengelolaan dana dijelaskan sejak awal dalam akad.
                4. **Surplus / defisit underwriting** — selisih pendapatan dan pengeluaran dana tabarru' dalam satu periode, dikelola sesuai produk dan kaidah syariah.

                :::contoh
                Para peserta menyetor kontribusi ke kumpulan tabarru'. Klaim dibayar dari kumpulan itu. Bila terdapat :istilah[surplus underwriting]{slug=surplus-underwriting} pada suatu periode, perlakuannya ditetapkan oleh produk dan kaidah syariah.
                :::

                :::kesalahpahaman
                **"Asuransi syariah hanya asuransi biasa yang diganti istilahnya."**

                Mekanismenya benar-benar berbeda: risiko dibagi, dananya dimiliki peserta, dan pengelolaannya berbasis imbalan (:istilah[wakalah bil ujrah]{slug=wakalah-bil-ujrah}) — bukan pemindahan risiko ke perusahaan.
                :::

                ## Pelajari selanjutnya

                Berikutnya: [risk sharing vs risk transfer](/learn/risk-sharing-vs-risk-transfer) dibahas berdampingan.
                MD,
            ],
            [
                'slug' => 'risk-sharing-vs-risk-transfer',
                'title' => 'Risk Sharing vs Risk Transfer',
                'category' => 'sharia',
                'level' => ContentLevel::Intermediate,
                'excerpt' => 'Dua mekanisme berbeda untuk menghadapi dampak finansial risiko. Risk transfer memindahkannya ke perusahaan; risk sharing membaginya di antara peserta lewat dana bersama.',
                'tags' => ['syariah', 'konsep'],
                'glossary' => ['risk-transfer', 'risk-sharing', 'tabarru', 'premi', 'kontribusi'],
                'takeaways' => [
                    'Risk transfer: peserta membayar premi, perusahaan menanggung risiko sesuai kontrak.',
                    'Risk sharing: peserta menyetor ke dana bersama yang membayar peserta yang tertimpa kerugian.',
                    'Bedakan keduanya setiap kali sama-sama muncul dalam satu pembahasan.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Risk transfer** memindahkan dampak finansial risiko dari individu ke perusahaan asuransi: peserta membayar :istilah[premi]{slug=premi}, perusahaan menanggung risiko sesuai kontrak. **Risk sharing** membagi risiko di antara para peserta lewat dana bersama (:istilah[tabarru']{slug=tabarru}).
                :::

                | Aspek | Risk transfer (konvensional) | Risk sharing (syariah) |
                |---|---|---|
                | Pembayaran | Premi | :istilah[Kontribusi]{slug=kontribusi} |
                | Penanggung risiko | Perusahaan | Kumpulan peserta lewat tabarru' |
                | Peran perusahaan | Menanggung risiko | Mengelola dana atas dasar akad (imbalan/ujrah) |
                | Surplus | Milik perusahaan | Dikelola sesuai produk & kaidah syariah |

                :::penting
                Aturan editorial InsuraLab: bedakan **pengalihan risiko konvensional** dari **berbagi risiko syariah** setiap kali keduanya sama-sama muncul. Keduanya bukan sinonim.
                :::

                ## Pelajari selanjutnya

                Berikutnya: PAYDI / unit link — produk yang menggabungkan proteksi dengan komponen investasi.
                MD,
            ],
            [
                'slug' => 'paydi-unit-link',
                'title' => 'PAYDI / Unit Link',
                'category' => 'unit-link-paydi',
                'level' => ContentLevel::Intermediate,
                'excerpt' => 'PAYDI adalah produk asuransi yang dikaitkan dengan investasi. Ia menggabungkan proteksi dengan komponen investasi yang nilainya dapat naik dan turun serta tidak dijamin.',
                'tags' => ['paydi', 'unit-link', 'investasi'],
                'glossary' => ['paydi', 'unit-link', 'biaya-asuransi', 'cuti-premi', 'dana-investasi'],
                'takeaways' => [
                    'Nilai investasi PAYDI tidak dijamin kecuali kontrak menyatakannya secara eksplisit — dan bisa turun.',
                    'Biaya, penarikan, dan cuti premi dapat mengurangi nilai polis dan keberlanjutannya.',
                    'Cuti premi bukan proteksi gratis.',
                ],
                'body' => <<<'MD'
                :::definisi
                **PAYDI** (Produk Asuransi yang Dikaitkan dengan Investasi) menggabungkan proteksi dengan komponen investasi. Sebagian premi membayar proteksi dan biaya; sebagian dialokasikan ke unit investasi yang nilainya dapat naik atau turun. Sering disebut :istilah[unit link]{slug=unit-link}.
                :::

                ## Alur premi

                `premi/kontribusi → biaya & proteksi → alokasi investasi → nilai unit → manfaat polis`

                Manfaat menarik dari UP dan, bila relevan, dari nilai unit.

                :::penting
                Peringatan konsumen yang harus selalu menyertai pembahasan PAYDI:

                - Nilai investasi **tidak dijamin** kecuali kontrak produk menyatakannya secara eksplisit.
                - Nilai unit **dapat turun**; ini bukan deposito.
                - :istilah[Biaya asuransi]{slug=biaya-asuransi} dan biaya lain dapat mengurangi nilai polis.
                - Bila nilai investasi tidak cukup membayar biaya, polis mungkin butuh setoran tambahan atau tidak berkelanjutan.
                - :istilah[Cuti premi]{slug=cuti-premi} **bukan** proteksi gratis — biaya tetap dapat dipotong.
                - **Penarikan** dapat mengurangi nilai investasi dan keberlanjutan polis.
                :::

                :::kesalahpahaman
                **"Unit link berarti investasi pasti untung."**

                Tidak. Nilai investasi dapat naik atau turun dan tidak dijamin. Proyeksi ilustrasi bukan janji hasil.
                :::

                ## Yang perlu diperiksa

                - Apa saja biayanya, dan untuk berapa tahun?
                - Apa yang terjadi bila nilai dana tidak menutup biaya?
                - Apakah polis diproyeksikan bertahan selama Anda membutuhkannya?

                ## Pelajari selanjutnya

                Berikutnya: underwriting — bagaimana penanggung menilai dan mengklasifikasi risiko.
                MD,
            ],
            [
                'slug' => 'underwriting',
                'title' => 'Underwriting',
                'category' => 'policy-process',
                'level' => ContentLevel::Intermediate,
                'excerpt' => 'Underwriting adalah cara penanggung memutuskan apakah menerima suatu risiko dan dengan syarat apa. Ia mengidentifikasi risiko, mengklasifikasinya, lalu mengambil keputusan.',
                'tags' => ['underwriting', 'proses'],
                'glossary' => ['underwriting', 'field-underwriting', 'medical-underwriting', 'financial-underwriting', 'moral-hazard'],
                'takeaways' => [
                    'Permintaan pemeriksaan medis bukan penolakan — biasanya berarti butuh informasi tambahan.',
                    'Agen adalah field underwriter (pengumpul informasi), bukan pengambil keputusan.',
                    'Fakta material harus diungkap jujur dan lengkap; menyembunyikan riwayat berisiko membatalkan klaim.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Underwriting** adalah cara penanggung memutuskan apakah menerima suatu risiko dan dengan syarat apa. Alurnya: identifikasi risiko → klasifikasi risiko → keputusan.
                :::

                ## Kemungkinan hasil

                | Hasil | Arti |
                |---|---|
                | Standar | Diterima dengan syarat standar |
                | Premi ekstra / rating | Diterima dengan tambahan biaya risiko |
                | Pengecualian | Suatu risiko/kondisi dikeluarkan |
                | Manfaat dimodifikasi | Manfaat / UP disesuaikan |
                | Butuh informasi tambahan | Perlu laporan medis/keuangan atau klarifikasi |
                | Ditunda (postpone) | Keputusan ditangguhkan sampai kondisi bisa dinilai |
                | Ditolak (decline) | Risiko tidak dapat diterima menurut kebijakan underwriting |

                :::contoh
                Seorang pemohon mengungkap riwayat penyakit. Penanggung dapat menerima dengan tarif standar, menambah rating, mengecualikan kondisi itu, atau meminta laporan medis lebih dulu sebelum memutuskan.
                :::

                :::kesalahpahaman
                **"Diminta medical check-up berarti sudah pasti ditolak."**

                Tidak. Permintaan pemeriksaan biasanya berarti penanggung butuh informasi lebih. Selain itu, agen adalah :istilah[field underwriter]{slug=field-underwriting} — pengumpul informasi — bukan pengambil keputusan akhir.
                :::

                :::penting
                Ungkapkan seluruh fakta medis dan gaya hidup yang ditanyakan secara jujur dan lengkap. Menyembunyikan riwayat yang ditanyakan dapat membuat klaim di kemudian hari gugur.
                :::

                ## Pelajari selanjutnya

                Berikutnya: proses bisnis asuransi secara menyeluruh, dari prospek sampai layanan.
                MD,
            ],
            [
                'slug' => 'proses-bisnis-asuransi',
                'title' => 'Proses Bisnis Asuransi',
                'category' => 'policy-process',
                'level' => ContentLevel::Intermediate,
                'excerpt' => 'Alur menyeluruh: prospek, analisis kebutuhan, pengajuan, underwriting, inforce, penyerahan polis, dan layanan. Kebutuhan baru menjadi proteksi setelah polis terbit, diterima, lalu dilayani.',
                'tags' => ['proses', 'operasional'],
                'glossary' => ['underwriting', 'inforce', 'spaj', 'welcome-call'],
                'takeaways' => [
                    'Mengajukan dan membayar bukan berarti sudah dijamin.',
                    'Menutup penjualan bukan akhir proses — penyerahan polis, layanan, dan peninjauan tahunan berlanjut.',
                    'Target pengajuan: LENGKAP + AKURAT + KONSISTEN sejak submission pertama.',
                ],
                'body' => <<<'MD'
                ## Alur menyeluruh

                ```
                Prospek → Analisis Kebutuhan → Pengajuan → Underwriting → Inforce → Penyerahan Polis → Layanan
                ```

                Sebuah kebutuhan baru menjadi proteksi nyata setelah pengajuan diproses, :istilah[underwriting]{slug=underwriting} selesai, syarat terpenuhi, polis diterbitkan, diterima nasabah, lalu dilayani sepanjang usia polis.

                :::penting
                Target kualitas pengajuan: **LENGKAP + AKURAT + KONSISTEN** sejak submission pertama ("Right First Time"). Foto identitas buram, tanda tangan tidak cocok, pertanyaan kesehatan tidak lengkap, atau premi proposal berbeda dari :istilah[SPAJ]{slug=spaj} semuanya menyebabkan pending.
                :::

                :::kesalahpahaman
                **"Sudah tanda tangan dan bayar, berarti sudah dijamin."**

                Mengajukan dan membayar tidak sama dengan :istilah[inforce]{slug=inforce}. Menutup penjualan juga bukan akhir proses — penyerahan polis, :istilah[welcome call]{slug=welcome-call}, layanan, dan peninjauan tahunan berlangsung bertahun-tahun.
                :::

                ## Yang perlu diperiksa

                - Apakah setiap formulir lengkap, terbaca, dan konsisten?
                - Kapan pertanggungan menjadi efektif?
                - Apakah Anda tahu cara melayani dan mengajukan klaim atas polis?

                ## Pelajari selanjutnya

                Berikutnya: etika dalam penjualan asuransi.
                MD,
            ],
            [
                'slug' => 'etika-penjualan-asuransi',
                'title' => 'Etika dalam Penjualan Asuransi',
                'category' => 'ethics-conduct',
                'level' => ContentLevel::Intermediate,
                'excerpt' => 'Karena asuransi berjalan di atas kepercayaan, penjualan harus legal, adil, transparan, dan akuntabel. Pelanggaran yang dikenal antara lain mis-selling, misrepresentation, churning, dan twisting.',
                'tags' => ['etika', 'kepatuhan'],
                'glossary' => ['mis-selling', 'misrepresentation', 'churning', 'twisting'],
                'takeaways' => [
                    'Persetujuan nasabah tidak membuat sebuah penjualan menjadi etis.',
                    'Uji cepat: legal? diperbolehkan? adil bagi nasabah? tahan jika jadi konsumsi publik?',
                    'Bila ragu: STOP → TANYA → ESKALASI.',
                ],
                'body' => <<<'MD'
                :::definisi
                Karena asuransi berjalan di atas **kepercayaan**, penjualan harus legal, adil, transparan, dan akuntabel. Nasabah menyerahkan data identitas, kesehatan, keuangan, dan tujuan hidupnya.
                :::

                ## Risiko konduk yang dikenal

                | Istilah | Arti |
                |---|---|
                | :istilah[Mis-selling]{slug=mis-selling} | Menjual produk yang tidak sesuai kebutuhan/kemampuan nasabah |
                | :istilah[Misrepresentation]{slug=misrepresentation} | Memberi gambaran/janji/informasi yang tidak sesuai kenyataan atau dokumen produk |
                | :istilah[Churning]{slug=churning} | Mendorong penggantian polis di perusahaan yang sama tanpa analisis memadai |
                | :istilah[Twisting]{slug=twisting} | Mendorong penggantian polis perusahaan lain secara tidak patut |

                :::kesalahpahaman
                **"Kalau nasabah setuju, berarti penjualannya sudah pasti etis."**

                Persetujuan nasabah tidak membuat sebuah penjualan menjadi etis. Ujinya: apakah legal, apakah diperbolehkan, apakah adil bagi nasabah, dan apakah akan tahan bila menjadi konsumsi publik. Bila ragu: **STOP → TANYA → ESKALASI.**
                :::

                ## Yang perlu diperiksa

                - Apakah produk benar-benar sesuai kebutuhan dan anggaran?
                - Apakah semua fakta material diungkap secara jujur?
                - Apakah penggantian pertanggungan yang ada jelas menguntungkan nasabah?

                ## Pelajari selanjutnya

                Berikutnya: dasar AML/KYC yang perlu dipahami setiap nasabah.
                MD,
            ],
            [
                'slug' => 'dasar-aml-kyc',
                'title' => 'Dasar AML / KYC',
                'category' => 'ethics-conduct',
                'level' => ContentLevel::Advanced,
                'excerpt' => 'Anti Pencucian Uang (APU) dan Know Your Customer (KYC) memastikan penanggung memahami siapa nasabahnya, dari mana dananya, dan untuk apa transaksinya.',
                'tags' => ['kepatuhan', 'aml', 'kyc'],
                'glossary' => ['kyc', 'cdd-edd', 'beneficial-owner', 'pep', 'fatca', 'crs'],
                'takeaways' => [
                    'KYC/CDD: identifikasi → verifikasi → pahami → pantau.',
                    'Status PEP adalah klasifikasi risiko, bukan tuduhan perbuatan salah.',
                    'Agen mengisi data FATCA/CRS apa adanya, tidak berdasarkan asumsi, dan tidak mengarahkan penyembunyian.',
                ],
                'body' => <<<'MD'
                :::definisi
                **APU** (Anti Pencucian Uang) dan **PPT** (Pencegahan Pendanaan Terorisme) mensyaratkan penanggung memahami siapa nasabahnya. **:istilah[KYC]{slug=kyc}** (Know Your Customer) mencakup identitas, pekerjaan, sumber dana, tujuan transaksi, pembayar, dan penerima manfaat.
                :::

                ## Konsep inti

                - **:istilah[CDD dan EDD]{slug=cdd-edd}** — uji tuntas standar dan uji tuntas mendalam untuk nasabah berisiko lebih tinggi.
                - **:istilah[Beneficial owner]{slug=beneficial-owner}** — pihak yang sesungguhnya memiliki/mengendalikan manfaat atau struktur.
                - **:istilah[PEP]{slug=pep}** — orang dengan fungsi publik menonjol.
                - Tahapan pencucian uang: **Placement → Layering → Integration.**

                :::kesalahpahaman
                **"Nasabah berstatus PEP berarti pasti bermasalah."**

                Tidak. PEP adalah **klasifikasi risiko**, bukan tuduhan. Ia dapat memerlukan EDD, tetapi tidak otomatis menyiratkan perbuatan salah.
                :::

                :::penting
                Untuk :istilah[FATCA]{slug=fatca} dan :istilah[CRS]{slug=crs}, agen mengisi data residensi pajak dan TIN **apa adanya sesuai formulir** — bukan berdasarkan asumsi — dan tidak pernah mengarahkan nasabah menyembunyikan residensi pajak atau TIN.
                :::

                ## Pelajari selanjutnya

                Berikutnya: perlindungan data nasabah — prinsip menangani informasi yang sangat sensitif.
                MD,
            ],
            [
                'slug' => 'perlindungan-data-nasabah',
                'title' => 'Perlindungan Data Nasabah',
                'category' => 'ethics-conduct',
                'level' => ContentLevel::Advanced,
                'excerpt' => 'Agen menangani data yang sangat sensitif: identitas, rekening bank, informasi keluarga, data kesehatan, hasil pemeriksaan medis, dan keuangan. Akses bukan izin untuk memakainya bagi tujuan lain.',
                'tags' => ['kepatuhan', 'data', 'privasi'],
                'glossary' => ['kyc'],
                'takeaways' => [
                    'Minimalkan data yang dikumpulkan; pakai hanya untuk tujuan sah yang dinyatakan.',
                    'Simpan dan kirim data lewat kanal yang aman; bagikan hanya kepada pihak berwenang.',
                    'Ikuti kebijakan retensi dan pemusnahan.',
                ],
                'body' => <<<'MD'
                :::definisi
                Agen menangani data yang sangat sensitif: identitas, rekening bank, informasi keluarga, data kesehatan, hasil pemeriksaan medis, dan keuangan. **Akses bukan berarti izin** untuk memakai data itu bagi tujuan lain.
                :::

                ## Prinsip

                - **Minimalisasi data** — kumpulkan hanya yang diperlukan.
                - **Pembatasan tujuan** — gunakan hanya untuk tujuan sah yang dinyatakan.
                - **Keamanan** — simpan dan kirim lewat kanal yang aman.
                - **Pengungkapan yang berwenang** — bagikan hanya kepada pihak yang berwenang.
                - **Retensi yang tepat** — ikuti kebijakan penyimpanan dan pemusnahan.

                :::kesalahpahaman
                **"Karena saya agennya, saya boleh menyimpan dan memakai data nasabah sesuka saya."**

                Tidak. Data dikumpulkan untuk tujuan tertentu dan hanya boleh dipakai untuk tujuan itu, disimpan aman, dan dibagikan hanya kepada pihak berwenang.
                :::

                ## Pelajari selanjutnya

                Berikutnya: peran agen asuransi — apa yang boleh dan tidak boleh mereka lakukan.
                MD,
            ],
            [
                'slug' => 'peran-agen-asuransi',
                'title' => 'Peran Agen Asuransi',
                'category' => 'ethics-conduct',
                'level' => ContentLevel::Beginner,
                'excerpt' => 'Agen berlisensi membantu Anda memahami risiko dan celah proteksi, menjelaskan produk secara berimbang, membantu pengajuan, memastikan kualitas data, menyerahkan polis, dan membantu layanan serta klaim.',
                'tags' => ['agen', 'etika'],
                'glossary' => ['field-underwriting', 'klaim', 'underwriting'],
                'takeaways' => [
                    'Agen TIDAK membuat keputusan akhir underwriting maupun klaim.',
                    'Agen tidak boleh menjamin persetujuan atau hasil klaim.',
                    'Nilai agen ada pada analisis kebutuhan yang akurat dan penjelasan yang jujur — bukan tekanan.',
                ],
                'body' => <<<'MD'
                :::definisi
                Seorang **agen berlisensi** membantu Anda memahami risiko dan celah proteksi, menjelaskan produk secara berimbang (manfaat, biaya, risiko, pengecualian, masa pertanggungan, kewajiban nasabah), membantu pengajuan, memastikan kualitas data, menyerahkan dan menjelaskan polis, serta membantu layanan dan klaim.
                :::

                :::contoh
                Saat klaim, agen membantu menyusun dokumen yang tepat dan menjelaskan prosesnya — sementara penanggung, bukan agen, yang memutuskan hasilnya.
                :::

                :::penting
                Agen **tidak** membuat keputusan akhir :istilah[underwriting]{slug=underwriting} maupun :istilah[klaim]{slug=klaim}, dan tidak boleh menjamin persetujuan atau hasil klaim. Dalam underwriting, agen berperan sebagai :istilah[field underwriter]{slug=field-underwriting} — sumber informasi awal.
                :::

                :::kesalahpahaman
                **"Kalau agen bilang klaim pasti cair, berarti aman."**

                Tidak seorang pun boleh menjamin hasil klaim. Klaim dinilai terhadap ketentuan polis oleh penanggung.
                :::

                ## Yang perlu diperiksa

                - Apakah rekomendasi terkait dengan kebutuhan yang dinyatakan dan anggaran Anda?
                - Apakah biaya, risiko, dan pengecualian dijelaskan — bukan hanya manfaat?
                - Apakah agen berlisensi secara sah?

                ## Pelajari selanjutnya

                Berikutnya: distribusi asuransi — mengapa jalur penjualan berbeda dari penerbit polis.
                MD,
            ],
            [
                'slug' => 'distribusi-asuransi',
                'title' => 'Distribusi Asuransi',
                'category' => 'ethics-conduct',
                'level' => ContentLevel::Intermediate,
                'excerpt' => 'Distribusi adalah cara produk asuransi sampai ke nasabah: keagenan, bancassurance, broker, atau kanal digital. Jalur distribusi berbeda dari perusahaan yang menerbitkan polis.',
                'tags' => ['distribusi', 'keagenan'],
                'glossary' => ['be', 'bp', 'upline-downline'],
                'takeaways' => [
                    'Jalur distribusi (mis. jaringan keagenan) berbeda dari penerbit dan pengelola polis.',
                    'Perusahaan asuransi yang namanya tertera di kontrak yang menanggung risiko.',
                    'Inilah alasan arsitektur InsuraLab bersifat provider-agnostic: distribusi bukan penanggung.',
                ],
                'body' => <<<'MD'
                :::definisi
                **Distribusi** adalah cara produk asuransi sampai ke nasabah — lewat jaringan keagenan, bancassurance, broker, atau kanal digital. Jalur distribusi **berbeda** dari perusahaan yang menerbitkan dan mengelola polis.
                :::

                :::penting
                Perusahaan asuransi (penerbit polis) berbeda dari jaringan distribusi. Sebuah jaringan keagenan adalah kanal penjualan; polis tetap diterbitkan dan dikelola oleh entitas perusahaan asuransi yang namanya tertera di kontrak — pihak itulah yang menanggung risiko.
                :::

                :::kesalahpahaman
                **"Jaringan keagenan tempat saya membeli polis sama dengan perusahaan asuransinya."**

                Bukan. Jaringan keagenan mendistribusikan; entitas asuransi yang menanggung. Dalam sebuah studi kasus provider, misalnya, sebuah jaringan seperti "Star Network" adalah kanal keagenan, sedangkan polis diterbitkan oleh entitas asuransi jiwa terkait.
                :::

                ## Mengapa ini penting bagi InsuraLab

                Karena distribusi bukan penanggung, model data InsuraLab bersifat **provider-agnostic**: "provider" adalah variabel, dan pengetahuan umum dipisahkan dari materi satu perusahaan.

                ## Pelajari selanjutnya

                Lihat halaman [Providers](/providers) untuk studi kasus provider pertama, dan [Products](/products) untuk catatan produk edukatif.
                MD,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function providerArticles(): array
    {
        return [
            [
                'slug' => 'allianz-di-indonesia-tiga-entitas-dan-jaringan-keagenan',
                'title' => 'Allianz di Indonesia: Tiga Entitas dan Jaringan Keagenan',
                'category' => 'ethics-conduct',
                'status' => ReferenceStatus::NeedsReview,
                'excerpt' => 'Di Indonesia, Allianz beroperasi melalui tiga entitas hukum yang berbeda, dan didistribusikan lewat Allianz Star Network (ASN). Entitas yang tercantum di polis adalah pihak yang menanggung risiko; ASN adalah kanal, bukan penerbit polis.',
                'tags' => ['allianz', 'entitas', 'distribusi', 'asn', 'referensi-pelatihan'],
                'glossary' => ['be', 'bp', 'upline-downline'],
                'takeaways' => [
                    'Tiga entitas Indonesia — Life, Utama, Life Syariah — tidak boleh disamakan.',
                    'Entitas yang namanya di polis adalah pihak yang menanggung risiko kontraktual.',
                    'ASN adalah kanal keagenan/distribusi, bukan perusahaan asuransinya.',
                    'Detail korporat terkini belum diverifikasi independen — perlakukan sebagai gambaran edukatif.',
                ],
                'body' => <<<'MD'
                :::catatan-provider
                Konten ini membahas informasi yang terkait dengan **provider tertentu (Allianz)**. InsuraLab adalah platform edukasi independen dan bukan situs resmi Allianz. Detail korporat dapat berubah.
                :::

                ## Konsep

                Di Indonesia, Allianz beroperasi melalui **tiga entitas hukum yang berbeda**. Yang krusial untuk dipahami konsumen: **entitas yang tercantum pada polis adalah pihak yang menanggung risiko kontraktual**.

                | Entitas | Fokus (menurut materi pelatihan) |
                |---|---|
                | **Allianz Life Indonesia** | Jiwa, kesehatan, dan solusi perlindungan finansial individu/keluarga |
                | **Allianz Utama Indonesia** | Asuransi umum — kendaraan, properti, bisnis, perjalanan, dan risiko non-jiwa lain |
                | **Allianz Life Syariah Indonesia** | Perlindungan jiwa dan pengelolaan risiko finansial berbasis prinsip syariah |

                :::penting
                Ketiganya **tidak boleh disamakan** menjadi satu "Allianz". Produk syariah menggunakan mekanisme berbagi risiko dan istilah "kontribusi" — berbeda dari pengalihan risiko pada produk konvensional.
                :::

                ## Jaringan keagenan: Allianz Star Network (ASN)

                **ASN** adalah kanal keagenan, distribusi, dan pengembangan bisnis Allianz Life. Dalam materi pelatihan, ASN menjadi konteks jenjang karier pemasar, struktur :istilah[BE]{slug=be}/:istilah[BP]{slug=bp}, unit/agency/group agency, serta model kompensasi.

                :::penting
                Perusahaan asuransi (penerbit polis) berbeda dari jaringan distribusi. ASN adalah kanal keagenan; polis tetap diterbitkan dan dikelola oleh entitas perusahaan asuransi yang namanya tertera di kontrak. Inilah alasan model InsuraLab bersifat **provider-agnostic**: distribusi bukan penanggung.
                :::

                :::perlu-verifikasi
                Detail korporat terkini (izin, kepemilikan, struktur organisasi, lingkup produk per entitas) belum diverifikasi secara independen dalam proyek ini. Perlakukan uraian di atas sebagai gambaran edukatif dari materi pelatihan, bukan pernyataan resmi yang berlaku sekarang.
                :::

                ## Pelajari konsepnya lebih dulu

                Lihat [Distribusi Asuransi](/learn/distribusi-asuransi) dan [Asuransi Syariah](/learn/asuransi-syariah).
                MD,
            ],
            [
                'slug' => 'bagaimana-allianz-flexi-ci-dijelaskan-dalam-materi-pelatihan',
                'title' => 'Bagaimana Allianz Flexi CI Dijelaskan dalam Materi Pelatihan',
                'category' => 'critical-illness',
                'status' => ReferenceStatus::NeedsReview,
                'excerpt' => 'Menurut kompilasi Allianz Basic Training (Agustus 2026), Flexi CI adalah rider penyakit kritis yang membayar santunan sekaligus pada diagnosis, terbagi menjadi tiga tingkat: Silver, Gold, dan Platinum.',
                'tags' => ['allianz', 'flexi-ci', 'penyakit-kritis', 'referensi-pelatihan'],
                'glossary' => ['penyakit-kritis', 'lump-sum', 'elimination-period', 'survival-period', 'manfaat-akselerasi', 'uang-pertanggungan'],
                'takeaways' => [
                    'Seluruh angka di bawah adalah referensi pelatihan, bukan ketentuan produk yang berlaku saat ini.',
                    '"Sampai ~150% UP" menggambarkan akumulasi lintas riwayat klaim — bukan berarti satu diagnosis membayar 150%.',
                    'Verifikasi setiap angka terhadap RIPLAY dan ketentuan polis terkini sebelum digunakan.',
                ],
                'body' => <<<'MD'
                :::catatan-provider
                Konten ini membahas informasi yang terkait dengan **provider tertentu (Allianz)** berdasarkan materi pelatihan keagenan. Ketentuan produk dan proses dapat berubah. Sumber, periode referensi, tanggal tinjau, dan status referensi ditampilkan di bawah artikel.
                :::

                ## Konsep

                Menurut kompilasi **Allianz Basic Training (Agustus 2026)**, Flexi CI adalah rider :istilah[penyakit kritis]{slug=penyakit-kritis} yang membayar :istilah[santunan sekaligus]{slug=lump-sum} pada diagnosis, terbagi menjadi tiga tingkat: Silver, Gold, dan Platinum.

                ## Referensi jumlah kondisi (materi pelatihan)

                | Kelompok manfaat | Silver | Gold | Platinum |
                |---|---|---|---|
                | Advanced CI | 77 | 77 | 77 |
                | Catastrophic CI | 6 | 6 | 6 |
                | Komplikasi diabetes | 4 | 4 | 4 |
                | Angioplasty | 1 | 1 | 1 |
                | Early CI | – | 30 | 60 |
                | Intermediate CI | – | 10 | 20 |
                | **Total kondisi** | **88** | **128** | **168** |

                ## Referensi fitur (materi pelatihan — DYNAMIC RULE)

                - Usia masuk: **1 bulan – 70 tahun** (usia ulang tahun terdekat).
                - Proteksi: **sampai usia 100** sesuai ketentuan.
                - :istilah[Elimination period]{slug=elimination-period}: **80 hari** sejak rider berlaku.
                - :istilah[Survival period]{slug=survival-period}: **0 hari**.
                - Underwriting: **full underwriting**, mengikuti polis dasar.
                - Mata uang: **IDR**.

                ## Referensi manfaat (materi pelatihan — DYNAMIC RULE)

                | Kategori | Manfaat | Maksimum yang ditampilkan pelatihan |
                |---|---|---|
                | Advanced CI | 100% :istilah[UP]{slug=uang-pertanggungan} | Rp5.000.000.000 |
                | Catastrophic CI | tambahan 20% UP | — |
                | Komplikasi diabetes | tambahan 20% UP | Rp200.000.000 |
                | Angioplasty | tambahan 10% UP | Rp200.000.000 |
                | Early CI | 50% UP | Rp1.250.000.000 |
                | Intermediate CI | 100% UP | Rp2.500.000.000 |

                :::perlu-verifikasi
                Angka-angka ini adalah **referensi pelatihan**. Simpan dan tampilkan sebagai informasi yang sadar-tanggal (status: perlu verifikasi) hingga diperiksa ulang terhadap dokumen resmi terkini. Jangan menampilkannya sebagai ketentuan produk yang permanen atau dijamin saat ini.
                :::

                :::penting
                Materi pelatihan menyebut total manfaat **sampai ~150% dari UP** tergantung struktur klaim dan ketentuan. Ini **tidak** berarti satu diagnosis otomatis membayar 150% — besar pembayaran bergantung pada tahap, kondisi, manfaat sebelumnya, limit, dan ketentuan produk.
                :::

                ## Cost of Rider (COR) — konsep pelatihan (DYNAMIC RULE)

                - Tahun 1: COR tidak dipotong / ditangguhkan (dibebankan pada tahun 2–3 sesuai skema pelatihan).
                - Tahun 2: 100% COR tahun berjalan + 50% COR tahun pertama yang ditangguhkan.
                - Tahun 3: 100% COR tahun berjalan + sisa 50% COR tahun pertama.
                - Tahun 4 dst: 100% COR tahun berjalan.
                - **Rujuk dokumentasi produk resmi terkini sebelum penggunaan operasional.**

                ## Prosedur klaim — referensi pelatihan (NEEDS VERIFICATION)

                Lengkapi formulir klaim CI beserta dokumen; kirim dalam tenggat pada polis/prosedur — sebuah tangkapan layar pelatihan menunjukkan **60 hari sejak diagnosis**. Perlakukan angka 60 hari sebagai referensi pelatihan yang perlu diverifikasi terhadap ketentuan polis terkini.

                ## Pelajari konsepnya lebih dulu

                Sebelum bergantung pada "Apa itu Flexi CI?", pahami dulu: [Apa Itu Penyakit Kritis?](/learn/penyakit-kritis), [Reimbursement vs Lump Sum](/learn/reimbursement-vs-lump-sum), [Rider](/learn/rider-manfaat-tambahan), dan [Uang Pertanggungan](/learn/manfaat-dan-uang-pertanggungan).
                MD,
            ],
            [
                'slug' => 'struktur-keagenan-allianz-be-bp-dan-generasi',
                'title' => 'Struktur Keagenan Allianz: BE, BP, dan Generasi',
                'category' => 'agency-compensation',
                'status' => ReferenceStatus::NeedsReview,
                'excerpt' => 'Dalam struktur pelatihan Allianz Star Network (ASN), BE adalah tingkat pemasar awal dan BP adalah tingkat kepemimpinan setelah aturan promosi terpenuhi. Upline/downline menggambarkan posisi hierarki; pangkat berbeda dari relasi.',
                'tags' => ['allianz', 'keagenan', 'asn', 'referensi-pelatihan'],
                'glossary' => ['be', 'bp', 'upline-downline', 'unit-agency-group-agency', 'generasi', 'nett-alp', 'persistency'],
                'takeaways' => [
                    'Ini pengetahuan provider lanjutan, bukan ajakan rekrutmen — InsuraLab tidak membangun kalkulator kompensasi.',
                    'Promosi "Opsi 3" bersifat inkonsisten secara internal dan sengaja tidak diselesaikan.',
                    'Tabel kualifikasi generasi terikat pada periode validasi 2021–2022.',
                ],
                'body' => <<<'MD'
                :::catatan-provider
                Konten ini adalah pengetahuan **provider-spesifik lanjutan** dari materi pelatihan keagenan Allianz. Ia bersifat edukatif — bukan ajakan rekrutmen — dan InsuraLab **tidak** membangun kalkulator kompensasi.
                :::

                ## Konsep

                Dalam struktur pelatihan **Allianz Star Network (ASN)**, :istilah[BE]{slug=be} (Business Executive) adalah tingkat pemasar awal dan :istilah[BP]{slug=bp} (Business Partner) adalah tingkat kepemimpinan setelah aturan promosi terpenuhi. :istilah[Upline/downline]{slug=upline-downline} menggambarkan posisi hierarki; pangkat berbeda dari relasi.

                Materi pelatihan menggambarkan :istilah[unit, agency, dan group agency]{slug=unit-agency-group-agency} serta generasi BP **G1–G8**. Aturan promosi dan maintaining mengacu pada :istilah[persistency]{slug=persistency} 13 bulan minimum 80% dan ambang :istilah[Nett ALP]{slug=nett-alp}.

                ## Promosi BE → BP (referensi pelatihan — DYNAMIC RULE)

                **Opsi 1** — Personal Nett ALP minimum Rp300.000.000; persistency 13 bulan minimum 80%.

                **Opsi 2** — Personal Nett ALP minimum Rp200.000.000 (akumulasi maks. 12 bulan sejak bergabung); minimum 2 BE langsung; gabungan Nett ALP BE langsung Rp100.000.000 (minimum Rp50.000.000 tiap BE); persistency 13 bulan minimum 80%.

                :::perlu-verifikasi
                **Opsi 3 — sumber tidak konsisten, sengaja tidak diselesaikan.**

                - **Teks** pelatihan menyebut: Personal Nett ALP Rp100.000.000; minimal 2 BE langsung; gabungan Nett ALP BE langsung Rp100.000.000; minimum Rp50.000.000 tiap BE; persistency 80%.
                - **Diagram** pelatihan tampak menunjukkan **5 BE langsung** dengan ALP kira-kira **50 / 50 / 30 / 35 / 35 juta**.

                Perbedaan ini **tidak diselesaikan secara diam-diam**. Catat sebagai: *PERLU VERIFIKASI TERHADAP PANDUAN KOMPENSASI RESMI.* Menjaga inkonsistensi sumber tetap terlihat adalah prinsip inti kepercayaan InsuraLab.
                :::

                ## Maintaining BP (referensi pelatihan — DYNAMIC RULE)

                - Nett Unit ALP minimum Rp300.000.000.
                - Persistency 13 bulan minimum 80%.
                - Minimum 2 BE langsung aktif. Definisi pelatihan "BE aktif": 6 kasus/tahun, ATAU BE dipromosikan menjadi BP.

                :::referensi-historis
                Kualifikasi generasi (G1–G8) pada materi pelatihan secara eksplisit terikat pada periode validasi tertentu (validasi BP akhir 2021; blok bisnis baru > 2022). Perlakukan sebagai skema untuk periode tersebut, bukan aturan yang berlaku umum saat ini.
                :::

                ## Pelajari konsepnya lebih dulu

                Lihat [Distribusi Asuransi](/learn/distribusi-asuransi) untuk memahami mengapa jaringan keagenan berbeda dari penerbit polis.
                MD,
            ],
            [
                'slug' => 'komisi-dasar-paydi-allianz-referensi-historis',
                'title' => 'Komisi Dasar PAYDI Allianz (Referensi Historis)',
                'category' => 'agency-compensation',
                'status' => ReferenceStatus::Historical,
                'excerpt' => 'Referensi pelatihan keagenan historis untuk komisi dasar pada SmartLink Protection Life dan AlliSya Protection Life. Persentase berlaku atas dasar komisi produk, bukan atas Uang Pertanggungan.',
                'tags' => ['allianz', 'kompensasi', 'historis', 'paydi'],
                'glossary' => ['paydi', 'fyc-syc', 'overriding'],
                'takeaways' => [
                    'Tarif ini historis dan provider-spesifik — jangan dipakai sebagai panduan kompensasi terkini.',
                    'Persentase berlaku atas dasar komisi produk, bukan atas Uang Pertanggungan.',
                    'Aturan kompensasi diversi berdasarkan produk, skema, dan tanggal berlaku.',
                ],
                'body' => <<<'MD'
                :::referensi-historis
                Informasi ini disimpan untuk tujuan pembelajaran dan menggambarkan materi pada periode tertentu. **Jangan** menganggapnya sebagai ketentuan yang masih berlaku saat ini.
                :::

                ## Konsep

                Referensi pelatihan keagenan historis untuk komisi dasar pada :istilah[PAYDI]{slug=paydi} **SmartLink Protection Life** dan **AlliSya Protection Life**:

                | Tahun | Tarif |
                |---|---|
                | Tahun 1 | 23,25% |
                | Tahun 2 | 23,25% |
                | Tahun 3 | 5% |
                | Tahun 4 | 5% |
                | Tahun 5 | 5% |
                | Top Up / Premi Tunggal | 3% |

                :::penting
                Persentase berlaku atas **dasar komisi yang didefinisikan produk**, **bukan** atas Uang Pertanggungan. Contoh konsep: ":istilah[55% × FYC]{slug=fyc-syc}" berarti 55% dari First Year Commission yang eligible — bukan 55% dari premi nasabah.
                :::

                ## Catatan

                Set pelatihan yang sama juga memuat jadwal historis untuk Legacy Pro, Legacy Max (syariah), Allianz PASTI, AlliSya Aman, dan Individual Health (15% dari gross premium, berbasis perpanjangan). Semuanya bersifat **historis dan provider-spesifik**, dipertahankan hanya untuk sejarah edukatif, dan tidak boleh ditampilkan sebagai panduan kompensasi terkini. Aturan kompensasi diversi berdasarkan produk, skema, hierarki, kualifikasi, produksi, persistency, dan tanggal berlaku.

                ## Pelajari konsepnya lebih dulu

                Lihat [PAYDI / Unit Link](/learn/paydi-unit-link) dan [Distribusi Asuransi](/learn/distribusi-asuransi).
                MD,
            ],
        ];
    }
}
