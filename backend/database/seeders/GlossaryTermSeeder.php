<?php

namespace Database\Seeders;

use App\Enums\ReferenceStatus;
use App\Models\GlossaryTerm;
use Illuminate\Database\Seeder;

/**
 * Plain-language insurance glossary, primary language Bahasa Indonesia, with the
 * standard English term in parentheses where it helps. Slugs are explicit and
 * stable (CONTENT_GUIDE §10, ADR-0006) so /glossary/underwriting keeps working.
 */
class GlossaryTermSeeder extends Seeder
{
    public function run(): void
    {
        // [slug, term, category, short_definition, definition]
        $terms = [
            ['risiko', 'Risiko (Risk)', 'general',
                'Kemungkinan terjadinya peristiwa tidak pasti yang membuat kondisi keuangan Anda memburuk.',
                'Dalam asuransi, yang dinilai bukan peristiwanya saja melainkan **dampak finansialnya** dan apakah sebuah rumah tangga sanggup menanggungnya. Risiko besar-tapi-jarang (misalnya penyakit kritis) ditangani berbeda dari risiko kecil-tapi-sering.'],
            ['premi', 'Premi (Premium)', 'general',
                'Pembayaran yang dilakukan agar polis asuransi tetap aktif.',
                'Premi dapat bersifat bulanan, tahunan, atau sekaligus. Besarnya mencerminkan risiko yang ditanggung, usia dan kesehatan tertanggung, besar manfaat, serta biaya produk. Pada produk syariah, istilah yang setara adalah "kontribusi".'],
            ['kontribusi', 'Kontribusi (Contribution)', 'sharia',
                'Istilah asuransi syariah untuk pembayaran yang disetor peserta.',
                'Berbeda dengan premi konvensional yang dibayarkan untuk mengalihkan risiko ke perusahaan, sebagian kontribusi masuk ke **dana tabarru\'** yang dipakai untuk membantu peserta lain yang tertimpa risiko.'],
            ['uang-pertanggungan', 'Uang Pertanggungan / UP (Sum Assured)', 'general',
                'Nilai manfaat yang ditetapkan polis bila peristiwa yang dijamin terjadi.',
                'Tercantum di ringkasan polis. Pembayaran sebenarnya bisa berupa persentase dari UP, atau — untuk asuransi kesehatan — dibatasi oleh biaya yang layak diganti (eligible).'],
            ['manfaat', 'Manfaat (Benefit)', 'general',
                'Sesuatu yang dibayarkan atau disediakan polis ketika kondisi pertanggungan terpenuhi.',
                'Bentuknya bisa penggantian biaya (reimbursement), santunan sekaligus (lump sum), atau layanan seperti cashless. Manfaat selalu tunduk pada definisi, limit, dan pengecualian polis.'],
            ['pemegang-polis', 'Pemegang Polis (Policyholder)', 'general',
                'Orang atau pihak yang memiliki kontrak asuransi.',
                'Pemegang polis menjalankan hak dan kewajiban polis (mis. membayar premi, mengubah penerima manfaat). Bisa jadi orang yang sama dengan tertanggung, bisa juga berbeda.'],
            ['tertanggung', 'Tertanggung (Insured)', 'general',
                'Orang yang risikonya dijamin oleh polis.',
                'Klaim dipicu oleh peristiwa yang menimpa tertanggung. Pada sebagian polis, tertanggung dan pemegang polis adalah pihak yang berbeda (mis. orang tua menanggung anak).'],
            ['penerima-manfaat', 'Penerima Manfaat (Beneficiary)', 'general',
                'Pihak yang berhak menerima manfaat berdasarkan polis.',
                'Dicantumkan di dalam polis dan dapat diubah oleh pemegang polis. Memastikan data penerima manfaat tetap mutakhir adalah bagian dari peninjauan polis tahunan.'],
            ['klaim', 'Klaim (Claim)', 'process',
                'Permintaan manfaat yang diajukan setelah terjadi peristiwa yang dijamin.',
                'Penanggung mencocokkan peristiwa dengan definisi di dalam polis, lalu membayar bila seluruh syarat terpenuhi. Agen boleh membantu menyiapkan klaim, tetapi **tidak** memutuskan atau menjamin hasilnya.'],
            ['pengecualian', 'Pengecualian (Exclusion)', 'general',
                'Risiko atau kondisi yang secara tegas tidak dijamin polis.',
                'Tercantum dalam ketentuan polis, dan kadang ditambahkan sebagai keputusan underwriting untuk pemohon tertentu.'],
            ['masa-tunggu', 'Masa Tunggu (Waiting Period)', 'process',
                'Jangka waktu setelah polis aktif ketika kondisi tertentu belum dijamin.',
                'Umum untuk kondisi yang sudah ada sebelumnya (pre-existing), kehamilan, dan penyakit tertentu pada asuransi kesehatan.'],
            ['elimination-period', 'Elimination Period', 'process',
                'Masa tunggu spesifik pada rider tertentu sebelum klaim dapat diajukan.',
                'Contoh referensi pelatihan: sebuah rider penyakit kritis menerapkan elimination period 80 hari sejak rider berlaku. Angka pastinya mengikuti ketentuan produk.'],
            ['survival-period', 'Survival Period', 'process',
                'Jumlah hari tertanggung harus bertahan hidup setelah diagnosis agar manfaat dibayarkan.',
                'Sebagian produk penyakit kritis menetapkan 0 hari; sebagian lain, misalnya 14 hari. Selalu didefinisikan di polis.'],
            ['underwriting', 'Underwriting', 'process',
                'Proses penilaian risiko calon tertanggung sebelum perusahaan menetapkan keputusan pertanggungan.',
                'Alurnya: identifikasi risiko, klasifikasi risiko, keputusan. Hasil yang mungkin: diterima standar, premi ekstra (rating), pengecualian, manfaat dimodifikasi, permintaan informasi tambahan, penundaan, atau ditolak.'],
            ['field-underwriting', 'Field Underwriting', 'process',
                'Peran agen sebagai pengumpul awal informasi underwriting.',
                'Agen mengamati, bertanya, mencatat, dan menyampaikan fakta material secara jujur dan lengkap. Agen **tidak** memutuskan penerimaan — itu wewenang underwriter perusahaan.'],
            ['medical-underwriting', 'Medical Underwriting', 'process',
                'Bagian underwriting yang menilai risiko kesehatan pemohon.',
                'Dapat memerlukan kuesioner kesehatan, laporan dokter, atau pemeriksaan medis. Permintaan pemeriksaan bukan berarti penolakan — biasanya berarti perusahaan butuh informasi lebih.'],
            ['financial-underwriting', 'Financial Underwriting', 'process',
                'Bagian underwriting yang menilai kewajaran besar pertanggungan terhadap kebutuhan dan kemampuan finansial pemohon.',
                'Bertujuan memastikan UP sejalan dengan potensi kerugian ekonomi yang hendak dilindungi, bukan sekadar sebesar yang diinginkan.'],
            ['rider', 'Rider', 'product',
                'Pertanggungan tambahan yang dilekatkan pada polis dasar.',
                'Contoh: rider penyakit kritis, cacat tetap, kecelakaan, atau proteksi pembayar premi. Rider hanya berlaku selama polis dasar yang kompatibel aktif. Ketersediaannya bergantung pada aturan produk, usia, underwriting, dan jalur distribusi.'],
            ['polis-dasar', 'Polis Dasar (Base Policy)', 'product',
                'Kontrak asuransi utama tempat rider dilekatkan.',
                'Polis dasar berdiri sendiri; rider bergantung padanya. Menghapus atau membuat polis dasar lapse akan mengakhiri rider-nya.'],
            ['asuransi-tradisional', 'Asuransi Tradisional (Traditional Insurance)', 'product',
                'Produk proteksi tanpa komponen investasi yang dikaitkan.',
                'Fokus pada manfaat proteksi (mis. santunan meninggal, penggantian biaya medis) dengan struktur biaya yang lebih sederhana dibanding unit link.'],
            ['unit-link', 'Unit Link', 'product',
                'Produk asuransi dengan komponen investasi yang diukur dalam unit.',
                'Sebagian premi membayar proteksi dan biaya; sisanya membeli unit. Nilai polis = jumlah unit × harga unit. Lihat PAYDI.'],
            ['paydi', 'PAYDI', 'product',
                'Produk Asuransi yang Dikaitkan dengan Investasi.',
                'Menggabungkan proteksi dengan komponen investasi yang nilainya **dapat naik dan turun** serta tidak dijamin kecuali kontrak menyatakannya secara eksplisit. Sering disebut unit link.'],
            ['dana-investasi', 'Dana Investasi (Investment Fund)', 'product',
                'Kumpulan aset tempat bagian investasi dari premi PAYDI dialokasikan.',
                'Kinerjanya menentukan nilai unit. Pemegang polis biasanya dapat memilih atau mengalihkan (switching) di antara beberapa pilihan dana sesuai profil risiko.'],
            ['biaya-asuransi', 'Biaya Asuransi (Cost of Insurance)', 'product',
                'Potongan yang diambil untuk membayar unsur proteksi pada polis unit link.',
                'Naik seiring usia dan besar risiko. Bila nilai polis tidak cukup menutupinya, polis bisa memerlukan setoran tambahan atau menjadi lapse.'],
            ['cuti-premi', 'Cuti Premi (Premium Holiday)', 'product',
                'Periode saat tidak ada premi disetor ke polis unit link.',
                'Bukan proteksi gratis: biaya tetap dapat dipotong dari nilai polis sehingga memperpendek usia polis.'],
            ['asuransi-syariah', 'Asuransi Syariah (Takaful)', 'sharia',
                'Asuransi yang dibangun atas prinsip tolong-menolong dan berbagi risiko, bukan pengalihan risiko.',
                'Peserta menyetor ke dana tabarru\' yang membayar peserta yang tertimpa kerugian yang dijamin; operator mengelola dana berdasarkan akad.'],
            ['risk-sharing', 'Risk Sharing', 'sharia',
                'Risiko ditanggung bersama oleh para peserta melalui dana tabarru\'.',
                'Prinsip inti asuransi syariah. Kontras dengan risk transfer, ketika risiko dipindahkan sepenuhnya ke perusahaan.'],
            ['risk-transfer', 'Risk Transfer', 'general',
                'Pengalihan dampak finansial risiko dari individu ke perusahaan asuransi.',
                'Mekanisme dasar asuransi konvensional: peserta membayar premi, perusahaan menanggung risiko sesuai kontrak.'],
            ['tabarru', 'Tabarru\'', 'sharia',
                'Dana tolong-menolong pada asuransi syariah yang dibentuk dari kontribusi peserta.',
                'Klaim dibayar dari dana tabarru\'. Surplus atau defisit underwriting suatu periode dikelola sesuai ketentuan produk dan kaidah syariah.'],
            ['taawun', 'Ta\'awun', 'sharia',
                'Prinsip saling menolong yang mendasari asuransi syariah.',
                'Peserta saling membantu menanggung risiko, alih-alih mengalihkannya sepenuhnya ke perusahaan.'],
            ['ujrah', 'Ujrah', 'sharia',
                'Imbalan (fee) yang diambil operator atas pengelolaan dana asuransi syariah.',
                'Diungkapkan sejak awal di dalam akad. Bagian dari model wakalah bil ujrah.'],
            ['wakalah-bil-ujrah', 'Wakalah bil Ujrah', 'sharia',
                'Akad perwakilan dengan imbalan untuk mengelola dana asuransi syariah.',
                'Operator bertindak sebagai wakil peserta dan menerima ujrah yang ditentukan atas pengelolaan tersebut.'],
            ['surplus-underwriting', 'Surplus Underwriting', 'sharia',
                'Kelebihan pendapatan dana tabarru\' atas pengeluarannya dalam satu periode.',
                'Perlakuannya (ditahan, dibagikan, atau dibawa ke periode berikutnya) ditetapkan oleh produk dan kaidah syariah. Kebalikannya adalah defisit underwriting.'],
            ['deductible', 'Deductible', 'general',
                'Bagian biaya yang layak diganti yang Anda tanggung lebih dulu sebelum penanggung membayar.',
                'Disebut juga "risiko sendiri". Deductible yang lebih tinggi biasanya menurunkan premi. Umum pada asuransi biaya kesehatan.'],
            ['co-share', 'Co-share', 'general',
                'Persentase dari biaya yang layak diganti yang tetap Anda tanggung bersama penanggung.',
                'Bila mekanisme ini berlaku, penanggung membayar persentase tertentu dari biaya eligible dan Anda menanggung sisanya, sesuai ketentuan polis.'],
            ['reimbursement', 'Reimbursement (Indemnity)', 'general',
                'Membayar klaim berdasarkan biaya nyata yang layak diganti, sampai batas tertentu.',
                'Disebut juga indemnitas. Kontras dengan santunan sekaligus (lump sum) yang membayar jumlah tetap tanpa melihat tagihan.'],
            ['lump-sum', 'Lump Sum', 'general',
                'Manfaat berupa jumlah tetap yang dibayarkan atas suatu pemicu, tanpa melihat biaya nyata.',
                'Dipakai oleh manfaat penyakit kritis, cacat tetap total, term life, dan kecelakaan. Besarnya tidak harus sama dengan tagihan mana pun.'],
            ['manfaat-akselerasi', 'Manfaat Akselerasi (Accelerated Benefit)', 'product',
                'Manfaat yang dibayar lebih awal dari manfaat polis dasar sehingga mengurangi UP dasar.',
                'Contoh: rider penyakit kritis "accelerated" membayar sebagian UP jiwa lebih awal, sehingga sisa UP jiwa berkurang. Lawannya: manfaat non-akselerasi.'],
            ['manfaat-non-akselerasi', 'Manfaat Non-Akselerasi (Non-Accelerated Benefit)', 'product',
                'Manfaat tambahan yang dibayar tanpa mengurangi UP polis dasar.',
                'Contoh konsep pelatihan: rider "CI Plus" membayar santunan penyakit kritis tanpa memotong UP jiwa dasar.'],
            ['cashless', 'Cashless', 'general',
                'Fasilitas ketika penanggung menyelesaikan biaya rumah sakit yang layak diganti secara langsung.',
                'Cashless **tidak** otomatis berarti nol biaya pribadi: kelebihan di atas limit, deductible, dan item non-eligible tetap bisa menjadi tanggungan pasien.'],
            ['persistency', 'Persistency', 'agency',
                'Ukuran seberapa baik polis tetap aktif dalam suatu periode.',
                'Materi pelatihan kerap menyebut persistency 13 bulan minimum 80% untuk aturan promosi atau maintaining tertentu. Aturan pastinya bersifat spesifik-rencana dan spesifik-periode.'],
            ['nett-alp', 'Nett ALP', 'agency',
                'Nett Annualized Life Premium — metrik produksi, bukan penghasilan agen.',
                'Dipakai dalam kualifikasi dan perhitungan tertentu. Cara hitung pastinya mengikuti definisi produk dan compensation plan; bukan sekadar premi × 12.'],
            ['be', 'Business Executive (BE)', 'agency',
                'Posisi pemasar tingkat awal dalam struktur pelatihan Allianz Star Network.',
                'Naik menjadi Business Partner (BP) setelah memenuhi aturan promosi dan kualifikasi.'],
            ['bp', 'Business Partner (BP)', 'agency',
                'Posisi pemasar tingkat kepemimpinan setelah aturan promosi BE terpenuhi.',
                'Seorang BP menyupervisi unit berisi BE yang melapor langsung, dan dapat berada dalam generasi keagenan yang lebih dalam.'],
            ['upline-downline', 'Upline dan Downline', 'agency',
                'Istilah posisi dalam hierarki keagenan: di atas (upline) dan di bawah (downline).',
                'Pangkat (BE/BP) berbeda dari relasi. Seorang BP bisa menjadi downline BP di atasnya sekaligus upline bagi yang di bawahnya.'],
            ['unit-agency-group-agency', 'Unit, Agency, Group Agency', 'agency',
                'Tingkatan struktur organisasi keagenan dalam materi pelatihan.',
                '"Unit" = seorang BP beserta BE langsungnya; "Agency" = unit yang telah tumbuh dengan generasi BP di bawahnya; "Group Agency" = struktur lebih luas lintas beberapa generasi dan unit.'],
            ['generasi', 'Generasi (Generation)', 'agency',
                'Kedalaman seorang Business Partner dalam organisasi keagenan yang berkembang.',
                'Materi pelatihan menggambarkan generasi G1 hingga G8, masing-masing dengan ambang kualifikasi yang terikat pada periode tertentu.'],
            ['overriding', 'Overriding', 'agency',
                'Kompensasi yang terkait dengan produksi unit atau generasi di bawah seorang pemasar.',
                'Terbagi menjadi overriding direct dan overriding generation, masing-masing tunduk pada aturan kualifikasi dan maintaining. Terkait produksi asuransi yang eligible — bukan sekadar imbalan merekrut orang.'],
            ['fyc-syc', 'FYC dan SYC', 'agency',
                'First Year Commission dan Second Year Commission — dasar perhitungan overriding.',
                'Nilai seperti "55% × FYC" berarti 55% dari First Year Commission yang eligible, **bukan** 55% dari premi nasabah.'],
            ['mis-selling', 'Mis-selling', 'compliance',
                'Menjual produk yang tidak sesuai kebutuhan atau kemampuan bayar nasabah.',
                'Tetap merupakan pelanggaran konduk meski nasabah setuju saat itu. Berbeda dari misrepresentation yang menyangkut informasi keliru atau menyesatkan.'],
            ['misrepresentation', 'Misrepresentation', 'compliance',
                'Memberikan gambaran, janji, atau informasi yang tidak sesuai kenyataan atau dokumen produk.',
                'Misalnya membesar-besarkan manfaat atau menutupi risiko dan pengecualian demi menutup penjualan.'],
            ['churning', 'Churning', 'compliance',
                'Mendorong penggantian polis di perusahaan yang sama tanpa analisis memadai.',
                'Sering didorong komisi baru alih-alih kepentingan nasabah. "Twisting" adalah padanannya untuk mengganti polis perusahaan lain.'],
            ['twisting', 'Twisting', 'compliance',
                'Mendorong penggantian polis perusahaan lain secara tidak patut.',
                'Seperti churning, ini pelanggaran konduk bila tidak jelas menguntungkan nasabah.'],
            ['moral-hazard', 'Moral Hazard', 'process',
                'Risiko bahwa kepemilikan asuransi mengubah perilaku sehingga memperbesar peluang klaim.',
                'Dipertimbangkan saat underwriting bersama faktor medis, gaya hidup, pekerjaan, dan keuangan.'],
            ['kyc', 'KYC', 'compliance',
                'Know Your Customer — memverifikasi identitas, pekerjaan, sumber dana, dan tujuan transaksi.',
                'Bagian dari uji tuntas nasabah. Menopang kewajiban anti pencucian uang dan pencegahan pendanaan terorisme.'],
            ['cdd-edd', 'CDD dan EDD', 'compliance',
                'Customer Due Diligence dan Enhanced Due Diligence.',
                'CDD: identifikasi, verifikasi, dan pemantauan terhadap profil nasabah. EDD: uji tuntas lebih mendalam untuk nasabah berisiko lebih tinggi.'],
            ['beneficial-owner', 'Beneficial Owner', 'compliance',
                'Pihak yang sesungguhnya memiliki atau mengendalikan manfaat atau struktur.',
                'Wajib diidentifikasi agar transaksi tidak menyembunyikan pengendali sebenarnya.'],
            ['pep', 'PEP', 'compliance',
                'Politically Exposed Person — orang dengan fungsi publik yang menonjol.',
                'Klasifikasi risiko yang dapat memerlukan EDD. Status PEP sendiri tidak menyiratkan adanya perbuatan salah.'],
            ['fatca', 'FATCA', 'compliance',
                'Foreign Account Tax Compliance Act — ketentuan pelaporan pajak lintas yurisdiksi (AS).',
                'Menyangkut residensi pajak dan nomor identifikasi pajak. Agen mengumpulkan data sesuai formulir dan tidak boleh mengarahkan nasabah menyembunyikan residensi pajak.'],
            ['crs', 'CRS', 'compliance',
                'Common Reporting Standard — standar pelaporan informasi keuangan antarnegara.',
                'Serupa tujuannya dengan FATCA namun multilateral. Data residensi pajak dikumpulkan apa adanya, bukan berdasarkan asumsi.'],
            ['riplay', 'RIPLAY', 'process',
                'Ringkasan Informasi Produk dan Layanan yang diberikan kepada calon nasabah.',
                'Dipakai bersama ketentuan polis sebagai penjelasan resmi atas manfaat, biaya, dan risiko produk.'],
            ['spaj', 'SPAJ', 'process',
                'Surat Permintaan Asuransi Jiwa — formulir pengajuan asuransi jiwa.',
                'Datanya harus lengkap, akurat, dan konsisten dengan proposal serta identitas pemohon agar tidak terjadi pending.'],
            ['inforce', 'Inforce', 'process',
                'Keadaan ketika polis aktif dan pertanggungan berlaku.',
                'Tercapai hanya bila syarat penerbitan, underwriting, pembayaran, dan tanggal efektif terpenuhi. Membayar saja tidak otomatis membuat polis inforce.'],
            ['welcome-call', 'Welcome Call', 'process',
                'Panggilan penjaminan mutu untuk memastikan nasabah memahami polis yang dibeli.',
                'Dapat mengonfirmasi kesadaran nasabah atas premi, karakteristik produk, proses tanda tangan, dan penjelasan yang diterima.'],
            ['human-life-value', 'Human Life Value (HLV)', 'life',
                'Perkiraan sederhana atas nilai ekonomi seseorang bagi keluarganya.',
                'Rumus pelatihan yang umum: penghasilan tahunan × sisa tahun produktif. Hanya titik awal dan **tidak** memberi harga pada sebuah nyawa.'],
            ['indemnity', 'Indemnity (Indemnitas)', 'general',
                'Prinsip mengembalikan Anda ke posisi keuangan sebelum kerugian, tidak lebih.',
                'Mendasari pertanggungan bergaya penggantian biaya seperti asuransi biaya kesehatan dan asuransi umum.'],
            ['penyakit-kritis', 'Penyakit Kritis (Critical Illness)', 'product',
                'Kondisi medis serius yang didefinisikan polis dan memicu santunan sekaligus (lump sum).',
                'Contoh yang umum: serangan jantung, stroke, dan kanker pada tingkat keparahan tertentu. Setiap kondisi punya definisi ketat dan bisa mensyaratkan ambang keparahan serta survival period.'],
            ['tpd', 'Total Permanent Disability (TPD)', 'product',
                'Cacat tetap yang, sesuai definisi polis, mengakhiri kemampuan seseorang mencari nafkah.',
                'Definisi bervariasi ("own occupation", "any occupation", atau daftar kehilangan tertentu). Berbeda dari cacat sementara.'],
            ['addb', 'Accidental Death & Disablement Benefit (ADDB)', 'product',
                'Pertanggungan untuk meninggal karena kecelakaan dan cacat akibat kecelakaan.',
                'Cacat sebagian permanen biasanya dibayar menurut tabel persentase per kondisi. Hanya merespons peristiwa yang memenuhi definisi "kecelakaan" pada polis.'],
        ];

        foreach ($terms as [$slug, $term, $category, $short, $definition]) {
            GlossaryTerm::updateOrCreate(
                ['slug' => $slug],
                [
                    'term' => $term,
                    'short_definition' => $short,
                    'definition' => $definition,
                    'category' => $category,
                    'last_reviewed_at' => now()->subDays(20)->toDateString(),
                    'reference_status' => ReferenceStatus::Current,
                ],
            );
        }

        $this->linkRelatedTerms();
    }

