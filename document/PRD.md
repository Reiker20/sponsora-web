# Product Requirements Document (PRD) — Sponsora

**Nama Proyek:** Sponsora  
**Tema:** SDG 17 — Kemitraan untuk Mencapai Tujuan (*Partnerships for the Goals*)  
**Mata Kuliah:** Proyek Perangkat Lunak (PPL) — Kelompok 6  
**Metode Pengembangan:** Agile Scrum  
**Status Dokumen:** Final MVP Baseline  

---

## 1. Informasi Tim Pengembang

| No | Nama Lengkap | NIM | Peran Scrum & Teknis | Fokus PBI (Jira) | Beban Kerja |
| :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Reika Elvaretta Putri Utomo | 102062400120 | Scrum Master & Developer | SCRUM-1, 6, 11, 16, 21, 30 | 20 SP |
| 2 | Mas Moh Abdulloh Al Wahabi | 102062400128 | Product Owner & Developer | SCRUM-5, 10, 15, 20, 25, 27 | 18 SP |
| 3 | Ahfiya Zukhrotunnabila | 102062400135 | Developer | SCRUM-3, 8, 13, 18, 23, 28 | 18 SP |
| 4 | Agus Permana | 103062400132 | Developer | SCRUM-4, 9, 14, 19, 24, 29 | 21 SP |
| 5 | Ilham Hilmi Widianto | 102062400114 | Developer | SCRUM-2, 7, 12, 17, 22, 26 | 19 SP |

---

## 2. Ringkasan Eksekutif & Latar Belakang

### 2.1 Latar Belakang Masalah
Penyelenggara kegiatan sosial—baik organisasi kemahasiswaan (HIMA/UKM), komunitas mahasiswa, maupun organisasi non-profit (NGO/NPO)—sering terkendala pendanaan saat mengadakan kegiatan seperti donor darah, seminar edukasi, aksi bersih lingkungan, atau turnamen amal. Proses pencarian sponsor saat ini berjalan manual, terfragmentasi (melalui WhatsApp, surel, atau dokumen cetak satu per satu), tidak memiliki kepastian waktu tanggapan, dan menyulitkan pelacakan progres.

Di sisi lain, perusahaan memiliki anggaran *Corporate Social Responsibility* (CSR) yang harus disalurkan ke kegiatan yang kredibel dan sesuai dengan fokus program CSR mereka. Namun, belum tersedia platform terpusat yang mempertemukan perusahaan dengan penyelenggara kegiatan terverifikasi sekaligus menjamin transparansi pelaporan pasca-kegiatan.

### 2.2 Solusi yang Diusulkan
**Sponsora** adalah sistem informasi berbasis web yang berfungsi sebagai katalog sekaligus ruang kerja sama (*marketplace* kemitraan sponsorship) antara penyelenggara kegiatan sosial (**Organizer**) dan perusahaan pemilik program CSR (**Sponsor**), dengan pengawasan kredibilitas dua arah oleh pengelola platform (**Admin**). Sponsora menyatukan proses mulai dari verifikasi identitas, publikasi proposal, pengajuan dan negosiasi komitmen dana, pemantauan progres pendanaan secara *real-time*, hingga pelaporan pertanggungjawaban keuangan dan hasil program pasca-kegiatan.

### 2.3 Tujuan Produk
1. Mempermudah penyelenggara event sosial (HIMA, UKM, NGO, NPO) dalam mencari sponsor melalui satu platform terpusat tanpa harus menghubungi calon sponsor satu per satu secara manual.
2. Membantu perusahaan menemukan kegiatan sosial terverifikasi yang sesuai dengan bidang usaha atau fokus program CSR mereka.
3. Menciptakan proses sponsorship yang terbuka dengan informasi status pengajuan dan perkembangan dana secara *real-time* dan transparan.
4. Mendukung pencapaian **SDG 17 (Kemitraan untuk Mencapai Tujuan)** melalui kolaborasi terstruktur antara komunitas sosial dan sektor industri.
5. Menyediakan mekanisme pelaporan keuangan dan hasil program pasca-kegiatan agar sponsor dapat memantau penggunaan dana dan penyelenggara dapat mempertanggungjawabkannya secara akuntabel.

---

## 3. Ruang Lingkup & Batasan Sistem (MVP Scope)

