"""Build the small, first-party editorial corpus. No AI, personal files, or bulk PDF imports.

The source PDFs remain outside Git. Re-running this script is deterministic.
"""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
groups = {
    'dasar': ('Dasar perlindungan', [
        ('risiko', 'Risiko', 'Kemungkinan suatu peristiwa menimbulkan kerugian. Bedakan peristiwanya dari dampak keuangannya.'),
        ('asuransi', 'Asuransi', 'Perjanjian perlindungan atas risiko tertentu dengan syarat, kewajiban, dan manfaat yang tertulis dalam polis.'),
        ('risk-transfer', 'Pengalihan risiko', 'Pada asuransi konvensional, risiko yang disepakati dialihkan kepada penanggung sesuai kontrak.'),
        ('premi', 'Premi', 'Pembayaran untuk pertanggungan sesuai jadwal dan ketentuan polis. Premi tidak otomatis sama dengan tabungan.'),
        ('manfaat', 'Manfaat', 'Pembayaran atau layanan yang diberikan apabila syarat pertanggungan terpenuhi.'),
        ('uang-pertanggungan', 'Uang pertanggungan', 'Nilai pertanggungan yang tercantum pada polis. Pembayaran tetap mengikuti peristiwa dan ketentuan manfaat.'),
        ('indemnity', 'Indemnitas', 'Prinsip penggantian kerugian yang menjadi dasar sebagian manfaat penggantian biaya; batas dan syaratnya mengikuti polis.'),
        ('dana-darurat', 'Dana darurat', 'Dana yang dapat diakses untuk kebutuhan tak terduga. Fungsinya berbeda dari kontrak pertanggungan asuransi.'),
    ]),
    'polis': ('Mengenal polis', [
        ('polis', 'Polis', 'Dokumen kontrak yang memuat pihak, hak, kewajiban, pertanggungan, serta ketentuan asuransi.'),
        ('pemegang-polis', 'Pemegang polis', 'Pihak yang memiliki kontrak dan menjalankan hak serta kewajiban yang ditetapkan di dalamnya.'),
        ('tertanggung', 'Tertanggung', 'Orang yang risikonya dipertanggungkan. Dapat berbeda dari pemegang polis.'),
        ('penerima-manfaat', 'Penerima manfaat', 'Pihak yang ditunjuk atau berhak menerima manfaat sesuai ketentuan polis.'),
        ('pengecualian', 'Pengecualian', 'Peristiwa atau kondisi yang dinyatakan tidak dijamin. Baca bersama definisi manfaat.'),
        ('masa-tunggu', 'Masa tunggu', 'Periode ketika manfaat tertentu belum berlaku menurut ketentuan polis. Tidak sama pada semua manfaat.'),
        ('free-look', 'Masa mempelajari polis', 'Kesempatan mempelajari dan menanggapi polis sesuai jangka waktu serta prosedur dalam dokumen produk.'),
        ('riplay', 'RIPLAY', 'Ringkasan Informasi Produk dan Layanan. Membantu mengenali produk, tetapi tidak menggantikan ketentuan lengkap polis.'),
        ('lapse', 'Lapse', 'Keadaan polis tidak berlaku karena kondisi yang ditetapkan kontrak, misalnya kewajiban pembayaran yang tidak terpenuhi.'),
    ]),
    'proteksi': ('Jenis proteksi', [
        ('asuransi-jiwa', 'Proteksi jiwa', 'Kategori perlindungan yang membahas dampak finansial meninggal dunia, dengan manfaat sesuai kontrak.'),
        ('asuransi-kesehatan', 'Proteksi kesehatan', 'Perlindungan terkait biaya atau peristiwa kesehatan yang dijamin. Cara pembayaran dan batas manfaat berbeda antarpolis.'),
        ('penyakit-kritis', 'Penyakit kritis', 'Kategori manfaat untuk kondisi yang memenuhi definisi penyakit kritis dalam polis; diagnosis saja belum menjelaskan seluruh syarat klaim.'),
        ('tpd', 'Disabilitas tetap', 'Kategori manfaat dengan definisi disabilitas tetap yang ditentukan polis. Periksa kriteria dan bukti yang diminta.'),
        ('addb', 'Proteksi kecelakaan', 'Kategori manfaat terkait kecelakaan sesuai definisi, peristiwa, dan tabel manfaat dalam polis.'),
        ('rider', 'Manfaat tambahan', 'Pertanggungan tambahan yang dilekatkan pada polis dasar yang sesuai. Periksa ketergantungan dan biaya masing-masing.'),
        ('polis-dasar', 'Polis dasar', 'Kontrak utama yang menjadi tempat melekatnya manfaat tambahan atau rider.'),
        ('human-life-value', 'Kebutuhan pengganti penghasilan', 'Topik untuk memahami dampak hilangnya penghasilan terhadap tanggungan; bukan penilaian harga nyawa atau rekomendasi nominal otomatis.'),
    ]),
    'klaim': ('Klaim & layanan', [
        ('klaim', 'Klaim', 'Permintaan manfaat kepada penanggung atau pengelola setelah terjadi peristiwa yang dianggap memenuhi pertanggungan.'),
        ('cashless', 'Cashless', 'Fasilitas penyelesaian tagihan melalui jaringan dan prosedur yang disepakati. Tidak berarti seluruh biaya pasti dijamin.'),
        ('reimbursement', 'Reimbursement', 'Mekanisme mengajukan penggantian biaya yang telah dibayar, dengan bukti dan syarat sesuai polis.'),
        ('lump-sum', 'Santunan sekaligus', 'Bentuk pembayaran manfaat sekaligus berdasarkan ketentuan, berbeda dari penggantian setiap bukti biaya.'),
        ('deductible', 'Risiko sendiri', 'Bagian biaya yang menjadi tanggungan pemegang polis atau tertanggung menurut ketentuan manfaat.'),
        ('co-share', 'Pembagian biaya', 'Mekanisme membagi biaya yang memenuhi syarat antara nasabah dan penanggung sesuai perhitungan dalam kontrak.'),
        ('survival-period', 'Survival period', 'Syarat masa bertahan hidup setelah peristiwa tertentu bila memang diatur pada manfaat terkait.'),
    ]),
    'proses': ('Proses & pendampingan', [
        ('underwriting', 'Underwriting', 'Penilaian risiko oleh perusahaan sebelum menetapkan keputusan pertanggungan. Agen membantu informasi, bukan menetapkan keputusan.'),
        ('field-underwriting', 'Pencatatan awal risiko', 'Peran pendamping dalam membantu memperoleh dan meneruskan informasi yang akurat untuk proses pengajuan.'),
        ('medical-underwriting', 'Penilaian kesehatan', 'Bagian proses underwriting yang menilai informasi kesehatan sesuai kebutuhan perusahaan.'),
        ('financial-underwriting', 'Penilaian finansial', 'Bagian proses underwriting yang menilai kewajaran pertanggungan berdasarkan informasi finansial.'),
        ('spaj', 'Pengajuan asuransi', 'Dokumen permintaan pertanggungan yang harus diisi lengkap, benar, dan konsisten. Pengajuan belum sama dengan penerimaan.'),
        ('inforce', 'Polis aktif', 'Status pertanggungan yang telah memenuhi ketentuan berlakunya polis. Periksa tanggal efektif dan kondisi manfaat.'),
        ('agen', 'Pendampingan agen', 'Bantuan untuk memahami informasi, menyiapkan pengajuan, serta menelusuri layanan. Keputusan underwriting dan klaim tetap pada perusahaan.'),
    ]),
    'syariah': ('Prinsip syariah', [
        ('asuransi-syariah', 'Asuransi syariah', 'Perlindungan berbasis prinsip saling menolong antarpeserta melalui pengelolaan dana dan akad yang dijelaskan pada dokumen produk.'),
        ('tabarru', 'Tabarru’', 'Dana kebajikan peserta yang digunakan untuk saling menolong sesuai ketentuan pengelolaan dalam akad.'),
        ('taawun', 'Ta’awun', 'Prinsip saling menolong yang mendasari hubungan antarpeserta dalam perlindungan syariah.'),
        ('ujrah', 'Ujrah', 'Imbalan pengelolaan yang ditetapkan sesuai akad. Periksa dasar, nilai, dan waktu pembebanannya pada dokumen.'),
        ('risk-sharing', 'Berbagi risiko', 'Pendekatan saling menanggung risiko antarpeserta sesuai ketentuan dana dan akad, dengan perusahaan sebagai pengelola.'),
        ('wakalah-bil-ujrah', 'Wakalah bil ujrah', 'Akad pemberian kuasa pengelolaan dengan imbalan ujrah sesuai ketentuan yang disepakati.'),
    ]),
}
guides = [
    ('membaca-riplay', 'Membaca RIPLAY dengan lebih percaya diri', 'Kenali bagian penting dokumen sebelum masuk ke rincian polis.', 'polis', ['Catat nama produk dan entitas penerbit; jangan hanya nama mereknya.', 'Baca manfaat bersama syarat, risiko, biaya, dan pengecualian.', 'Pisahkan ilustrasi dari janji kontraktual; tandai asumsi dan versinya.', 'Bandingkan dengan RIPLAY personal dan polis yang benar-benar diterima.', 'Tuliskan pertanyaan yang belum terjawab sebelum mengambil keputusan.']),
    ('mengenal-pihak-polis', 'Siapa melakukan apa dalam polis?', 'Bedakan pemegang polis, tertanggung, penerima manfaat, dan perusahaan.', 'polis', ['Temukan nama pemegang polis dan tertanggung.', 'Periksa penerima manfaat beserta prosedur pembaruannya.', 'Catat perusahaan yang menerbitkan kontrak.', 'Baca hak, kewajiban, dan saluran layanan masing-masing pihak.', 'Konfirmasikan perbedaan identitas atau istilah sebelum mengajukan perubahan.']),
    ('manfaat-dan-batasan', 'Baca manfaat bersama batasannya', 'Gunakan satu catatan agar gambaran perlindungan tidak terpotong.', 'proteksi', ['Tulis peristiwa yang dijamin dan definisinya.', 'Catat bentuk pembayaran serta batas per manfaat bila ada.', 'Periksa masa tunggu, pengecualian, risiko sendiri, dan pembagian biaya.', 'Tandai manfaat dasar dan tambahan serta ketergantungannya.', 'Gunakan ketentuan polis untuk memastikan informasi yang belum jelas.']),
    ('persiapan-konsultasi', 'Siapkan percakapan tentang proteksi', 'Bawa pertanyaan yang membantu memahami pilihan tanpa terburu-buru.', 'dasar', ['Tuliskan risiko keuangan yang ingin dipahami.', 'Catat perlindungan yang sudah ada, termasuk fasilitas kerja atau BPJS.', 'Tentukan batas kemampuan pembayaran secara pribadi; tidak perlu memasukkan data sensitif di sini.', 'Minta penjelasan manfaat, biaya, risiko, dan kewajiban secara berimbang.', 'Simpan rujukan dan waktu untuk membaca sebelum mengambil keputusan.']),
    ('menelusuri-klaim', 'Menelusuri prosedur klaim', 'Checklist membaca prosedur, bukan jaminan bahwa klaim akan disetujui.', 'klaim', ['Buka ketentuan klaim pada versi polis yang berlaku.', 'Temukan saluran pelaporan, dokumen, dan batas waktu yang ditentukan.', 'Cocokkan jenis manfaat dengan peristiwa serta bukti yang tersedia.', 'Gunakan saluran resmi untuk penyerahan dokumen pribadi.', 'Catat nomor referensi dan tindak lanjut; keputusan akhir ada pada perusahaan.']),
    ('cara-pembayaran-manfaat', 'Cashless, reimbursement, atau santunan?', 'Pahami mekanismenya sebelum membandingkan nominal manfaat.', 'klaim', ['Cari nama dan definisi manfaat pada polis.', 'Periksa apakah manfaat mengganti biaya atau berupa santunan.', 'Untuk cashless, baca jaringan, persetujuan, dan bagian biaya sendiri.', 'Untuk reimbursement, lihat bukti biaya dan prosedur pengajuan.', 'Untuk santunan, baca pemicu manfaat dan syarat yang harus terpenuhi.']),
]
faq = [
    ('apa-itu-asuransi', 'Mulai dari mana untuk memahami asuransi?', 'Mulai dari risiko, lalu pelajari apa yang dijamin, apa yang tidak dijamin, dan kewajiban dalam polis. Gunakan peta untuk membuka istilah yang belum familiar.', ['asuransi', 'risiko', 'polis']),
    ('premi-bukan-tabungan', 'Apakah premi sama dengan tabungan?', 'Premi adalah pembayaran sesuai kontrak pertanggungan. Adanya unsur investasi atau nilai tunai pada produk tertentu tidak membuat seluruh premi menjadi tabungan. Baca struktur produk, biaya, dan risikonya.', ['premi', 'asuransi']),
    ('cashless-bukan-tanpa-batas', 'Apakah cashless berarti semua biaya dibayar?', 'Cashless menjelaskan fasilitas penyelesaian tagihan. Cakupan, jaringan, persetujuan, batas manfaat, dan biaya yang ditanggung sendiri tetap mengikuti ketentuan polis.', ['cashless', 'deductible', 'co-share']),
    ('beda-reimbursement', 'Apa beda reimbursement dan santunan sekaligus?', 'Reimbursement berkaitan dengan penggantian biaya yang memenuhi syarat. Santunan sekaligus dibayarkan berdasarkan pemicu manfaat dalam polis. Keduanya tidak dapat disamakan hanya dari nominalnya.', ['reimbursement', 'lump-sum']),
    ('riplay-dan-polis', 'Apakah RIPLAY menggantikan polis?', 'RIPLAY merangkum informasi produk dan layanan. Ketentuan lengkap, hak, kewajiban, serta pertanggungan mengikuti dokumen kontrak yang berlaku. Baca keduanya dan tanyakan perbedaan yang ditemukan.', ['riplay', 'polis']),
    ('pihak-dalam-polis', 'Apakah pemegang polis selalu tertanggung?', 'Tidak selalu. Pemegang polis memiliki kontrak, sedangkan tertanggung adalah orang yang risikonya dipertanggungkan. Periksa nama dan peran pada dokumen.', ['pemegang-polis', 'tertanggung', 'polis']),
    ('siapa-penerima', 'Di mana memeriksa penerima manfaat?', 'Lihat penunjukan penerima manfaat pada dokumen polis dan prosedur perubahan melalui perusahaan. Penerima manfaat mempunyai peran yang berbeda dari pemegang polis dan tertanggung.', ['penerima-manfaat', 'polis']),
    ('apa-itu-rider', 'Apa yang perlu dicek sebelum memahami rider?', 'Periksa polis dasar yang kompatibel, manfaat yang ditambahkan, biaya, syarat berlaku, dan akibat perubahan polis dasar terhadap rider. Jangan menganggap setiap rider dapat berdiri sendiri.', ['rider', 'polis-dasar']),
    ('masa-tunggu', 'Apakah semua manfaat punya masa tunggu yang sama?', 'Jangan mengasumsikannya. Baca syarat masing-masing manfaat dan tanggal mulai berlaku. Masa tunggu dan survival period juga mempunyai fungsi yang berbeda.', ['masa-tunggu', 'survival-period']),
    ('keputusan-underwriting', 'Apakah agen menentukan hasil underwriting?', 'Agen dapat membantu menyampaikan informasi dengan benar. Perusahaan menilai risiko dan menetapkan keputusan. Pengajuan atau pembayaran saja belum membuktikan pertanggungan sudah berlaku.', ['agen', 'underwriting', 'spaj']),
    ('jaminan-klaim', 'Apakah pendampingan agen menjamin klaim disetujui?', 'Pendampingan membantu memahami proses dan kelengkapan dokumen. Persetujuan klaim tetap ditetapkan perusahaan berdasarkan polis dan bukti yang diajukan.', ['agen', 'klaim']),
    ('polis-aktif', 'Bagaimana memastikan polis sudah aktif?', 'Periksa konfirmasi perusahaan, tanggal efektif, dan kewajiban yang harus dipenuhi. Status pengajuan berbeda dari status pertanggungan. Syarat manfaat tertentu tetap perlu diperiksa.', ['inforce', 'spaj', 'masa-tunggu']),
    ('mengapa-pengecualian', 'Mengapa pengecualian perlu dibaca bersama manfaat?', 'Nama manfaat belum menjelaskan seluruh cakupan. Pengecualian menunjukkan kondisi yang tidak dijamin, sehingga membantu membaca batas perlindungan secara utuh.', ['pengecualian', 'manfaat']),
    ('syariah', 'Apa yang dipelajari lebih dulu dalam asuransi syariah?', 'Kenali prinsip saling menolong, akad, dana tabarru’, dan imbalan pengelolaan. Setelah itu baca penerapannya pada dokumen produk yang sedang dipelajari.', ['asuransi-syariah', 'tabarru', 'ujrah']),
    ('dana-darurat', 'Apakah dana darurat dan asuransi memiliki fungsi yang sama?', 'Keduanya membantu menghadapi ketidakpastian dengan cara yang berbeda. Dana darurat adalah dana yang bisa diakses, sedangkan asuransi memberikan manfaat sesuai syarat kontrak. Pahami keduanya dalam konteks kebutuhan pribadi.', ['dana-darurat', 'asuransi', 'risiko']),
]
nodes = []
passages = []
for group, (name, terms) in groups.items():
    for slug, title, summary in terms:
        nodes.append(dict(slug=slug, title=title, summary=summary, group=group))
    passages.append(dict(key=f'editorial-{group}', source='editorial', version='12 November 2026 · draf editorial', locator=name, body='Catatan pembelajaran InsuraLab. Disusun untuk membantu membaca dokumen, belum ditinjau ahli independen.\n\n'+'\n\n'.join(f'{title}: {summary}' for _, title, summary in terms)))
