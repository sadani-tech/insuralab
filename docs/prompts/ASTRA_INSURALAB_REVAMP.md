# Prompt Astra: Revamp Total InsuraLab dan Fondasi Workspace Agen

Salin seluruh isi di bawah garis ini sebagai instruksi tugas untuk Astra pada
workspace yang memiliki akses repository dan referensi lokal.

---

Kerjakan revamp total InsuraLab sebagai product designer dan engineer full-stack.
Saya ingin perubahan menyeluruh pada UI/UX, arsitektur informasi, dan pengalaman
belajarnya. Implementasikan di repository lama, sambil mempersiapkan operasional
agen asuransi untuk tahap berikutnya. Jangan berhenti pada audit atau proposal.

**Arahan tambahan yang wajib:** InsuraLab harus berguna dan lengkap untuk alur
utamanya tanpa AI. Materi terkurasi dari `E:\Kerja\Insurance` menjadi fondasi
pengetahuan; AI adalah bantuan opsional di atasnya. UI/UX harus dirombak menjadi
lebih modern, profesional, dan menyampaikan rasa aman, kejelasan, serta perlindungan.

## 1. Lokasi dan keputusan yang sudah final

- Repository target: `D:\surya\Coding\sadani-tech\insuralab`.
- Referensi lokal: `E:\Kerja\Insurance`.
- Referensi pengalaman: https://hpt.muhammadiyah.or.id/3d . Baca juga `/explore`
  dan `/about` untuk memahami hubungan peta, pencarian sumber, dan percakapan AI.
- Plan awal: `D:\surya\Coding\sadani-tech\insuralabs\docs\REBUILD_PLAN.md`.
- Keputusan terbaru: revamp **in-place di insuralab**, memakai Laravel,
  Filament, Next.js, TypeScript, dan PostgreSQL yang sudah ada. Usulan membuat
  aplikasi di `insuralabs`, database produksi terpisah untuk aplikasi baru,
  dan mengganti backend ke NestJS dalam plan awal sudah dibatalkan.
- Nama produk untuk pekerjaan ini tetap InsuraLab. Perubahan branding/nama
  bukan prasyarat; jangan rename repository atau infrastruktur.
- Sasaran produk sekarang mencakup dua area: pengetahuan asuransi untuk publik
  dan workspace privat untuk mendukung pekerjaan agen. Detail operasional agen
  akan ditetapkan pada implementasi berikutnya.
- Graph, pencarian, glossary, bacaan, FAQ, panduan, sumber, dan rencana alur
  kerja agen harus bisa berjalan tanpa LLM, embedding, kuota, atau layanan AI.
  Dependensi aplikasi/database biasa tetap ada; tanpa AI tidak berarti seluruh
  aplikasi harus bekerja offline atau tanpa koneksi ke server.

Instruksi ini mengubah arah roadmap lama. Baca aturan repository, kemudian
perbarui dokumen produk/roadmap/constitution yang bertentangan agar konsisten
dengan arahan baru. Larangan lama memulai graph sebelum fase tertentu atau
membatasi produk selamanya pada edukasi tidak menghalangi tugas ini. Prinsip
akurasi sumber, batas kewenangan agen, independensi pengetahuan publik,
pengujian, dan perlindungan data tetap berlaku.

## 2. Audit singkat sebelum implementasi

1. Masuk ke repository target sebelum perintah Git/npm/Composer. Baca AGENTS.md
   yang berlaku, CLAUDE.md, README, frontend/AGENTS.md, dokumen visi, arsitektur,
   domain, konten, API, roadmap, dan ADR yang relevan.
2. Periksa status Git dan perubahan pengguna. Buat branch revamp bila kondisi
   memungkinkan tanpa mengganggu pekerjaan berjalan. Jangan reset, clean,
   overwrite perubahan pengguna, atau menggabungkan project lain ke commit.
3. Verifikasi struktur, dependency/runtime aktual, API, database, dan cara
   menjalankan aplikasi. Status “selesai” di dokumen bukan bukti runtime.
   Baca dokumentasi Next.js yang terpasang sesuai frontend/AGENTS.md.
4. Jalankan baseline pemeriksaan relevan dan lihat UI lama. Catat masalah nyata
   navigasi, hierarchy, typography, spacing, reading, mobile, dan interaksi.