### 3.1 Fitur Dalam Ruang Lingkup (In-Scope)
* Autentikasi dan manajemen hak akses untuk 3 peran: **Organizer**, **Sponsor**, dan **Admin**.
* Verifikasi dua arah oleh Admin terhadap akun Organizer, akun Sponsor, serta kelayakan proposal event.
* Pengelolaan siklus hidup proposal event oleh Organizer (Draft, Pengajuan Verifikasi, Revisi, Terverifikasi/Tayang, Selesai, Dilaporkan).
* Katalog event publik bagi Sponsor terverifikasi lengkap dengan fitur pencarian kata kunci serta penyaringan kategori dan target dana.
* Pengajuan komitmen dukungan oleh Sponsor serta peninjauan oleh Organizer (Terima, Tolak, atau Negosiasi).
* Kalkulasi otomatis dan visualisasi *progress bar* dana terkumpul secara *real-time*.
* Dashboard ringkasan dan riwayat kemitraan sesuai hak akses masing-masing peran.
* Sistem notifikasi status internal lintas peran.
* Penyusunan, pengiriman, peninjauan, dan revisi **Laporan Keuangan** serta **Laporan Hasil Program** pasca-kegiatan, beserta pemantauan kepatuhan pelaporan oleh Admin.

### 3.2 Batasan Sistem (Out-of-Scope)
* **Bukan Payment Gateway:** Transaksi pencairan dan transfer dana riil dilakukan di luar sistem. Sponsora hanya mencatat nominal komitmen, bentuk dukungan, dan status kesepakatan.
* **Tanpa Audit Bukti Otomatis:** Laporan keuangan memuat rincian pengeluaran beserta unggahan berkas bukti nota/kwitansi, namun sistem tidak melakukan audit forensik/keaslian bukti secara otomatis; validasi kewajaran laporan dilakukan oleh Sponsor yang mendanai event terkait.
* **Skala Prototipe Akademis:** Dikembangkan menggunakan metode Agile Scrum sebagai prototipe fungsional tingkat MVP.

---

## 4. Karakteristik Pengguna & Matriks Hak Akses (RBAC)

| Peran Pengguna | Deskripsi | Hak Akses Utama |
| :--- | :--- | :--- |
| **Organizer** | Penyelenggara kegiatan sosial dari HIMA, UKM, NGO, atau NPO yang membutuhkan dukungan dana CSR. | `INSERT`, `UPDATE`, `DELETE` (saat Draft) pada proposal event miliknya; `UPDATE` status penerimaan komitmen sponsor; `INSERT` dan `UPDATE` pada laporan keuangan serta laporan hasil program miliknya. |
| **Sponsor** | Perusahaan yang mencari kegiatan sosial untuk didanai sesuai fokus program CSR. | `SELECT` pada katalog dan detail event terverifikasi; `INSERT` dan `SELECT` pada pengajuan komitmen, profil perusahaan, dan riwayat kemitraan; `SELECT` pada laporan event yang didanai; `INSERT` pada tanggapan laporan (`Terima` / `Minta Revisi`). |
| **Admin** | Pengelola platform yang memoderasi kredibilitas pengguna, kelayakan proposal, dan kepatuhan pelaporan. | Akun dibuat secara internal (tanpa registrasi publik). *Full Control* pada verifikasi akun Organizer & Sponsor, verifikasi proposal, publikasi katalog, dan pemantauan seluruh transaksi komitmen serta kepatuhan batas waktu laporan. |

---

## 5. Aturan Bisnis & Siklus Hidup Status (State Machines)

### 5.1 Siklus Status Verifikasi Akun (`users.status_verifikasi`)
1. **Menunggu Verifikasi:** Status awal saat Organizer atau Sponsor baru menyelesaikan registrasi. Pengguna belum dapat mengajukan proposal (Organizer) atau mengajukan komitmen dana (Sponsor).
2. **Terverifikasi:** Disetujui oleh Admin setelah pemeriksaan identitas organisasi/perusahaan. Membuka akses penuh ke fitur transaksi utama.
3. **Ditolak:** Akun dinyatakan tidak valid oleh Admin.

