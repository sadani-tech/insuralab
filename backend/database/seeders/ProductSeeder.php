<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\InsuranceScheme;
use App\Enums\ProductStructure;
use App\Enums\ReferenceStatus;
use App\Models\Article;
use App\Models\GlossaryTerm;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProtectionCategory;
use App\Models\Provider;
use App\Models\Source;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Educational product records for Allianz training examples. Every record:
 *  - belongs to the Allianz provider row and a product category,
 *  - links to protection concepts, general learning articles, and glossary terms
 *    (concept → provider → product hierarchy),
 *  - carries a reference_status per docs/DOMAIN_KNOWLEDGE.md, a reference_period,
 *    and the Basic Training source,
 *  - does NOT fabricate current product facts. Numeric detail lives in the
 *    linked article, flagged needs_review.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $allianz = Provider::where('slug', 'allianz')->firstOrFail();
        $source = Source::where('title', 'Allianz Basic Training – Dokumentasi Terstruktur')->firstOrFail();

        $cat = fn (string $slug) => ProductCategory::where('slug', $slug)->firstOrFail()->id;
        $prot = fn (string $key) => ProtectionCategory::where('key', $key)->firstOrFail()->id;

        $period = 'Allianz Basic Training – kompilasi Agustus 2026';

        $products = [
            [
                'name' => 'Allianz Flexi Medical Plan (AFMP)',
                'category' => 'medical-hospitalisation',
                'structure' => ProductStructure::Traditional,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Proteksi biaya kesehatan yang dibayar dengan basis penggantian (indemnitas/reimbursement), tunduk pada biaya yang layak diganti, limit, deductible, dan co-share.',
                'desc' => "## Apa produk ini?\n\nRider proteksi biaya kesehatan yang, menurut materi pelatihan, membayar dengan basis **penggantian (reimbursement)** atas biaya rawat yang layak diganti.\n\n## Risiko apa yang ditangani?\n\nBiaya medis dan rawat inap yang bisa menggerus tabungan.\n\n## Bagaimana manfaat bekerja?\n\nMembayar biaya eligible sampai limit plan, setelah **deductible** dan/atau **co-share**. Fasilitas **cashless** menyelesaikan biaya eligible secara langsung — tetapi tidak berarti nol biaya pribadi: kelebihan di atas limit, deductible, dan item non-eligible tetap bisa menjadi tanggungan Anda.\n\n## Batasan penting\n\nLimit, hak kamar, daftar pengecualian, dan masa tunggu ditetapkan di ketentuan produk terkini. Jangan menganggap angka pelatihan sebagai ketentuan yang berlaku sekarang.",
                'protections' => ['health' => true],
                'meta' => ['payout_model' => 'reimbursement', 'note' => 'Cashless does not necessarily mean zero out-of-pocket.'],
                'concepts' => ['asuransi-kesehatan', 'reimbursement-vs-lump-sum', 'rider-manfaat-tambahan'],
                'terms' => ['reimbursement', 'deductible', 'co-share', 'cashless', 'indemnity'],
            ],
            [
                'name' => 'Allianz Flexi CI',
                'category' => 'critical-illness-rider',
                'structure' => ProductStructure::Other,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Rider penyakit kritis yang membayar santunan sekaligus pada diagnosis sesuai plan (Silver / Gold / Platinum). Semua angka manfaat, jumlah kondisi, usia, dan struktur COR adalah referensi pelatihan yang perlu diverifikasi terhadap RIPLAY terkini.',
                'desc' => "## Apa produk ini?\n\nRider penyakit kritis dengan tiga tingkat (Silver, Gold, Platinum) yang, menurut materi pelatihan, membayar **santunan sekaligus (lump sum)** pada diagnosis kondisi yang dijamin.\n\n## Risiko apa yang ditangani?\n\nBeban finansial akibat diagnosis penyakit kritis: biaya pengobatan, waktu tidak bekerja, dan kebutuhan pemulihan.\n\n## Bagaimana manfaat bekerja?\n\nManfaat berupa persentase Uang Pertanggungan sesuai tahap kondisi (mis. Advanced CI, Early CI). Materi pelatihan menyebut total manfaat **sampai ~150% UP** lintas riwayat klaim — ini **tidak** berarti satu diagnosis membayar 150%.\n\n## Batasan penting\n\nJumlah kondisi (88/128/168), elimination period, survival period, usia masuk, batas manfaat, dan struktur Cost of Rider adalah **referensi pelatihan** (status: perlu verifikasi). Rincian lengkap dan pelabelannya ada di artikel tertaut.",
                'protections' => ['critical_illness' => true],
                'meta' => ['tiers' => ['Silver' => 88, 'Gold' => 128, 'Platinum' => 168], 'payout_model' => 'lump_sum'],
                'concepts' => ['penyakit-kritis', 'reimbursement-vs-lump-sum', 'rider-manfaat-tambahan', 'manfaat-dan-uang-pertanggungan'],
                'terms' => ['penyakit-kritis', 'lump-sum', 'elimination-period', 'survival-period', 'manfaat-akselerasi', 'uang-pertanggungan'],
            ],
            [
                'name' => 'CI Plus',
                'category' => 'critical-illness-rider',
                'structure' => ProductStructure::Other,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Rider penyakit kritis yang, menurut konsep pelatihan yang dibahas, tidak mengurangi Uang Pertanggungan jiwa dasar.',
                'desc' => "## Apa produk ini?\n\nRider penyakit kritis. Menurut konsep pelatihan yang dibahas, manfaatnya bersifat **non-akselerasi** — dibayar **tanpa mengurangi** Uang Pertanggungan jiwa polis dasar.\n\n## Bagaimana manfaat bekerja?\n\nSantunan sekaligus pada diagnosis kondisi yang dijamin, terpisah dari manfaat jiwa dasar.\n\n## Batasan penting\n\nDefinisi kondisi, daftar penyakit, dan ketentuan lain mengikuti RIPLAY dan polis terkini. Perlakukan struktur ini sebagai konsep pelatihan yang perlu diverifikasi.",
                'protections' => ['critical_illness' => true],
                'meta' => ['reduces_base_sum_assured' => false],
                'concepts' => ['penyakit-kritis', 'rider-manfaat-tambahan'],
                'terms' => ['penyakit-kritis', 'manfaat-non-akselerasi', 'lump-sum'],
            ],
            [
                'name' => 'CI Accelerated',
                'category' => 'critical-illness-rider',
                'structure' => ProductStructure::Other,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Rider penyakit kritis yang mengurangi Uang Pertanggungan jiwa dasar, karena membayar sebagian manfaat dasar lebih awal.',
                'desc' => "## Apa produk ini?\n\nRider penyakit kritis dengan manfaat **akselerasi**: membayar sebagian Uang Pertanggungan jiwa **lebih awal** pada diagnosis, sehingga **sisa UP jiwa berkurang**.\n\n## Bagaimana manfaat bekerja?\n\nContoh konsep: bila UP jiwa Rp1 miliar dan manfaat CI accelerated membayar 50%, sisa UP jiwa menjadi Rp500 juta.\n\n## Batasan penting\n\nBesar persentase, kondisi yang dijamin, dan interaksi dengan manfaat jiwa mengikuti ketentuan produk terkini.",
                'protections' => ['critical_illness' => true, 'life' => false],
                'meta' => ['reduces_base_sum_assured' => true],
                'concepts' => ['penyakit-kritis', 'rider-manfaat-tambahan', 'asuransi-jiwa'],
                'terms' => ['penyakit-kritis', 'manfaat-akselerasi', 'uang-pertanggungan'],
            ],
            [
                'name' => 'TPD (Total Permanent Disability) Rider',
                'category' => 'disability-rider',
                'structure' => ProductStructure::Other,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Santunan sekaligus atas cacat tetap total sesuai definisi polis, karena penyakit atau kecelakaan.',
                'desc' => "## Apa produk ini?\n\nRider yang membayar **santunan sekaligus** ketika tertanggung mengalami cacat tetap total sesuai definisi polis.\n\n## Risiko apa yang ditangani?\n\nHilangnya kemampuan mencari nafkah secara permanen; kebutuhan dana untuk biaya hidup dan penyesuaian.\n\n## Bagaimana manfaat bekerja?\n\nDibayar atas pemicu (cacat tetap total per definisi). Bisa berstruktur standalone atau accelerated terhadap UP jiwa — tergantung produk.\n\n## Batasan penting\n\nDefinisi \"cacat tetap total\" ketat dan berbeda antarproduk (\"own occupation\", \"any occupation\", daftar kehilangan). Bukan untuk cacat sementara.",
                'protections' => ['disability' => true],
                'meta' => ['payout_model' => 'lump_sum'],
                'concepts' => ['total-permanent-disability', 'rider-manfaat-tambahan'],
                'terms' => ['tpd', 'lump-sum', 'manfaat-akselerasi'],
            ],
            [
                'name' => 'ADDB (Accidental Death & Disablement Benefit)',
                'category' => 'accident-rider',
                'structure' => ProductStructure::Other,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Menjamin meninggal karena kecelakaan, cacat tetap total karena kecelakaan, dan cacat sebagian permanen menurut tabel manfaat.',
                'desc' => "## Apa produk ini?\n\nRider yang menjamin **meninggal karena kecelakaan**, cacat tetap total karena kecelakaan, dan cacat sebagian permanen.\n\n## Bagaimana manfaat bekerja?\n\nMeninggal karena kecelakaan membayar jumlah penuh; cacat sebagian permanen dibayar menurut **tabel persentase** per kondisi.\n\n## Batasan penting\n\nHanya merespons peristiwa yang memenuhi definisi \"kecelakaan\" pada polis. Tidak semua musibah otomatis memenuhi syarat; penyebab yang berkaitan dengan penyakit ditangani pertanggungan lain.",
                'protections' => ['accident' => true],
                'meta' => ['payout_model' => 'schedule'],
                'concepts' => ['perlindungan-kecelakaan', 'rider-manfaat-tambahan'],
                'terms' => ['addb', 'lump-sum', 'pengecualian'],
            ],
            [
                'name' => 'Term Life Rider',
                'category' => 'term-life-rider',
                'structure' => ProductStructure::Other,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Proteksi meninggal tambahan untuk jangka waktu tertentu; dipakai untuk penggantian penghasilan, Human Life Value, dan kewajiban sementara.',
                'desc' => "## Apa produk ini?\n\nRider yang menambah **proteksi meninggal untuk periode tertentu** di atas polis dasar.\n\n## Risiko apa yang ditangani?\n\nKerugian ekonomi bagi tanggungan bila pencari nafkah meninggal dalam periode tersebut.\n\n## Bagaimana manfaat bekerja?\n\nSantunan sekaligus jika tertanggung meninggal dalam masa pertanggungan rider. Berguna untuk menutup celah Human Life Value, cicilan, atau biaya pendidikan yang bersifat sementara.\n\n## Batasan penting\n\nProteksi berhenti di akhir periode rider kecuali diperpanjang sesuai ketentuan.",
                'protections' => ['life' => true],
                'meta' => ['payout_model' => 'lump_sum'],
                'concepts' => ['asuransi-jiwa', 'human-life-value', 'rider-manfaat-tambahan'],
                'terms' => ['uang-pertanggungan', 'human-life-value', 'lump-sum'],
            ],
            [
                'name' => 'Payor Protection Rider',
                'category' => 'payor-rider',
                'structure' => ProductStructure::Other,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Melindungi pembayaran premi sesuai ketentuan rider bila pembayar premi meninggal. Varian pasangan dan CI/TPD (Spouse Payor Protection, Payor Benefit, Spouse Payor Benefit) memiliki tujuan yang sama.',
                'desc' => "## Apa produk ini?\n\nKeluarga rider yang **melindungi kelanjutan pembayaran premi** sesuai ketentuan rider ketika pihak pembayar mengalami risiko.\n\n## Varian menurut materi pelatihan\n\n- **Payor Protection** — pembayar meninggal.\n- **Spouse Payor Protection** — pasangan pembayar meninggal.\n- **Payor Benefit** — pembayar mengalami CI / TPD yang memenuhi syarat.\n- **Spouse Payor Benefit** — pasangan pembayar mengalami CI / TPD yang memenuhi syarat.\n\n## Batasan penting\n\nPemicu, definisi, dan durasi perlindungan mengikuti ketentuan rider. Rider ini **tidak menjamin** pembayaran di luar syarat yang ditetapkan.",
                'protections' => ['income' => true, 'life' => false],
                'meta' => ['variants' => ['Payor Protection', 'Spouse Payor Protection', 'Payor Benefit', 'Spouse Payor Benefit']],
                'concepts' => ['rider-manfaat-tambahan', 'premi-dan-kontribusi', 'penyakit-kritis'],
                'terms' => ['rider', 'premi', 'tpd'],
            ],
            [
                'name' => 'SmartLink Protection Life',
                'category' => 'life-unit-link',
                'structure' => ProductStructure::Paydi,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Polis dasar unit link (PAYDI) yang menggabungkan proteksi dengan komponen investasi. Nilai investasi tidak dijamin dan dapat turun.',
                'desc' => "## Apa produk ini?\n\nPolis dasar **unit link (PAYDI)** yang menggabungkan proteksi jiwa dengan komponen investasi.\n\n## Bagaimana manfaat bekerja?\n\nSebagian premi membayar proteksi dan biaya; sisanya membeli unit investasi. Manfaat menarik dari Uang Pertanggungan dan, bila relevan, nilai unit.\n\n## Batasan penting (PAYDI)\n\n- Nilai investasi **tidak dijamin** kecuali kontrak menyatakannya, dan **dapat turun**.\n- Biaya asuransi, penarikan, switching, dan cuti premi dapat mengurangi nilai polis dan keberlanjutannya.\n- Cuti premi **bukan** proteksi gratis. Proyeksi ilustrasi bukan janji hasil.",
                'protections' => ['life' => true, 'income' => false],
                'meta' => ['is_paydi' => true],
                'concepts' => ['paydi-unit-link', 'asuransi-jiwa', 'premi-dan-kontribusi'],
                'terms' => ['paydi', 'unit-link', 'biaya-asuransi', 'cuti-premi', 'dana-investasi'],
            ],
            [
                'name' => 'AlliSya Protection Life',
                'category' => 'sharia-life',
                'structure' => ProductStructure::Paydi,
                'scheme' => InsuranceScheme::Sharia,
                'reference_status' => ReferenceStatus::NeedsReview,
                'short' => 'Produk kontribusi unit link (PAYDI) syariah berbasis prinsip berbagi risiko (dana tabarru\'), dengan komponen investasi yang tidak dijamin.',
                'desc' => "## Apa produk ini?\n\nProduk **unit link (PAYDI) syariah**. Peserta menyetor **kontribusi**; sebagiannya masuk ke dana **tabarru'** untuk saling menolong antarpeserta, dan operator mengelola dana atas dasar akad (imbalan/ujrah).\n\n## Bagaimana manfaat bekerja?\n\nKombinasi proteksi jiwa dan komponen investasi, dengan mekanisme berbagi risiko, bukan pengalihan risiko.\n\n## Batasan penting\n\nSemua peringatan PAYDI berlaku: nilai investasi tidak dijamin dan dapat turun; biaya dan penarikan mengurangi nilai polis. Gunakan istilah **kontribusi**, bukan premi, sesuai struktur produk.",
                'protections' => ['life' => true],
                'meta' => ['is_paydi' => true, 'scheme' => 'sharia'],
                'concepts' => ['asuransi-syariah', 'risk-sharing-vs-risk-transfer', 'paydi-unit-link'],
                'terms' => ['asuransi-syariah', 'kontribusi', 'tabarru', 'ujrah', 'paydi'],
            ],
            [
                'name' => 'Allianz PAYDI base-commission schedule (historical training reference)',
                'category' => 'life-unit-link',
                'structure' => ProductStructure::Paydi,
                'scheme' => InsuranceScheme::Conventional,
                'reference_status' => ReferenceStatus::Historical,
                'short' => 'Referensi training keagenan HISTORIS: komisi dasar SmartLink / AlliSya Protection Life Y1 23,25%, Y2 23,25%, Y3–Y5 5%, top-up 3%. Disimpan hanya untuk sejarah edukatif — bukan panduan kompensasi terkini.',
                'desc' => "## Referensi historis\n\nCatatan ini adalah **referensi training keagenan historis** untuk komisi dasar PAYDI (SmartLink / AlliSya Protection Life). Disimpan untuk tujuan pembelajaran dan menggambarkan materi pada periode tertentu — **bukan** ketentuan yang berlaku saat ini.\n\n## Konteks yang wajib menyertai\n\nPersentase berlaku atas **dasar komisi yang didefinisikan produk**, **bukan** atas Uang Pertanggungan. Aturan kompensasi diversi berdasarkan produk, skema, dan tanggal berlaku. Rincian dan pelabelannya ada di artikel tertaut.",
                'protections' => ['income' => true],
                'meta' => ['historical' => true, 'kind' => 'compensation_reference'],
                'concepts' => ['paydi-unit-link', 'distribusi-asuransi'],
                'terms' => ['paydi', 'fyc-syc', 'overriding'],
            ],
        ];

        foreach ($products as $row) {
            $product = Product::updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                [
                    'provider_id' => $allianz->id,
                    'product_category_id' => $cat($row['category']),
                    'name' => $row['name'],
                    'short_description' => $row['short'],
                    'description' => $row['desc']."\n\n## Sumber & referensi\n\nSemua figur bersifat referensi pelatihan provider dan harus diperiksa terhadap dokumentasi resmi terkini (polis, RIPLAY, ilustrasi) sebelum penggunaan aktual. Lihat status referensi dan sumber di bawah.",
                    'insurance_scheme' => $row['scheme'],
                    'product_structure' => $row['structure'],
                    'reference_status' => $row['reference_status'],
                    'reference_period' => $period,
                    'last_reviewed_at' => now()->subDays(14)->toDateString(),
                    'status' => ContentStatus::Published,
                    'metadata' => $row['meta'],
                ],
            );

            $sync = [];
            foreach ($row['protections'] as $key => $isPrimary) {
                $sync[$prot($key)] = ['is_primary' => $isPrimary];
            }
            $product->protectionCategories()->sync($sync);

            $product->sources()->syncWithoutDetaching([
                $source->id => ['note' => 'Referensi pelatihan; verifikasi terhadap RIPLAY / polis terkini.'],
            ]);

            $product->relatedArticles()->sync(
                Article::whereIn('slug', $row['concepts'])->pluck('id')->all(),
            );
            $product->glossaryTerms()->sync(
                GlossaryTerm::whereIn('slug', $row['terms'])->pluck('id')->all(),
            );
        }
    }
}