5. Periksa referensi web secara visual jika tool memungkinkan. Ambil prinsip
   interaksinya, lalu buat identitas visual InsuraLab sendiri. Jika canvas tidak
   dapat diakses, jelaskan keterbatasan; jangan mengklaim telah menilainya.
6. Inventarisasi referensi lokal terlebih dahulu. Sampling cukup untuk mulai;
   tidak perlu mentranskripsi seluruh rekaman atau mengimpor semua PDF.

Lanjutkan langsung ke desain dan implementasi setelah audit yang memadai.
Ambil keputusan rutin sendiri dan dokumentasikan asumsi yang relevan.

## 3. Arah pengalaman dan desain visual

Bangun pengalaman modern dan profesional dengan kesan **perlindungan yang
menenangkan**: informasi tertata, langkah berikutnya jelas, dan pengguna merasa
didampingi saat memahami risiko. Saya mengizinkan penggantian total layout,
navigasi, komponen visual, dan design tokens yang sekarang. Ini harus tampak
sebagai perubahan desain yang menyeluruh, bukan sekadar pergantian warna.
Nilai desain dari kemudahan menemukan, memahami, dan menelusuri informasi.

- Peta pengetahuan menjadi pintu masuk utama, disertai akses bacaan dan panduan
  yang langsung terlihat. Navigasi publik inti: **Jelajah, Panduan, Tanya,
  Sumber**. Tanya membuka pencarian jawaban terkurasi terlebih dahulu; bantuan
  AI tersedia sebagai opsi tambahan. Akses **Workspace Agen** menuju area privat.
- `/` dapat menjadi explorer langsung; `/explore` boleh menjadi alias/redirect.
  Tentukan satu URL kanonis dengan selected node, pencarian, dan filter yang
  dapat dibagikan. Jangan memasukkan data privat ke URL publik.
- Tetapkan arah visual konkret sebelum coding: typography, ukuran teks,
  grid/spacing, warna permukaan, accent, warna jenis node, elevation, icon,
  focus/hover/selected states, serta motion. Gunakan variabel/token terpusat.
- Arah warna: permukaan putih hangat/off-white, teks navy/slate yang tegas,
  accent teal/emerald yang terukur, dan garis pemisah halus. Tetapkan nilai token
  dan kontrasnya saat desain. Canvas boleh memiliki variasi warna yang sesuai
  identitas ini; jangan membuat seluruh produk bergantung pada tampilan gelap.
- Tunjukkan perlindungan lewat komposisi berlapis, area konten yang tertata,
  pengelompokan yang jelas, dan ilustrasi garis yang relevan. Motif perisai,
  payung, atau lingkar pelindung boleh digunakan secara halus dan konsisten.
  Hindari pengulangan ikon perisai, neon/glow berlebihan, serta efek sci-fi
  yang mengganggu pembacaan hubungan di peta.
- Bangun kesan profesional lewat hierarchy yang tegas, reading width yang
  nyaman, ruang kosong yang terukur, alignment konsisten, ikon satu keluarga,
  border/radius/elevation yang disiplin, dan animasi singkat yang punya tujuan.
- Tonjolkan kejelasan sumber, tanggal tinjauan, serta langkah yang dapat diambil
  sebagai bukti kepercayaan. Jangan membuat badge sertifikasi, jaminan keamanan,
  afiliasi resmi, logo regulator, atau janji manfaat tanpa dasar yang sah.
- Desktop: navigasi ringkas, canvas dominan, pencarian mudah ditemukan,
  panel detail yang bisa ditutup, dan kontrol peta yang mudah dipahami.
- Mobile: canvas tetap terbaca, kontrol sentuh cukup besar, bottom sheet detail,
  safe area, keyboard chat, dan akses pencarian yang stabil. Pastikan panel
  tidak menutup seluruh kontrol penting dan halaman tidak overflow horizontal.
- Beri orientasi pengguna baru lewat contoh penelusuran dan petunjuk singkat.
  Gunakan bahasa Indonesia yang jelas untuk label serta penjelasan.
- Hindari hero besar dan deretan kartu berulang yang menggeser alat utama jauh
  dari layar pertama. Jangan mengisi dashboard dengan angka atau aktivitas palsu.
- Sediakan loading, kosong, gagal, retry, WebGL fallback, serta feedback untuk
  pencarian dan pemilihan node. Kontrol yang terlihat harus berfungsi; kapabilitas
  yang belum tersedia dijelaskan jujur atau disembunyikan.