### 5.2 Siklus Status Proposal Event (`events.status`)
1. **Draft:** Proposal baru disimpan oleh Organizer. Masih dapat diubah (`Edit`) atau dihapus (`Delete`).
2. **Menunggu Verifikasi:** Proposal diajukan Organizer ke Admin. Proposal dikunci dari perubahan.
3. **Perlu Revisi:** Proposal ditolak oleh Admin disertai catatan perbaikan. Organizer dapat mengedit kembali dan mengajukan ulang.
4. **Terverifikasi:** Proposal disetujui Admin dan otomatis dipublikasikan ke katalog Sponsor. Menerima pengajuan komitmen dana.
5. **Selesai:** Ditandai oleh Organizer setelah target dana tercapai atau kegiatan telah terlaksana. Menutup pengajuan komitmen baru, membuka kewajiban pelaporan, dan menetapkan `tanggal_selesai` serta `batas_laporan`.
6. **Dilaporkan:** Status tercapai apabila baik Laporan Keuangan maupun Laporan Hasil Program telah dikirim oleh Organizer.

### 5.3 Siklus Status Komitmen Dana (`commitments.status`)
1. **Menunggu Tanggapan:** Sponsor mengirimkan formulir komitmen dana/dukungan pada suatu event.
2. **Diterima:** Organizer menyetujui komitmen. Nominal otomatis ditambahkan ke perhitungan progres dana event.
3. **Ditolak:** Organizer menolak pengajuan komitmen. Siklus komitmen berakhir.
4. **Negosiasi:** Organizer meminta penyesuaian nominal atau syarat dukungan. Sponsor dapat mengajukan ulang komitmen dengan nilai yang diperbarui.

### 5.4 Siklus Status Laporan Pertanggungjawaban (`reports.status`)
Setiap event yang berstatus `Selesai` memiliki kewajiban hingga 2 jenis laporan (`keuangan` dan `hasil_program`):
1. **Draft:** Laporan sedang disusun oleh Organizer dan belum terlihat oleh Sponsor.
2. **Dikirim:** Laporan telah dikirim ke Sponsor yang memiliki komitmen berstatus `Diterima` pada event tersebut.
3. **Perlu Revisi:** Minimal satu Sponsor peninjau memilih opsi *Minta Revisi* beserta catatan perbaikan. Organizer wajib memperbaiki dan mengirim ulang laporan.
4. **Diterima:** Seluruh Sponsor yang mendanai event tersebut telah menyetujui laporan.

### 5.5 Rumus Perhitungan Otomatis Sistem
* **Dana Terkumpul pada Event:** Tidak disimpan sebagai kolom statis di tabel `events`, melainkan dihitung secara dinamis dari agregasi komitmen yang disetujui:
  $$\text{Dana Terkumpul} = \sum_{i=1}^{n} \text{Nominal Komitmen}_i \quad (\text{di mana } \text{status}_i = \text{'Diterima'})$$
* **Persentase Progres Pendanaan:**
  $$\text{Progres (\%)} = \min\left(100\%, \frac{\text{Dana Terkumpul}}{\text{Target Dana}} \times 100\%\right)$$
* **Kalkulasi Laporan Keuangan:**
  $$\text{Total Pengeluaran} = \sum_{j=1}^{m} \text{Nominal Item Pengeluaran}_j$$
  $$\text{Sisa Dana} = \text{Dana Terkumpul} - \text{Total Pengeluaran}$$

---

## 6. Kebutuhan Fungsional (Functional Requirements)

### 6.1 Modul Organizer (`FR-ORG`)