edges = []
# Deliberate reading links: these are labelled associations, not causal assertions.
for group, (_, terms) in groups.items():
    anchor = terms[0][0]
    second = terms[1][0]
    for slug, title, _ in terms[1:]:
        edges.append(dict(source=anchor, target=slug, passage=f'editorial-{group}', label='Konteks pembelajaran', explanation=f'Baca {title.lower()} dalam kelompok {groups[group][0].lower()} untuk memahami istilah beserta perannya. Hubungan ini adalah panduan membaca, bukan jaminan manfaat.'))
    for slug, title, _ in terms[2:]:
        edges.append(dict(source=second, target=slug, passage=f'editorial-{group}', label='Pelajari bersama', explanation=f'Catatan {groups[group][0].lower()} menjelaskan kedua istilah agar peran dan batasannya dapat dibedakan. Buka rujukan untuk membaca definisi masing-masing.'))
cross = [('premi','polis'),('manfaat','klaim'),('manfaat','reimbursement'),('manfaat','lump-sum'),('polis','underwriting'),('polis','inforce'),('rider','polis'),('cashless','asuransi-kesehatan'),('klaim','pengecualian'),('underwriting','tertanggung'),('asuransi','asuransi-syariah'),('agen','klaim'),('asuransi-jiwa','penerima-manfaat')]
by_slug = {n['slug']: n for n in nodes}
for a,b in cross:
    group=by_slug[b]['group']
    edges.append(dict(source=a,target=b,passage=f'editorial-{group}',label='Bacaan terkait',explanation=f'Gunakan definisi {by_slug[b]["title"].lower()} sebagai konteks ketika mempelajari {by_slug[a]["title"].lower()}. Tautan editorial ini tidak menyatakan cakupan atau keputusan polis tertentu.'))