- Reading view, glossary, sumber, produk, dan provider yang masih relevan
  mengikuti design system baru. Pelihara URL lama melalui tampilan baru atau
  redirect yang masuk akal; fitur sekunder tidak wajib muncul di navigasi utama.
- Presentasikan arah desain melalui token dan contoh layar explorer, reader,
  pencarian jawaban, serta shell agen. Iterasi berdasarkan screenshot nyata
  untuk hierarchy, kontras, kepadatan, dan kesan perlindungan yang konsisten.

## 4. Knowledge graph dan sumber

Peta berisi konsep, risiko, kategori proteksi, proses, provider, produk, dan
sumber. Setiap node mempunyai ID stabil, nama/alias, tipe, ringkasan, status
publikasi, serta metadata sumber/review yang sesuai. Setiap edge memiliki tipe,
arah jika relevan, penjelasan hubungan, dan bukti sumber yang dapat ditelusuri.

Interaksi inti:
- Cari istilah, pilih hasil, fokus node, sorot tetangga dan hubungan berlabel.
- Buka detail node: definisi, konteks, keterkaitan, sumber, status/tanggal review.
- Buka penjelasan hubungan dan passage/halaman yang menjadi dasarnya.
- Filter kelompok/jenis node, buka tetangga bertahap, reset tampilan.
- Pindah 3D ke 2D/daftar yang membawa informasi setara dan bisa dipakai keyboard.
- Dari detail, buka FAQ/panduan terkait atau pertanyaan tentang node; dari jawaban,
  kembali ke node/sumber. Semua tautan materi ini berfungsi tanpa AI.

Mulai dengan target 40–60 konsep dan 80–120 hubungan yang bermakna, disesuaikan
dengan ketersediaan bukti. Angka ini target kurasi, bukan izin membuat relasi
atau rujukan palsu. Node umum dapat mengambil konsep lama yang sudah ditinjau.
Sumber historis/needs_review wajib ditampilkan dengan status dan konteksnya.

Audit model lama dan perluas secara terukur. Gunakan kembali Source, glossary,
provider, produk, dan konten yang layak. Pilih satu identitas konsep yang jelas;
jangan membuat duplikasi glossary/node yang berbeda isi tanpa aturan sinkronisasi.
Metadata sumber saja belum cukup untuk citation: tambahkan versi dokumen,
passage/halaman stabil, dan hubungan evidence bila dibutuhkan.

Graph rendering hanya menyajikan data. Edge adalah relasi yang dikurasi;
kemiripan embedding tidak otomatis menjadi fakta. Ukuran/posisi node tidak
menyatakan kualitas produk atau tingkat kebutuhan pengguna.

Pilih library graph setelah membaca dokumentasinya dan menguji kompatibilitas
dengan stack aktual. Client-only loading, disposal resource, reduced motion,
query subgraph yang dibatasi, dan fallback perlu ditangani. Jangan menambah
database graph atau microservice tanpa kebutuhan yang terbukti.

## 5. Pengetahuan terkurasi sebagai dasar, AI sebagai opsi

### 5.1 Alur lengkap tanpa AI

Implementasikan pengalaman utama tanpa memanggil layanan AI. Pengguna harus
bisa mencari konsep, memahami keterkaitan, membaca penjelasan, mendapatkan
jawaban FAQ, mengikuti panduan, dan membuka sumber. Konten penjelasan tersimpan
serta ditinjau di CMS/database; jangan menghasilkan ulang definisi, ringkasan,
atau relasi melalui model setiap kali halaman dibuka.

- Pencarian memakai judul, istilah/alias, kata kunci, kategori, dan isi konten
  terbit di database. Keyword/full-text search harus berdiri sendiri; vector
  search dan embedding boleh menambah kualitas, bukan menjadi syarat hasil muncul.
- Sediakan jawaban FAQ editorial dengan pertanyaan, alias pertanyaan, jawaban,
  batasan, konsep terkait, dan rujukan passage/halaman. Pada kecocokan lemah,
  tampilkan daftar hasil terkait sebagai pilihan, bukan mengklaim sudah menjawab
  pertanyaan pengguna atau menggabungkan potongan menjadi jawaban baru.
- Sediakan artikel ringkas, glossary, penjelasan hubungan, perbandingan konsep,
  panduan langkah demi langkah, dan checklist yang bisa dipakai secara langsung.