| ID | Nama Kebutuhan | Deskripsi Fungsional | Use Case Terkait |
| :--- | :--- | :--- | :--- |
| **FR-ORG-01** | Registrasi & Login | Organizer dapat melakukan registrasi akun baru dan login ke dalam sistem menggunakan kredensial terdaftar. | UC-01 |
| **FR-ORG-02** | Pengelolaan Profil Pengguna | Organizer dapat melihat dan memperbarui data profil organisasi/pribadinya (`nama_organisasi`, `jenis` [HIMA/UKM/NGO/NPO], `kontak`, `deskripsi`). | UC-02 |
| **FR-ORG-03** | Pembuatan Proposal Event | Organizer (yang akunnya telah terverifikasi) dapat membuat proposal event baru berisi judul kegiatan, kategori, deskripsi, tanggal pelaksanaan, target dana, dan benefit sponsor, lalu menyimpannya sebagai `Draft`. | UC-03 |
| **FR-ORG-04** | Pengubahan & Penghapusan Proposal | Selama proposal masih berstatus `Draft` atau `Perlu Revisi` (belum diverifikasi Admin), Organizer dapat mengubah isi maupun menghapus proposal event. | UC-04 |
| **FR-ORG-05** | Pengajuan Verifikasi Event | Organizer dapat mengajukan proposal event kepada Admin untuk ditinjau dan diverifikasi sebelum dipublikasikan ke katalog. | UC-05 |
| **FR-ORG-06** | Peninjauan Komitmen Dana | Organizer dapat meninjau setiap pengajuan komitmen dana yang masuk dari Sponsor dan memilih tindakan: `Diterima`, `Ditolak`, atau `Negosiasi`. | UC-06 |
| **FR-ORG-07** | Pengubahan Status Event | Organizer dapat menandai event menjadi `Selesai` setelah target dana tercapai atau kegiatan telah berlangsung. Tindakan ini menutup komitmen baru serta membuka kewajiban pelaporan beserta batas waktunya. | UC-07 |
| **FR-ORG-08** | Riwayat Kemitraan | Organizer dapat melihat daftar riwayat kerja sama sponsorship yang pernah terjalin pada event-event sebelumnya beserta detail nominal dan sponsornya. | UC-08 |
| **FR-ORG-09** | Dashboard Pemantauan | Organizer memiliki dashboard ringkasan yang menampilkan jumlah proposal aktif, status verifikasi, total dana terkumpul, dan komitmen dana yang masuk. | UC-08 |
| **FR-ORG-10** | Penyusunan Laporan Keuangan | Setelah event berstatus `Selesai`, Organizer dapat menyusun laporan keuangan berisi rincian item pengeluaran (uraian, nominal, unggahan bukti). Sistem menghitung otomatis total pengeluaran dan sisa dana. Dapat disimpan sebagai `Draft`, dikirim ke Sponsor, atau direvisi jika diminta. | UC-21 |
| **FR-ORG-11** | Pelaporan Hasil Program | Organizer dapat mengisi laporan hasil program berisi ringkasan pelaksanaan, capaian hasil, kendala, dan unggahan berkas dokumentasi kegiatan, lalu mengirimkannya ke Sponsor serta merevisinya bila diminta. | UC-22 |

### 6.2 Modul Sponsor (`FR-SPO`)

| ID | Nama Kebutuhan | Deskripsi Fungsional | Use Case Terkait |
| :--- | :--- | :--- | :--- |
| **FR-SPO-01** | Registrasi & Login | Sponsor (perusahaan) dapat melakukan registrasi akun dan login ke dalam sistem. | UC-09 |
| **FR-SPO-02** | Pengelolaan Profil | Sponsor dapat mengelola data profil perusahaannya (`nama_perusahaan`, `fokus_csr`, `kontak`, `alamat`). | UC-10 |
| **FR-SPO-03** | Pencarian & Filter Katalog Event | Sponsor dapat menjelajahi katalog event yang telah berstatus `Terverifikasi`, mencari berdasarkan kata kunci, serta memfilter berdasarkan kategori atau besaran target dana. | UC-11 |
| **FR-SPO-04** | Melihat Detail Event | Sponsor dapat melihat halaman informasi lengkap suatu event (deskripsi, tanggal, penyelenggara terverifikasi, target dana, benefit sponsor, dan *progress bar* dana terkumpul). | UC-12 |
| **FR-SPO-05** | Pengajuan Komitmen Dana | Sponsor (yang akunnya telah terverifikasi) dapat mengajukan komitmen dukungan pada event aktif dengan mengisi nominal dana serta bentuk dukungan (uang/barang/dll). | UC-13 |
| **FR-SPO-06** | Pelacakan Progres Dana | Sponsor dapat memantau progres dana yang terkumpul pada event yang mereka danai secara *real-time*. | UC-14 |
| **FR-SPO-07** | Riwayat Kemitraan | Sponsor dapat melihat daftar riwayat kontribusi sponsorship CSR yang pernah dilakukan beserta nominal dan statusnya. | UC-15 |
| **FR-SPO-08** | Dashboard Pemantauan | Sponsor memiliki dashboard utama yang menampilkan ringkasan jumlah komitmen yang diajukan, diterima, dan sedang berjalan. | UC-15 |
| **FR-SPO-09** | Melihat Laporan Program | Sponsor dapat melihat dan mengunduh Laporan Keuangan beserta bukti pengeluaran serta Laporan Hasil Program dari event yang komitmennya berstatus `Diterima`. | UC-23 |
| **FR-SPO-10** | Menanggapi Laporan | Sponsor dapat memberikan keputusan terhadap laporan yang dikirim Organizer: memilih `Terima` laporan atau `Minta Revisi` disertai catatan perbaikan wajib. | UC-24 |

