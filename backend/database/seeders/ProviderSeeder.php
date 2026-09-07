<?php

namespace Database\Seeders;

use App\Enums\ProviderStatus;
use App\Models\Provider;
use App\Models\Source;
use Illuminate\Database\Seeder;

/**
 * Allianz exists ONLY as a provider row (ADR-0002). The three Indonesian legal
 * entities and the ASN distribution channel are described in the record's fields
 * and metadata — not as separate tables or a hardcoded platform identity.
 */
class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $source = Source::updateOrCreate(
            ['title' => 'Allianz Basic Training – Dokumentasi Terstruktur'],
            [
                'publisher' => 'Allianz (agency Basic Training material, internal education compilation)',
                'url' => null,
                'published_at' => '2026-08-01',
                'accessed_at' => now()->toDateString(),
                'reference_period' => 'Compilation dated August 2026',
                'notes' => implode("\n", [
                    'Not a substitute for official documents. Real transactions must use the current policy, RIPLAY, illustration, underwriting guide, SOP, agency terms, compensation plan, and prevailing regulation.',
                    'Known internal inconsistencies that must remain visible (NEEDS VERIFICATION):',
                    '1) BE→BP promotion "Option 3": the text states 2 direct BE (combined Nett ALP Rp100m, min Rp50m each) while the diagram appears to show 5 direct BEs (~50/50/30/35/35 million). Unresolved — verify against the official compensation guide.',
                    '2) Overriding Direct: a detailed persistency table (Upper/Standard/Lower = 60/55/50% × FYC) and a summary slide differ on Year 2 treatment; do not merge. Verify.',
                    '3) Overriding Generation qualification (G1–G8) is explicitly tied to a historical validation period (BP validation end-2021, new business blocks >2022) and the "5 BP" figure appears merged across G5–G8. Treat as historical + needs verification.',
                ]),
            ],
        );

        $provider = Provider::updateOrCreate(
            ['slug' => 'allianz'],
            [
                'name' => 'Allianz',
                'description' => implode("\n\n", [
                    'Allianz adalah kelompok jasa keuangan global yang bergerak di perlindungan risiko, asuransi, dan layanan keuangan. Di InsuraLab, Allianz dipakai sebagai **studi kasus provider pertama** — bukan identitas platform.',
                    'Di Indonesia, tiga entitas hukum yang berbeda tidak boleh disamakan, karena entitas yang tercantum pada polis adalah pihak yang menanggung risiko kontraktual:',
                    '- **Allianz Life Indonesia** — solusi jiwa, kesehatan, dan perlindungan finansial individu/keluarga.',
                    '- **Allianz Utama Indonesia** — asuransi umum (kendaraan, properti, bisnis, perjalanan, dan risiko non-jiwa lain).',
                    '- **Allianz Life Syariah Indonesia** — perlindungan jiwa dan pengelolaan risiko finansial berbasis prinsip syariah.',
                    '**Allianz Star Network (ASN)** adalah kanal keagenan dan pengembangan bisnis Allianz Life. Distribusi terpisah dari penerbit polis: ASN adalah kanal; polis diterbitkan dan dikelola oleh entitas perusahaan asuransi yang namanya tertera di kontrak.',
                    'Detail korporat terkini (izin, kepemilikan, struktur) belum diverifikasi secara independen dalam proyek ini — perlakukan uraian entitas di atas sebagai gambaran edukatif, bukan pernyataan resmi.',
                ]),
                'country_of_origin' => 'Jerman',
                'presence_in_indonesia' => 'Beroperasi melalui Allianz Life Indonesia, Allianz Utama Indonesia, dan Allianz Life Syariah Indonesia; distribusi keagenan melalui Allianz Star Network (ASN).',
                'website_url' => 'https://www.allianz.co.id',
                'status' => ProviderStatus::Active,
                'last_reviewed_at' => now()->subDays(14)->toDateString(),
                'metadata' => [
                    'entities' => [
                        ['name' => 'Allianz Life Indonesia', 'focus' => 'Jiwa, kesehatan, perlindungan finansial individu/keluarga'],
                        ['name' => 'Allianz Utama Indonesia', 'focus' => 'Asuransi umum / non-jiwa (kendaraan, properti, bisnis, perjalanan)'],
                        ['name' => 'Allianz Life Syariah Indonesia', 'focus' => 'Perlindungan jiwa berbasis prinsip syariah'],
                    ],
                    'business_areas' => ['Jiwa', 'Kesehatan', 'Penyakit kritis', 'Disabilitas', 'Kecelakaan', 'Unit link / PAYDI', 'Syariah', 'Asuransi umum'],
                    'distribution' => 'Allianz Star Network (ASN) — kanal keagenan, bukan penerbit polis.',
                    'case_study_note' => 'Studi kasus provider pertama. Bukan identitas platform.',
                ],
            ],
        );

        $provider->sources()->syncWithoutDetaching([
            $source->id => ['note' => 'Primary training source for all Allianz-specific content.'],
        ]);
    }
}