- Dari setiap node, tampilkan materi dan sumber terkait secara deterministik.
  Pelajari selanjutnya menggunakan relasi dan urutan editorial yang tersimpan.
- Tampilan Tanya tetap berguna tanpa AI: pencarian FAQ, topik populer, dan hasil
  bacaan terkait. Bedakan label “Jawaban terkurasi” dari “Penjelasan AI”.
- Jika AI tidak diaktifkan, tidak tersedia, timeout, atau kuota habis, arahkan
  pengguna ke hasil terkurasi dengan konteks pencarian/node tetap terjaga.
  Jangan tampilkan error konfigurasi sebagai pengganti seluruh halaman.
- Aktifkan AI melalui pengaturan server/feature flag; default AI nonaktif dan
  aplikasi tetap dapat dijalankan sepenuhnya. Status ketersediaannya tidak boleh
  memicu panggilan model atau embedding dari core flow.

### 5.2 Bantuan AI bersumber, bila diaktifkan

Bangun fondasi retrieval dan percakapan yang terhubung dengan node serta sumber.
Sumber terbit yang memang diizinkan untuk pengguna menentukan materi yang boleh
dicari dan dikirim ke model. Filter akses dilakukan **sebelum retrieval dan
pemanggilan model**, termasuk untuk pencarian semantik, cache, dan citation.

- Jawaban menyertakan rujukan yang benar-benar bisa dibuka dan mendukung klaim.
- Bukti yang kurang menghasilkan penjelasan keterbatasan, bukan fabrikasi.
- Pertahankan konteks versi, provider, dan perbedaan antarsumber.
- Instruksi dalam dokumen diperlakukan sebagai data; sanitasi hasil rendering.
- API key server-side, timeout/cancel, batas input, throttling, kuota biaya,
  error/retry, dan pencatatan pemakaian disiapkan sesuai kebutuhan.
- Riwayat pengguna bersifat privat; tidak otomatis menjadi Q&A publik.
- Jika credential/model belum dikonfigurasi, selesaikan adapter dan alurnya,
  pertahankan mode pengetahuan terkurasi dan beri penjelasan singkat bahwa
  bantuan AI belum tersedia. Detail konfigurasi hanya untuk pengelola; jelaskan
  bagian yang belum diuji di laporan. Jangan menampilkan respons AI simulasi
  sebagai jawaban model yang sungguhan.

Pemilihan model/embedding/provider ditentukan berdasarkan kemampuan, biaya,
akses data, dan kebutuhan implementasi, bukan asumsi nama model tertentu.

## 6. Penggunaan referensi E:\Kerja\Insurance

Inventaris awal menemukan folder Allianz Individu, Allianz Syariah, Allianz
Umum, brosur, materi/playbook, allianz discover, dan recordings. Ada PDF,
spreadsheet, presentasi, gambar, dan video. Nama folder tidak membuktikan
produk masih aktif, skema produk, hak publikasi, atau akurasi informasinya.

Klasifikasikan bahan menjadi public-approved, internal/restricted, personal,
dan belum ditinjau. Default impor adalah belum ditinjau, bukan public-approved.
Pisahkan status hak akses dari status akurasi/tanggal review.

Temuan sampel yang perlu diperhatikan:
- `materi/playbook/Playbook Allisya CI Hasanah.pdf` menyatakan materi internal
  dan melarang penyebarluasan/publikasi. Perlakukan ini sebagai batas nyata;
  jangan mengunggah, mengindeks ke AI publik, membuat public excerpt/summary,
  atau menganggap login agen saja memberi izin penggunaan. Catat metadata
  pembatasannya dan gunakan hanya dengan otorisasi yang sesuai.
- `materi/MARKET SURVEY dan SKRIP REKPRO ALLISYA.pdf` bisa menjadi masukan
  memahami alur survey/presentasi agen. Tinjau ulang pertanyaan yang menggiring,
  penyederhanaan manfaat, dan pengumpulan data; jangan menyalinnya mentah sebagai
  formulir publik atau menganggap seluruh klaim di dalamnya fakta terkini.
- `materi/vision-guide.txt` memuat klaim pemasaran/pendapatan yang belum
  diverifikasi. Jangan menjadikannya jaminan, konten faktual, atau aturan komisi.