### 6.3 Modul Admin (`FR-ADM`)

| ID | Nama Kebutuhan | Deskripsi Fungsional | Use Case Terkait |
| :--- | :--- | :--- | :--- |
| **FR-ADM-01** | Login Admin | Admin dapat login menggunakan akun pengelola khusus untuk mengakses pusat moderasi platform. | UC-16 |
| **FR-ADM-02** | Verifikasi Akun Organizer | Admin memeriksa kelengkapan identitas organisasi/individu Organizer baru dan memberikan keputusan setuju atau tolak sebelum diizinkan membuat proposal. | UC-17 |
| **FR-ADM-03** | Verifikasi Akun Sponsor | Admin memeriksa keabsahan data perusahaan Sponsor baru dan memberikan keputusan setuju atau tolak sebelum diizinkan mengajukan komitmen dana. | UC-17 |
| **FR-ADM-04** | Verifikasi Proposal | Admin meninjau kelengkapan data setiap proposal berstatus `Menunggu Verifikasi`, lalu menyetujuinya atau menolaknya disertai catatan perbaikan yang jelas. | UC-18 |
| **FR-ADM-05** | Publikasi Event ke Katalog | Proposal yang disetujui Admin otomatis berubah status menjadi `Terverifikasi` dan langsung ditayangkan pada katalog publik Sponsor. | UC-19 |
| **FR-ADM-06** | Dashboard Operasional | Admin memiliki dashboard operasional yang merangkum antrean verifikasi akun, antrean verifikasi proposal, dan ringkasan aktivitas komitmen aktif. | UC-20 |
| **FR-ADM-07** | Pemantauan Semua Aktivitas | Admin dapat memantau seluruh daftar event, progres pendanaan, dan transaksi komitmen dana yang berjalan di platform. | UC-20 |
| **FR-ADM-08** | Pemantauan Kepatuhan Pelaporan | Admin dapat memantau daftar event berstatus `Selesai`, menyaring event yang belum atau melewati `batas_laporan`, serta memeriksa status laporan setiap event. | UC-25 |

### 6.4 Modul Lintas Peran (`FR-ALL`)

| ID | Nama Kebutuhan | Deskripsi Fungsional | Use Case Terkait |
| :--- | :--- | :--- | :--- |
| **FR-ALL-01** | Notifikasi Status | Sistem mengirimkan notifikasi internal otomatis setiap terjadi perubahan status penting (akun diverifikasi, proposal disetujui/perlu revisi, komitmen baru masuk, komitmen diterima/ditolak/dinegosiasikan, laporan dikirim, tanggapan laporan diberikan, dan pengingat batas waktu laporan) beserta penanda status baca (`status_baca`). | UC-26 |

---

## 7. Skenario Use Case Inti & Kriteria Penerimaan (Acceptance Criteria)

### 7.1 UC-03: Membuat Proposal Event (Organizer)
* **Prakondisi:** Organizer sudah login dan `users.status_verifikasi = 'Terverifikasi'`.
* **Alur Utama:**
  1. Organizer membuka menu **Buat Proposal**.
  2. Mengisi `judul`, `category_id`, `deskripsi`, `tanggal_pelaksanaan`, `target_dana`, dan `benefit_sponsor`.
  3. Menekan tombol **Simpan Proposal**.
  4. Sistem memvalidasi kelengkapan input dan menyimpan data dengan status `Draft`.
* **Alur Alternatif:** Jika ada kolom wajib yang kosong atau format nominal tidak valid, sistem menolak penyimpanan dan menampilkan pesan kesalahan validasi.

### 7.2 UC-18: Memverifikasi Proposal (Admin)
* **Prakondisi:** Admin sudah login dan terdapat proposal berstatus `Menunggu Verifikasi`.
* **Alur Utama:**
  1. Admin membuka menu **Pusat Verifikasi > Proposal** dan memilih satu pengajuan.
  2. Sistem menampilkan detail lengkap proposal beserta profil Organizer.
  3. Jika layak, Admin menekan **Setujui**. Sistem mencatat log pada `event_verifications`, mengubah status event menjadi `Terverifikasi`, menayangkannya di katalog, dan mengirim notifikasi ke Organizer.