    private function linkRelatedTerms(): void
    {
        $link = function (string $slug, array $relatedSlugs): void {
            $termA = GlossaryTerm::where('slug', $slug)->first();
            if (! $termA) {
                return;
            }
            $ids = GlossaryTerm::whereIn('slug', $relatedSlugs)->pluck('id')->all();
            $termA->relatedTerms()->syncWithoutDetaching($ids);
        };

        $link('reimbursement', ['lump-sum', 'deductible', 'co-share', 'cashless', 'indemnity']);
        $link('paydi', ['unit-link', 'biaya-asuransi', 'cuti-premi', 'dana-investasi']);
        $link('asuransi-syariah', ['tabarru', 'taawun', 'ujrah', 'wakalah-bil-ujrah', 'risk-sharing']);
        $link('be', ['bp', 'upline-downline', 'nett-alp', 'persistency']);
        $link('penyakit-kritis', ['lump-sum', 'survival-period', 'elimination-period', 'manfaat-akselerasi']);
        $link('underwriting', ['field-underwriting', 'medical-underwriting', 'financial-underwriting', 'moral-hazard']);
        $link('rider', ['polis-dasar', 'manfaat-akselerasi', 'manfaat-non-akselerasi']);
        $link('risk-sharing', ['risk-transfer', 'tabarru', 'taawun']);
        $link('klaim', ['pengecualian', 'masa-tunggu', 'reimbursement']);
        $link('uang-pertanggungan', ['manfaat', 'human-life-value', 'lump-sum']);
        $link('overriding', ['fyc-syc', 'nett-alp', 'persistency', 'generasi']);
        $link('kyc', ['cdd-edd', 'beneficial-owner', 'pep']);
        $link('tpd', ['lump-sum', 'manfaat-akselerasi', 'addb']);
    }
}