- Contoh ilustrasi bernama individu dapat memuat data pribadi. Jangan membaca
  massal, menyalin ke repo, memakai sebagai fixture/demo, atau mengirimkannya
  ke layanan eksternal. Gunakan data sintetis yang jelas untuk pengujian UI.

Buat inventaris aman: kategori dokumen, checksum/dedup, status hak akses,
penerbit/versi bila diketahui, kebutuhan review/OCR, dan pemetaan ke konsep.
Laporan repository tidak memuat identitas personal atau daftar berkas sensitif.
Jangan copy seluruh folder referensi, dokumen internal, video, atau spreadsheet
ke public/static assets, Git, seeders, log, maupun embedding store bersama.
Gunakan referensi lokal sebagai sumber read-only dan kelola dokumen secara privat.
Brosur/RIPLAY pun perlu verifikasi versi dan hak penggunaan sebelum publikasi.

### Paket konten yang wajib tersedia tanpa AI

Kurasi bahan yang diizinkan dari folder referensi tersebut menjadi konten
persisten yang benar-benar dipakai UI. Inventaris file atau tombol membuka PDF
saja belum memenuhi kebutuhan ini. Gunakan konten lama yang valid bila relevan,
kemudian lengkapi dari sumber lokal yang lolos review.

Target awal, disesuaikan dengan bukti dan izin yang tersedia:
- 40–60 konsep/glossary beserta ringkasan dan relasi yang dapat ditelusuri.
- Sedikitnya 15 FAQ editorial tentang konsep, membaca dokumen, serta proses.
- Sedikitnya 6 panduan/checklist, misalnya membaca RIPLAY/polis, memahami pihak
  dalam polis, mencatat manfaat dan batasan, mempersiapkan konsultasi, menelusuri
  prosedur klaim dari dokumen, dan memahami perbedaan konsep pembayaran manfaat.
- Ringkasan sumber dan studi kasus produk yang diizinkan, dengan entitas
  penerbit, versi/periode, halaman rujukan, review status, dan keterbatasannya.
- Rancangan template operasional agen tanpa AI: agenda appointment, catatan
  tindak lanjut, checklist konsultasi/presentasi, serta pendampingan layanan.
  Template agen masuk blueprint tahap berikutnya, bukan alasan membangun CRM
  lengkap sekarang. Materi internal tetap membutuhkan izin penggunaan yang sesuai.

Jangan memenuhi target angka dengan duplikasi, sumber fiktif, atau publikasi
bahan restricted. Jika ada kekurangan, selesaikan konten yang sah, catat jumlah
nyata dan gap-nya. Gunakan seed/import idempotent serta CMS yang dapat memperbarui
konten tanpa model AI. Ekstraksi dapat memakai parser/OCR lokal; jangan membuat
proses pengisian maupun pemeliharaan materi wajib memakai layanan AI eksternal.

Buat `docs/CONTENT_COVERAGE.md`: jenis/jumlah konten yang selesai, pemetaan
ke konsep dan sumber yang aman dicatat, status review/izin, jalur UI tempat
konten digunakan, serta kekurangan yang perlu ditindaklanjuti. Jangan menyebut
konten sudah ditinjau manusia jika pemeriksaan editorialnya belum terjadi;
pertahankan sebagai draft/needs_review sesuai keadaan sebenarnya.

## 7. Persiapan operasional agen untuk tahap berikutnya

Siapkan arsitektur dan UX agar operasional agen bisa diterapkan kemudian dalam
aplikasi yang sama. Detail proses di bawah adalah usulan backlog untuk divalidasi,
bukan SOP final yang harus diimplementasikan seluruhnya sekarang.

Usulan alur kerja:
**Prospek → tindak lanjut → appointment → pencatatan kebutuhan → persiapan
presentasi → pelacakan pengajuan → pencatatan polis → layanan berkelanjutan
dan pendampingan klaim.**

Domain yang perlu dirancang:
- Workspace/team, membership, peran, kepemilikan record, dan penugasan.
- Prospek/kontak, izin komunikasi, sumber prospek, aktivitas, notes, dan tasks.
- Appointment/follow-up, pipeline opportunity, dan checklist kebutuhan.
- Materi presentasi, produk dan sumber versi tertentu sebagai referensi kerja.
- Application/policy tracking, pengingat layanan, dan claim assistance.
- Training/lisensi/CPD serta team reporting sebagai opsi fase lanjutan.