* **Alur Alternatif (Penolakan/Revisi):** Jika data belum lengkap, Admin menekan **Tolak** dan wajib mengisi `catatan` perbaikan. Status event berubah menjadi `Perlu Revisi` dan notifikasi beserta catatan dikirim ke Organizer.

### 7.3 UC-13 & UC-06: Pengajuan & Peninjauan Komitmen Dana (Sponsor & Organizer)
* **Prakondisi:** Sponsor sudah login, akun `Terverifikasi`, dan event berstatus `Terverifikasi` (aktif).
* **Alur Pengajuan (Sponsor):**
  1. Pada halaman detail event, Sponsor menekan **Ajukan Komitmen** / **Danai Event Ini**.
  2. Mengisi `nominal` dan `bentuk_dukungan`, lalu mengirim pengajuan.
  3. Sistem menyimpan komitmen dengan status `Menunggu Tanggapan` dan memberi notifikasi ke Organizer.
* **Alur Peninjauan (Organizer):**
  1. Organizer membuka menu **Komitmen Masuk** dan memilih salah satu tindakan:
     * **Terima:** Status komitmen menjadi `Diterima`, sistem otomatis mengakumulasi nominal ke progres dana event, dan mengirim notifikasi ke Sponsor.
     * **Tolak:** Status komitmen menjadi `Ditolak` dan Sponsor menerima notifikasi.
     * **Negosiasikan:** Status komitmen menjadi `Negosiasi`, memungkinkan Sponsor memperbarui dan mengajukan ulang nominal atau bentuk dukungan.

### 7.4 UC-21 & UC-24: Penyusunan & Tanggapan Laporan Pertanggungjawaban
* **Prakondisi:** Event berstatus `Selesai` dan memiliki komitmen berstatus `Diterima`.
* **Alur Penyusunan (Organizer):**
  1. Organizer memilih menu **Laporan Keuangan** atau **Laporan Hasil Program** pada event yang telah selesai.
  2. Untuk Laporan Keuangan, sistem menampilkan total dana diterima; Organizer menambahkan baris rincian pengeluaran (`uraian`, `nominal`, `bukti_berkas` berformat PDF/JPG/PNG $\le 5\text{ MB}$). Sistem menghitung total pengeluaran dan sisa dana secara otomatis.
  3. Organizer menekan **Kirim ke Sponsor** (atau **Simpan Draft**). Status laporan menjadi `Dikirim` dan notifikasi dikirim ke Sponsor pendana.
* **Alur Tanggapan (Sponsor):**
  1. Sponsor membuka laporan dan memeriksa rincian serta berkas bukti.
  2. Jika sesuai, Sponsor menekan **Terima Laporan**. Jika seluruh Sponsor pendana pada event tersebut telah menyetujui, status laporan menjadi `Diterima`.
  3. Jika memilih **Minta Revisi**, Sponsor wajib mengisi `catatan`. Status laporan berubah menjadi `Perlu Revisi` dan Organizer mendapat notifikasi untuk memperbaiki serta mengirim ulang laporan.

---

## 8. Kebutuhan Non-Fungsional (Non-Functional Requirements)