entries=[]
for slug,title,summary,group,steps in guides:
    entries.append(dict(slug=slug,kind='guide',title=title,summary=summary,body='Panduan belajar mandiri. Gunakan dokumen produk yang sesuai dan konfirmasikan informasi yang belum jelas melalui saluran resmi. Checklist ini tidak mengirim atau menyimpan informasi pribadi.',steps=steps,keywords=[group,title],terms=[n['slug'] for n in nodes if n['group']==group],passage=f'editorial-{group}'))
for slug,title,answer,terms in faq:
    entries.append(dict(slug=slug,kind='faq',title=title,summary=answer,body=answer+'\n\nPenjelasan umum untuk belajar. Ketentuan yang berlaku tetap mengikuti versi dokumen kontrak masing-masing.',steps=[],keywords=terms,terms=terms,passage=f'editorial-{by_slug[terms[0]]["group"]}'))
sources=[dict(key='editorial',title='Catatan pembelajaran InsuraLab',publisher='InsuraLab · materi editorial',url=None,notes='Materi pengantar buatan InsuraLab; belum ditinjau ahli independen. Tidak ada data personal atau salinan dokumen internal.'),dict(key='apm',title='RIPLAY Umum Allianz Preferred Medical',publisher='PT Asuransi Allianz Life Indonesia',url='https://www.allianz.co.id/produk/asuransi-individu/asuransi-kesehatan/allianz-preferred-medical.html',notes='Dibaca dari salinan lokal brosur/RIPLAY yang juga memiliki halaman produk publik. Rujukan versi lokal, bukan konfirmasi manfaat terkini. File tidak didistribusikan ulang.')]
passages.append(dict(key='apm-membaca',source='apm',version='Salinan lokal · versi perlu dicocokkan',locator='Halaman 1–3 · fitur, manfaat, risiko',body='Catatan baca atas RIPLAY lokal: dokumen memisahkan identitas penerbit, fitur utama, manfaat, risiko, dan ketentuan. Gunakan susunan ini sebagai checklist membaca; nominal, usia, dan manfaat terkini harus dicocokkan kembali dengan dokumen resmi. Ini parafrasa editorial, bukan kutipan atau jaminan produk.'))
entries.append(dict(slug='studi-baca-riplay-apm',kind='guide',title='Studi baca: bagian-bagian RIPLAY kesehatan',summary='Latihan menelusuri struktur dokumen Allianz Preferred Medical tanpa menyimpulkan rekomendasi produk.',body='Buka sumber resmi, lalu cari identitas penerbit, fitur, manfaat, risiko, biaya, dan ketentuan. Studi kasus ini membantu membaca dokumen. Versi lokal belum dikonfirmasi sebagai versi terkini; tidak ada rekomendasi pembelian atau jaminan manfaat.',steps=['Cocokkan nama entitas penerbit dengan dokumen yang Anda baca.','Tandai bagian manfaat dan baca bersama bagian risiko serta ketentuan.','Periksa versi dan gunakan dokumen resmi terbaru untuk konfirmasi.'],keywords=['riplay','kesehatan','allianz'],terms=['riplay','asuransi-kesehatan','manfaat'],passage='apm-membaca'))
data=dict(nodes=nodes,edges=edges,entries=entries,sources=sources,passages=passages)
target=ROOT/'backend/database/data/knowledge.json'
target.parent.mkdir(parents=True,exist_ok=True)
target.write_text(json.dumps(data,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
print(f'{len(nodes)} concepts, {len(edges)} reading relations, {len(entries)} entries, {len(passages)} passages')