Hasil **sekarang**:
1. Buat `docs/AGENT_OPERATIONS_BLUEPRINT.md` berisi alur pengguna, domain,
   proposed ERD, lifecycle/status transisi, batas kewenangan, ownership scope,
   matriks akses, data minimum, integrasi, asumsi, dan backlog berurutan.
2. Beri tempat yang jelas untuk area privat `/agent` dalam arsitektur/routing.
   Bila membuat route/shell sekarang, gunakan autentikasi dan otorisasi server
   yang benar dengan deny-by-default; jangan mengandalkan menyembunyikan menu.
   Isi awal boleh onboarding atau empty state yang jujur. Tidak perlu membuat
   CRM semu, kontak, laporan, grafik, atau pipeline berisi data demo produksi.
3. Tentukan pemisahan public knowledge, restricted knowledge, dan agent records
   pada API, pencarian, storage, caching, dan AI. Reuse layanan pengetahuan
   dengan scope akses yang eksplisit. Jangan menyimpan kontak/notes nasabah
   sebagai node graph publik atau konten editorial.
4. Dokumentasikan opsi agent/team boundary dan relasi ke identitas user yang
   sudah ada. Implementasikan hanya entity/migration/policy yang dibutuhkan
   oleh fitur aktif sekarang. Jika area privat belum dapat dijamin aman,
   tetap nonaktifkan fiturnya dan dokumentasikan kontrak integrasinya.

Hasil **nanti**, belum diimplementasikan pada revamp ini: CRUD CRM lengkap,
otomasi WhatsApp/email, impor data nasabah nyata, dokumen identitas/medis,
integrasi sistem pengajuan insurer, quote/pricing, pembayaran, komisi/overriding,
target karier, recruitment automation, dan perhitungan rekomendasi individual.

Komisi/jabatan/lisensi berbeda berdasarkan provider dan periode; rancang
kebutuhan versioning di blueprint tanpa mengarang rule atau tabel produksi.
Workspace agen membantu pencatatan dan layanan; aplikasi tidak mengambil
keputusan underwriting, persetujuan klaim, atau menerbitkan polis. Integrasi
resmi/provider tidak diasumsikan tersedia dan jangan membuat API fiktif.

Seluruh alur operasional yang dirancang harus mempunyai jalur manual yang
lengkap: form terstruktur, status, tugas, checklist, dan referensi yang dipilih
pengguna. AI kelak boleh membantu merangkum atau menyarankan draft, tetapi
tidak wajib untuk membuat/mengubah record, mencari materi, atau menyelesaikan
pekerjaan agen. Pisahkan fitur bantu AI dari aturan bisnis dan penyimpanan data.

## 8. Urutan pelaksanaan dan batas selesai

A. Audit, sinkronkan visi/roadmap, tulis ADR revamp, dan tetapkan arah visual.
B. Buat vertical slice nyata: shell baru → search → graph → node detail →
   passage sumber. Uji visual desktop/mobile dan iterasi sampai nyaman dipakai.
C. Hubungkan model/API/editorial, status publikasi, versi sumber, dan data yang
   layak. Selesaikan paket konten tanpa AI, keyword search, FAQ, panduan, dan
   reader. Prototype lokal boleh sementara; hasil final slice memakai data API.
D. Verifikasi seluruh alur inti dengan AI dimatikan, lalu integrasikan bantuan
   AI opsional sejauh konfigurasi memungkinkan. Tangani perpindahan ke hasil
   terkurasi saat layanan AI gagal dan laporkan batas verifikasi yang nyata.
E. Selesaikan agent blueprint dan boundary/shell privat yang aman sesuai scope.
F. Rapikan reading view serta route penting lama, lakukan QA, dan perbarui docs.

Jangan menunggu persetujuan ulang untuk desain, refactor, dependency yang
diperlukan, atau perubahan lokal yang sudah tercakup tugas. Lanjutkan pekerjaan
mandiri sampai slice revamp dan deliverable yang disebutkan selesai. Tanyakan
hanya keputusan material yang benar-benar tidak bisa diinferensikan; kerjakan
bagian independen sambil menunggu. Integrasi yang butuh credential/izin eksternal
boleh tercatat sebagai belum terverifikasi, tanpa memblokir semua pekerjaan lain.

## 9. Perlindungan pekerjaan dan verifikasi

- Gunakan perubahan schema aditif dan backfill/import yang idempotent. Jangan
  memakai migrate:fresh, menghapus tabel/data lama, atau drop database aktif.
  Migrasi dari nol/rollback hanya di database testing terisolasi yang diverifikasi.