| ID | Kategori | Spesifikasi Kebutuhan & Standar Kualitas |
| :--- | :--- | :--- |
| **NFR-ORG-01** | Usability (Organizer) | Antarmuka pengelolaan proposal dan laporan dirancang intuitif sehingga pengurus HIMA, UKM, NGO, maupun NPO dapat mengoperasikan sistem tanpa pelatihan khusus. |
| **NFR-ORG-02** | Performance | Waktu muat (*response time*) halaman pengelolaan proposal dan dashboard maksimal **3 detik** pada koneksi jaringan standar. |
| **NFR-ORG-03** | Reliability | Setiap transaksi CRUD pada proposal dan draft laporan disimpan secara konsisten (*ACID-compliant*) untuk mencegah kehilangan data. |
| **NFR-ORG-04** | Validasi & Keamanan Unggahan | Berkas bukti pengeluaran dan dokumentasi kegiatan dibatasi hanya untuk ekstensi **PDF, JPG, atau PNG** dengan ukuran maksimal **5 MB per berkas**. Sistem wajib menolak berkas di luar ketentuan. |
| **NFR-SPO-01** | Usability (Sponsor) | Pencarian kata kunci dan filter katalog (kategori & target dana) memberikan hasil secara responsif dan mudah dinavigasi. |
| **NFR-SPO-02** | Availability | Ketersediaan sistem minimum **98%** agar Sponsor dapat meninjau katalog dan laporan di luar jam kerja. |
| **NFR-SPO-03** | Responsiveness & Compatibility | Antarmuka web responsif (*mobile & desktop friendly*) dan kompatibel di peramban modern (Chrome, Edge, Firefox, Safari, Opera). |
| **NFR-SPO-04** | Kerahasiaan Laporan (Confidentiality) | Laporan keuangan dan hasil program beserta berkas buktinya hanya dapat diakses oleh **Organizer pemilik event**, **Sponsor yang komitmennya berstatus `Diterima` pada event tersebut**, dan **Admin**. |
| **NFR-ADM-01** | Security & Authentication | Kata sandi pengguna wajib di-*hash* menggunakan algoritma **bcrypt**. Pembatasan akses rute menggunakan middleware **Role-Based Access Control (RBAC)**. |
| **NFR-ADM-02** | Maintainability & Data Integrity | Skema basis data relasional menjaga integritas referensial (*foreign key constraints*) dan mendukung histori verifikasi berulang. |
| **NFR-ADM-03** | Monitoring Capability | Dashboard Admin menyajikan metrik antrean verifikasi dan status komitmen secara *real-time* dari basis data. |
| **NFR-ADM-04** | Audit Trail & Keterlacakan | Setiap pengiriman laporan, revisi, verifikasi proposal, dan tanggapan Sponsor wajib mencatat penanda waktu (*timestamp*) dan identitas aktor terkait. |

---

## 9. Spesifikasi Teknis & Lingkungan Sistem (Technical Requirements)

| ID | Komponen | Spesifikasi Teknis |
| :--- | :--- | :--- |
| **NTR-01** | Frontend Stack | HTML5, CSS3, JavaScript, **Laravel Blade Template**, dengan kerangka kerja **Tailwind CSS** atau **Bootstrap**. |
| **NTR-02** | Backend Stack | **PHP** dengan kerangka kerja **Laravel** (Arsitektur Three-Tier MVC, Eloquent ORM, Middleware RBAC). |
| **NTR-03** | Basis Data | **MySQL** atau **PostgreSQL** ( terdiri dari 10 tabel utama: `users`, `organizer_profiles`, `sponsor_profiles`, `categories`, `events`, `event_verifications`, `commitments`, `notifications`, `reports`, `report_items`, `report_reviews`). |
| **NTR-04** | Web Server | Apache atau Nginx (mendukung protokol HTTP/HTTPS pada lingkungan lokal maupun cloud hosting). |
| **NTR-05** |Penyimpanan Berkas | **Laravel Storage** (penyimpanan server terproteksi untuk lampiran bukti laporan keuangan dan dokumentasi program). |
| **NTR-06** | Perangkat Lunak Pendukung | Visual Studio Code, Git & GitHub, Postman, Figma, dan Jira. |
| **NTR-07** | Spesifikasi Minimum Klien | Laptop/PC/Smartphone dengan peramban web versi terbaru dan koneksi internet minimal 2 Mbps. |

---

## 10. Kriteria Selesai (Definition of Done — DoD)

Sebuah User Story / Product Backlog Item (PBI) pada proyek Sponsora dinyatakan **Done (Selesai)** apabila memenuhi seluruh kriteria berikut:
1. **Kesesuaian Antarmuka:** Tampilan halaman telah diimplementasikan secara responsif sesuai acuan mockup Figma Sponsora.
2. **Kesesuaian Logika Bisnis:** Fungsi backend, validasi form, dan perubahan status berjalan sesuai skenario Use Case pada Bab 3.4 dan Bab 4.1.1.
3. **Kualitas & Pengujian:** Seluruh alur utama (*happy path*) dan alur alternatif (*error handling*) telah diuji tanpa menimbulkan *bug* penghambat (*blocker*).
4. **Peer Review:** Kode sumber telah di-*push* ke repositori Git dan ditinjau (*code review*) oleh minimal satu rekan anggota tim pengembang lainnya.