- Jangan menghapus implementasi lama sebelum penggantinya siap; fitur sekunder
  boleh dikeluarkan dari navigasi tanpa memusnahkan data maupun riwayatnya.
- Jangan push, deploy, menambahkan integrasi berbayar, mengirim komunikasi ke
  prospek, atau mempublikasikan materi referensi dalam tugas ini.
- Sesuaikan pengujian UI dengan perilaku baru. Jangan sekadar menghapus tes
  yang gagal atau mempertahankan snapshot untuk desain yang sudah diganti.
- Uji API/publication/ACL, IDOR lintas user/workspace, sumber restricted pada
  graph/search/citation/cache/AI, input dokumen, serta graph pagination/bounds.
- Uji alur cari → node → relasi → sumber → tanya → kembali ke graph; direct
  URL, back/forward, loading/error, WebGL gagal, keyboard, dan reduced motion.
- Jalankan uji dengan AI/embedding dinonaktifkan dan tanpa credential model:
  graph, keyword search, glossary, FAQ, panduan/checklist, sumber/reader, serta
  boundary area agen tetap berfungsi. Pastikan tidak ada request ke provider AI
  atau pekerjaan embedding yang dipicu oleh alur inti maupun startup aplikasi.
- Uji AI timeout/kuota habis dan transisi ke konten terkurasi tanpa kehilangan
  pertanyaan atau pilihan node. Uji hasil pencarian lemah/tidak ada hasil agar
  tidak menampilkan jawaban yang tidak didukung sumber.
- Jalankan test backend dan frontend, formatting checks, lint, typecheck, dan
  production build yang sesuai perubahan. Catat baseline failure terpisah.
- Jalankan aplikasi; ambil dan periksa screenshot desktop serta mobile untuk
  explorer, node panel, search, source reader, chat, dan shell privat jika dibuat.
  Simpan screenshot yang aman agar hasil dapat ditinjau. Jangan klaim QA visual
  bila hanya build/test yang dijalankan.
- Ukur graph pada dataset awal dan lebih besar. Target awal: interaktif dalam
  3 detik dan minimal 30 FPS pada perangkat uji yang dicatat, dengan batas node
  dan adaptive detail. Laporkan hasil aktual dan perbaiki hambatan utama.
- Evaluasi retrieval/AI pada pertanyaan relevan, di luar corpus, bukti kurang,
  versi bertentangan, dan restricted source. Citation valid harus membuka passage
  pendukung; keberadaan link saja belum membuktikan klaimnya benar.

## 10. Deliverable akhir

- Revamp UI/UX yang berjalan di `insuralab`, dengan explorer sebagai pusat.
- Graph, search, detail node/edge, sumber, dan fallback yang berfungsi.
- Paket pengetahuan terkurasi dari sumber lokal yang diizinkan, dengan glossary,
  FAQ, panduan/checklist, reading view, dan pencarian yang berfungsi tanpa AI.
- Integrasi/fondasi bantuan AI opsional, fallback ke materi terkurasi, serta
  status implementasi yang jujur. Tanpa AI tetap merupakan pengalaman lengkap.
- Rancangan operasional agen dan boundary privat yang sesuai scope sekarang.
- UI modern dan profesional dengan identitas perlindungan yang konsisten pada
  explorer, reader, pencarian jawaban, sumber, serta shell privat yang dibuat.
- `docs/REVAMP_PLAN.md`, `docs/CONTENT_COVERAGE.md`,
  `docs/AGENT_OPERATIONS_BLUEPRINT.md`, ADR yang relevan,
  serta README/visi/arsitektur/roadmap/constitution yang konsisten dan mutakhir.
- Ringkasan perubahan, cara menjalankan dan meninjau, hasil pemeriksaan,
  screenshot aman, serta backlog/credential/keputusan yang masih diperlukan.

Mulai dengan membaca repository dan referensi, jelaskan temuan penting secara
singkat, lalu kerjakan implementasinya. Prioritaskan hasil yang nyata, dapat
ditinjau, dan mudah dikembangkan menjadi alat kerja agen pada fase berikutnya.
Keberhasilan revamp dinilai pertama-tama dari kegunaan materi dan pengalaman
tanpa AI, kemudian dari nilai tambah bantuan AI yang tersedia.
