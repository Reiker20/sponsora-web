# Task Breakdown & Sprint Backlog (TASK_BREAKDOWN.md) — Sponsora

**Nama Proyek:** Sponsora  
**Tema:** SDG 17 — Kemitraan untuk Mencapai Tujuan (*Partnerships for the Goals*)  
**Mata Kuliah:** Proyek Perangkat Lunak (PPL) — Kelompok 6  
**Kerangka Kerja:** Agile Scrum ($30\text{ User Stories}$, Total $96\text{ Story Points}$)  
**Status Dokumen:** Living Document (Dapat diperbarui mengikuti progres papan Jira)

---

## 1. Panduan Status & Pembaruan Cepat

Dokumen ini dirancang menggunakan kotak centang Markdown agar mudah diperbarui oleh tim setiap kali ada perubahan di Jira:
* `[x]` = **Done (Selesai):** Sudah tercentang/dicoret di papan Jira dan lolos *peer review*.
* `[-]` = **In Progress (Sedang Dikerjakan):** Sedang dalam tahap pengerjaan UI/Backend.
* `[ ]` = **To Do (Belum Dikerjakan / Antrean):** Menunggu giliran pengerjaan atau integrasi.

---

## 2. Ringkasan Jadwal Sprint (Sprint Roadmap)

| Sprint | Periode Pengerjaan | Fokus Modul Utama | Jumlah Story & Beban | Target Luaran (Deliverables) |
| :--- | :--- | :--- | :--- | :--- |
| **Sprint 0** *(Persiapan)* | 22 Sep – 7 Okt 2026 | Analisis kebutuhan, Use Case, ERD, Arsitektur, Mockup Figma, Setup Git & Jira | Persiapan Awal | Backlog disepakati, ERD & Mockup selesai, repositori Git & papan Jira siap dipakai. |
| **Sprint 1** *(Minggu 1)* | 8 – 14 Okt 2026 | **Modul Organizer** (`SCRUM-1` s.d. `SCRUM-10` di Jira) | $9\text{ Story}$ ($28\text{ SP}$) | Organizer dapat mendaftar, mengelola profil, CRUD proposal, mengajukan verifikasi, meninjau komitmen, menyelesaikan event, serta melihat riwayat & dashboard. |
| **Sprint 2** *(Minggu 2)* | 15 – 21 Okt 2026 | **Modul Sponsor** (`SCRUM-11` s.d. `SCRUM-18` di Jira) | $8\text{ Story}$ ($26\text{ SP}$) | Sponsor dapat mendaftar, mengelola profil perusahaan, mencari & memfilter katalog event, melihat detail, mengajukan komitmen 3 langkah, melacak progres dana, serta melihat riwayat & dashboard. |
| **Sprint 3** *(Minggu 3)* | 22 – 28 Okt 2026 | **Modul Admin & Notifikasi** (`SCRUM-19` s.d. `SCRUM-26` di Jira) | $8\text{ Story}$ ($26\text{ SP}$) | Admin dapat login, memverifikasi akun Organizer & Sponsor, memverifikasi proposal, menayangkan event ke katalog, memantau dashboard operasional, serta sistem mengirim notifikasi lintas peran. |
| **Sprint 4** *(Minggu 4)* | 29 Okt – 4 Nov 2026 | **Modul Pelaporan Program** (`SCRUM-68` s.d. `SCRUM-72` di Jira) | $5\text{ Story}$ ($16\text{ SP}$) | Organizer dapat menyusun & mengirim Laporan Keuangan dan Laporan Hasil Program; Sponsor dapat melihat & menanggapi laporan; Admin dapat memantau kepatuhan batas waktu pelaporan. |
| **Sprint 5** *(Finalisasi)* | 29 Okt – 4 Nov 2026 | Pengujian E2E (*End-to-End*), perbaikan *bug*, dokumentasi akhir, dan persiapan demo | Finalisasi QA | Prototipe web Sponsora siap didemonstrasikan secara utuh dari hulu ke hilir. |

---

## 3. Matriks Distribusi Tugas Tim Pengembang

Setiap anggota tim bertanggung jawab atas **6 User Story** (masing-masing terdiri dari 3 sub-task: **Desain UI/Blade**, **Logika Backend Laravel**, dan **Pengujian Skenario**):

| No | Nama Anggota | NIM | Peran Scrum & Teknis | Kode PBI (Dokumen Proposal) | Kode Aktual di Papan Jira | Total SP |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | **Reika Elvaretta Putri Utomo** | 102062400120 | Scrum Master & Developer | `SCRUM-1, 6, 11, 16, 21, 30` | `SCRUM-1, 7, 12, 17, 22, 72` | $20\text{ SP}$ |
| 2 | **Mas Moh Abdulloh Al Wahabi** | 102062400128 | Product Owner & Developer | `SCRUM-5, 10, 15, 20, 25, 27` | `SCRUM-6, 11, 16, 21, 26, 69` | $18\text{ SP}$ |
| 3 | **Ahfiya Zukhrotunnabila** | 102062400135 | Developer | `SCRUM-3, 8, 13, 18, 23, 28` | `SCRUM-3, 9, 14, 19, 24, 70` | $18\text{ SP}$ |
| 4 | **Agus Permana** | 103062400132 | Developer | `SCRUM-4, 9, 14, 19, 24, 29` | `SCRUM-5, 10, 15, 20, 25, 71` | $21\text{ SP}$ |
| 5 | **Ilham Hilmi Widianto** | 102062400114 | Developer | `SCRUM-2, 7, 12, 17, 22, 26` | `SCRUM-2, 8, 13, 18, 23, 68` | $19\text{ SP}$ |

> **Catatan Sinkronisasi Penomoran Jira vs Proposal:**  
> Pada papan Jira aktual, kode `SCRUM-4` terlewat/dihapus saat inisialisasi sehingga tugas ke-4 hingga ke-25 bergeser $+1$ menjadi `SCRUM-5` s.d. `SCRUM-26`. Sementara itu, 5 fitur tambahan pada Modul Pelaporan tercatat di Jira dengan kode `SCRUM-68` s.d. `SCRUM-72` (yang merepresentasikan `SCRUM-26` s.d. `SCRUM-30` pada tabel proposal). Kedua kode dicantumkan di bawah ini agar tim mudah melakukan kroscek.

---

## 4. Tabel Referensi Cepat Status Papan Jira Saat Ini

| Kode Jira | Kode Proposal | Nama Backlog / Fitur | Peran Aktor | PIC (Assignee) | Status Jira Saat Ini |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **SCRUM-1** | SCRUM-1 | Registrasi Organizer | Organizer | Reika Elvaretta | `[x]` Done |
| **SCRUM-2** | SCRUM-2 | Mengelola Profil Organizer | Organizer | Ilham Hilmi | `[-]` In Progress / Open |
| **SCRUM-3** | SCRUM-3 | Membuat Proposal Event | Organizer | Ahfiya Z. | `[x]` Done |
| **SCRUM-5** | SCRUM-4 | Mengubah / Mengedit Proposal | Organizer | Agus Permana | `[x]` Done |
| **SCRUM-6** | SCRUM-5 | Mengajukan Verifikasi Proposal | Organizer | Abdulloh Al Wahabi | `[x]` Done |
| **SCRUM-7** | SCRUM-6 | Meninjau Komitmen Dana | Organizer | Reika Elvaretta | `[x]` Done |
| **SCRUM-8** | SCRUM-7 | Menyelesaikan Event | Organizer | Ilham Hilmi | `[ ]` To Do |
| **SCRUM-9** | SCRUM-8 | Melihat Riwayat Kemitraan (Organizer) | Organizer | Ahfiya Z. | `[x]` Done |
| **SCRUM-10** | SCRUM-9 | Melihat Dashboard Organizer | Organizer | Agus Permana | `[x]` Done |
| **SCRUM-11** | SCRUM-10 | Registrasi Sponsor | Sponsor | Abdulloh Al Wahabi | `[x]` Done |
| **SCRUM-12** | SCRUM-11 | Mengelola Profil Perusahaan | Sponsor | Reika Elvaretta | `[ ]` To Do |
| **SCRUM-13** | SCRUM-12 | Mencari Event (Katalog & Filter) | Sponsor | Ilham Hilmi | `[ ]` To Do |
| **SCRUM-14** | SCRUM-13 | Melihat Detail Event | Sponsor | Ahfiya Z. | `[x]` Done |
| **SCRUM-15** | SCRUM-14 | Mengajukan Komitmen Dana | Sponsor | Agus Permana | `[x]` Done |
| **SCRUM-16** | SCRUM-15 | Melacak Progres Dana | Sponsor | Abdulloh Al Wahabi | `[ ]` To Do |
| **SCRUM-17** | SCRUM-16 | Melihat Riwayat Kemitraan (Sponsor) | Sponsor | Reika Elvaretta | `[x]` Done |
| **SCRUM-18** | SCRUM-17 | Melihat Dashboard Sponsor | Sponsor | Ilham Hilmi | `[x]` Done |
| **SCRUM-19** | SCRUM-18 | Login Admin | Admin | Ahfiya Z. | `[x]` Done |
| **SCRUM-20** | SCRUM-19 | Memverifikasi Akun Organizer | Admin | Agus Permana | `[x]` Done |
| **SCRUM-21** | SCRUM-20 | Memverifikasi Akun Sponsor | Admin | Abdulloh Al Wahabi | `[x]` Done |
| **SCRUM-22** | SCRUM-21 | Memverifikasi Proposal | Admin | Reika Elvaretta | `[x]` Done |
| **SCRUM-23** | SCRUM-22 | Mempublikasikan Event ke Katalog | Admin | Ilham Hilmi | `[x]` Done |
| **SCRUM-24** | SCRUM-23 | Melihat Dashboard Admin | Admin | Ahfiya Z. | `[x]` Done |
| **SCRUM-25** | SCRUM-24 | Memantau Seluruh Aktivitas Platform | Admin | Agus Permana | `[ ]` To Do |
| **SCRUM-26** | SCRUM-25 | Menerima Notifikasi Status | Semua Peran | Abdulloh Al Wahabi | `[ ]` To Do |
| **SCRUM-68** | SCRUM-26 | Laporan Keuangan | Organizer | Ilham Hilmi | `[x]` Done |
| **SCRUM-69** | SCRUM-27 | Laporan Hasil Program | Organizer | Abdulloh Al Wahabi | `[x]` Done |
| **SCRUM-70** | SCRUM-28 | Melihat Laporan Program | Sponsor | Ahfiya Z. | `[x]` Done |
| **SCRUM-71** | SCRUM-29 | Menanggapi Laporan | Sponsor | Agus Permana | `[x]` Done |
| **SCRUM-72** | SCRUM-30 | Memantau Kepatuhan Pelaporan | Admin | Reika Elvaretta | `[ ]` To Do |

---

## 5. Rincian Sub-Task Teknis per Sprint

### SPRINT 1 — Modul Organizer (`28 SP`)

#### 1. `[x]` SCRUM-1: Registrasi & Login Organizer
* **Kebutuhan & Use Case:** `FR-ORG-01` | `UC-01`
* **PIC:** Reika Elvaretta Putri Utomo
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman `Register Organizer` & `Login` dengan tata letak *split-screen* (`#173F30` & `#FBFCF8`), tab pemilih peran, serta unggahan berkas SK Organisasi.
  - [x] **Backend:** Buat controller autentikasi Organizer, validasi input & berkas legalitas (`PDF/JPG/PNG` $\le 5\text{ MB}$), simpan ke tabel `users` (status awal `Menunggu Verifikasi`) dan `organizer_profiles`.
  - [x] **Testing:** Uji registrasi data valid, penolakan email duplikat, dan login menggunakan akun terdaftar.

#### 2. `[-]` SCRUM-2: Mengelola Profil Organizer
* **Kebutuhan & Use Case:** `FR-ORG-02` | `UC-02`
* **PIC:** Ilham Hilmi Widianto
* **Sub-Task Pengerjaan:**
  - [ ] **UI/Frontend:** Implementasi halaman `Edit Profil Organizer` (kartu identitas kiri dengan lencana verifikasi + kartu formulir kanan).
  - [ ] **Backend:** Buat endpoint `GET` dan `PUT/PATCH` untuk memperbarui `nama_organisasi`, `jenis`, `kontak`, `deskripsi`, dan `email` pada `organizer_profiles` & `users`.
  - [ ] **Testing:** Uji perubahan data profil dan pastikan perubahan langsung tampil akurat pada halaman detail event.

#### 3. `[x]` SCRUM-3: Membuat Proposal Event
* **Kebutuhan & Use Case:** `FR-ORG-03` | `UC-03`
* **PIC:** Ahfiya Zukhrotunnabila
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman *Workspace* `Buat Proposal` (4 tahapan form: Informasi Dasar, Target & Anggaran, Benefit Sponsor, Tinjau & Ajukan + unggah PDF proposal).
  - [x] **Backend:** Buat `EventController@store` dengan middleware pengecekan `status_verifikasi == 'Terverifikasi'`, validasi form, dan penyimpanan ke tabel `events` dengan status awal `Draft`.
  - [x] **Testing:** Uji pembuatan proposal berstatus `Draft` dan uji validasi pesan kesalahan ketika ada kolom wajib yang kosong.

#### 4. `[x]` SCRUM-5 *(Proposal: SCRUM-4)*: Mengubah / Menghapus Proposal
* **Kebutuhan & Use Case:** `FR-ORG-04` | `UC-04`
* **PIC:** Agus Permana
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Tambahkan tombol aksi `Edit` dan `Hapus` beserta modal konfirmasi pada kartu di halaman `Proposal Saya` khusus untuk proposal berstatus `Draft` atau `Perlu Revisi`.
  - [x] **Backend:** Buat `EventController@update` dan `EventController@destroy` dengan *policy* pembatasan agar proposal yang sudah `Menunggu Verifikasi` atau `Terverifikasi` tidak bisa dihapus/diubah sembarangan.
  - [x] **Testing:** Uji pembaruan isi proposal `Draft`/`Perlu Revisi` serta uji penghapusan proposal.

#### 5. `[x]` SCRUM-6 *(Proposal: SCRUM-5)*: Mengajukan Verifikasi Proposal
* **Kebutuhan & Use Case:** `FR-ORG-05` | `UC-05`
* **PIC:** Mas Moh Abdulloh Al Wahabi
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi tombol `Ajukan Verifikasi` pada halaman `Proposal Saya` dan langkah ke-4 `Buat Proposal`, lengkap dengan 3 kotak centang persetujuan komitmen.
  - [x] **Backend:** Buat endpoint transisi status proposal dari `Draft` atau `Perlu Revisi` menjadi `Menunggu Verifikasi` dan kunci penyuntingan form.
  - [x] **Testing:** Uji perubahan status proposal menjadi `Menunggu Verifikasi` dan pastikan langsung masuk ke antrean Pusat Verifikasi Admin.

#### 6. `[x]` SCRUM-7 *(Proposal: SCRUM-6)*: Meninjau Komitmen Dana
* **Kebutuhan & Use Case:** `FR-ORG-06` | `UC-06`
* **PIC:** Reika Elvaretta Putri Utomo
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman *Workspace* `Komitmen Masuk` dengan tata letak *Master-Detail* (tabel daftar komitmen di kiri + panel keputusan `Terima Komitmen`, `Tolak`, dan `Negosiasikan` di kanan).
  - [x] **Backend:** Buat logika perubahan status komitmen (`Diterima`, `Ditolak`, `Negosiasi`), simpan catatan negosiasi, dan picu pembaruan kalkulasi dana terkumpul apabila komitmen `Diterima`.
  - [x] **Testing:** Uji ketiga skenario keputusan Organizer (Terima, Tolak, Negosiasi) dan pastikan progres dana hanya bertambah saat komitmen `Diterima`.

#### 7. `[ ]` SCRUM-8 *(Proposal: SCRUM-7)*: Menyelesaikan Event
* **Kebutuhan & Use Case:** `FR-ORG-07` | `UC-07`
* **PIC:** Ilham Hilmi Widianto
* **Sub-Task Pengerjaan:**
  - [ ] **UI/Frontend:** Tambahkan tombol aksi `Tandai Selesai` pada event yang telah mencapai target dana atau melewati tanggal pelaksanaan.
  - [ ] **Backend:** Buat fungsi transisi `events.status` menjadi `'Selesai'`, catat `tanggal_selesai`, tetapkan `batas_laporan` otomatis (misal $+14\text{ hari}$), dan kunci penerimaan komitmen baru.
  - [ ] **Testing:** Uji penutupan event dan pastikan tombol `Danai Event Ini` di sisi Sponsor tertutup serta menu pelaporan terbuka.

#### 8. `[x]` SCRUM-9 *(Proposal: SCRUM-8)*: Melihat Riwayat Kemitraan (Organizer)
* **Kebutuhan & Use Case:** `FR-ORG-08` | `UC-08`
* **PIC:** Ahfiya Zukhrotunnabila
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman *Workspace* `Riwayat Kemitraan` (3 kartu ringkasan metrik + filter tab + tabel 5 kolom beserta paginasi).
  - [x] **Backend:** Buat query relasi antara `events`, `commitments`, dan `sponsor_profiles` milik Organizer yang sedang login.
  - [x] **Testing:** Uji tampilan daftar riwayat sponsor dan filter berdasarkan status kemitraan.

#### 9. `[x]` SCRUM-10 *(Proposal: SCRUM-9)*: Melihat Dashboard Organizer
* **Kebutuhan & Use Case:** `FR-ORG-09` | `UC-08`
* **PIC:** Agus Permana
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman utama `Dashboard Organizer` (4 kartu KPI, grafik batang perkembangan komitmen bulanan, tabel komitmen terbaru, kartu progres aktif, dan kartu kuning `Perlu Perhatian`).
  - [x] **Backend:** Buat agregasi metrik dashboard (jumlah proposal aktif, total dana terkumpul, komitmen menunggu tanggapan, dan pengingat tenggat laporan).
  - [x] **Testing:** Uji akurasi angka ringkasan dan tautan navigasi cepat dari dashboard.

---

### SPRINT 2 — Modul Sponsor (`26 SP`)

#### 10. `[x]` SCRUM-11 *(Proposal: SCRUM-10)*: Registrasi & Login Sponsor
* **Kebutuhan & Use Case:** `FR-SPO-01` | `UC-09`
* **PIC:** Mas Moh Abdulloh Al Wahabi
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman `Register Sponsor` (form 2 kolom mencakup NIB perusahaan, Fokus CSR, dan unggahan berkas NIB/Legalitas) serta halaman `Login Sponsor`.
  - [x] **Backend:** Buat proses registrasi akun dengan `role = 'sponsor'`, simpan data perusahaan ke `sponsor_profiles`, dan simpan berkas NIB ke *storage*.
  - [x] **Testing:** Uji registrasi akun perusahaan baru dan login ke sistem.

#### 11. `[ ]` SCRUM-12 *(Proposal: SCRUM-11)*: Mengelola Profil Perusahaan
* **Kebutuhan & Use Case:** `FR-SPO-02` | `UC-10`
* **PIC:** Reika Elvaretta Putri Utomo
* **Sub-Task Pengerjaan:**
  - [ ] **UI/Frontend:** Implementasi halaman `Edit Profil Sponsor` (kartu profil perusahaan di kiri dengan lencana `Sponsor Terverifikasi` + form edit `Nama Perusahaan`, `E-mail`, `Nomor Kontak`, `Tentang Perusahaan`, dan `Fokus Dalam CSR` di kanan).
  - [ ] **Backend:** Buat controller update profil perusahaan pada tabel `sponsor_profiles` dan `users`.
  - [ ] **Testing:** Uji perubahan fokus CSR dan informasi kontak perusahaan.

#### 12. `[ ]` SCRUM-13 *(Proposal: SCRUM-12)*: Mencari & Memfilter Katalog Event
* **Kebutuhan & Use Case:** `FR-SPO-03` | `UC-11`
* **PIC:** Ilham Hilmi Widianto
* **Sub-Task Pengerjaan:**
  - [ ] **UI/Frontend:** Implementasi halaman `Jelajahi Event` (Katalog) lengkap dengan kotak pencarian kata kunci, filter dropdown kategori (`categories`), filter target dana, dan grid 3 kolom kartu event ($416\times 458\text{ px}$).
  - [ ] **Backend:** Buat query pencarian (`LIKE` judul/nama organisasi) dan filter (`category_id`, rentang `target_dana`) yang hanya menampilkan event berstatus `'Terverifikasi'`.
  - [ ] **Testing:** Uji kombinasi pencarian kata kunci dan filter kategori hingga menampilkan hasil yang relevan.

#### 13. `[x]` SCRUM-14 *(Proposal: SCRUM-13)*: Melihat Detail Event
* **Kebutuhan & Use Case:** `FR-SPO-04` | `UC-12`
* **PIC:** Ahfiya Zukhrotunnabila
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman `Detail Event` (banner utama, lencana organizer terverifikasi, 3 kartu info pelaksanaan/penerima manfaat/lokasi, deskripsi lengkap, serta *sticky card* progres dana di kanan).
  - [x] **Backend:** Ambil detail `Event` beserta profil Organizer, hitung persentase progres dana dan sisa hari pelaksanaan, serta bedakan tampilan kartu kanan untuk peran Sponsor vs Organizer.
  - [x] **Testing:** Uji tampilan detail event baik menggunakan akun Sponsor maupun akun Organizer.

#### 14. `[x]` SCRUM-15 *(Proposal: SCRUM-14)*: Mengajukan Komitmen Dana
* **Kebutuhan & Use Case:** `FR-SPO-05` | `UC-13`
* **PIC:** Agus Permana
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi modal `Popup Pendanaan 3 Langkah` (`1. Nominal`, `2. Konfirmasi`, `3. Selesai`) beserta tampilan *sticky card* setelah komitmen diajukan ("Komitmen Berhasil diajukan").
  - [x] **Backend:** Buat `CommitmentController@store` (khusus Sponsor berstatus `Terverifikasi`), validasi nominal $> 0$, simpan ke tabel `commitments` dengan status `'Menunggu Tanggapan'`, dan dukung pengajuan ulang jika berstatus `'Negosiasi'`.
  - [x] **Testing:** Uji alur 3 langkah pengajuan komitmen dan uji penolakan apabila nominal kosong/tidak valid.

#### 15. `[ ]` SCRUM-16 *(Proposal: SCRUM-15)*: Melacak Progres Dana
* **Kebutuhan & Use Case:** `FR-SPO-06` | `UC-14`
* **PIC:** Mas Moh Abdulloh Al Wahabi
* **Sub-Task Pengerjaan:**
  - [ ] **UI/Frontend:** Integrasikan komponen visual *progress bar* (`#BCD63E`), persentase capaian, jumlah mitra sponsor, dan nominal terkumpul pada kartu katalog, halaman detail event, serta riwayat kemitraan Sponsor.
  - [ ] **Backend:** Buat *accessor* / metode `hitungProgresDana()` pada model `Event` yang menjumlahkan `SUM(nominal)` dari `commitments` berstatus `'Diterima'` secara *real-time*.
  - [ ] **Testing:** Uji penerimaan komitmen baru oleh Organizer dan pastikan *progress bar* di sisi Sponsor langsung bertambah secara otomatis.

#### 16. `[x]` SCRUM-17 *(Proposal: SCRUM-16)*: Melihat Riwayat Kemitraan (Sponsor)
* **Kebutuhan & Use Case:** `FR-SPO-07` | `UC-15`
* **PIC:** Reika Elvaretta Putri Utomo
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi tampilan daftar riwayat kontribusi CSR yang pernah diajukan/didanai oleh perusahaan beserta lencana statusnya.
  - [x] **Backend:** Buat endpoint daftar seluruh komitmen milik `sponsor_id` terkait beserta relasi ke `events` dan status laporannya.
  - [x] **Testing:** Uji navigasi dari riwayat kemitraan menuju detail event dan halaman laporan program.

#### 17. `[x]` SCRUM-18 *(Proposal: SCRUM-17)*: Melihat Dashboard Sponsor
* **Kebutuhan & Use Case:** `FR-SPO-08` | `UC-15`
* **PIC:** Ilham Hilmi Widianto
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman `Dashboard Sponsor` (4 kartu KPI: `Komitmen Diajukan`, `Komitmen Diterima`, `Total Dukungan`, `Event Berjalan` + tabel `Komitmen Terbaru` dengan lencana `Disetujui` / `Ditinjau`).
  - [x] **Backend:** Buat agregasi statistik komitmen perusahaan yang sedang login.
  - [x] **Testing:** Uji kesesuaian angka kartu KPI dengan data komitmen aktual di database.

---

### SPRINT 3 — Modul Admin & Notifikasi Lintas Peran (`26 SP`)

#### 18. `[x]` SCRUM-19 *(Proposal: SCRUM-18)*: Login Admin
* **Kebutuhan & Use Case:** `FR-ADM-01` | `UC-16`
* **PIC:** Ahfiya Zukhrotunnabila
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman login pengelola dan proteksi rute menuju ruang kerja Admin.
  - [x] **Backend:** Buat *database seeder* untuk akun Admin utama (`role = 'admin'`) beserta middleware otorisasi khusus Admin.
  - [x] **Testing:** Uji login Admin dan pastikan akun Organizer/Sponsor ditolak jika mencoba mengakses URL `/admin/*`.

#### 19. `[x]` SCRUM-20 *(Proposal: SCRUM-19)*: Memverifikasi Akun Organizer
* **Kebutuhan & Use Case:** `FR-ADM-02` | `UC-17`
* **PIC:** Agus Permana
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi tab `Organizer` pada halaman `Pusat Verifikasi` Admin beserta modal/halaman tinjauan identitas organisasi dan pratinjau berkas SK Organisasi.
  - [x] **Backend:** Buat aksi `Setujui` (`users.status_verifikasi = 'Terverifikasi'`) dan `Tolak` (`'Ditolak'`) untuk akun Organizer.
  - [x] **Testing:** Uji persetujuan akun Organizer baru hingga akun tersebut diizinkan membuat proposal.

#### 20. `[x]` SCRUM-21 *(Proposal: SCRUM-20)*: Memverifikasi Akun Sponsor
* **Kebutuhan & Use Case:** `FR-ADM-03` | `UC-17`
* **PIC:** Mas Moh Abdulloh Al Wahabi
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi tab `Sponsor` pada halaman `Pusat Verifikasi` Admin beserta tinjauan nomor NIB dan berkas legalitas perusahaan.
  - [x] **Backend:** Buat aksi `Setujui` (`users.status_verifikasi = 'Terverifikasi'`) dan `Tolak` (`'Ditolak'`) untuk akun Sponsor.
  - [x] **Testing:** Uji verifikasi akun Sponsor hingga perusahaan tersebut dapat menekan tombol `Danai Event Ini`.

#### 21. `[x]` SCRUM-22 *(Proposal: SCRUM-21)*: Memverifikasi Proposal Event
* **Kebutuhan & Use Case:** `FR-ADM-04` | `UC-18`
* **PIC:** Reika Elvaretta Putri Utomo
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi tab `Proposal` pada `Pusat Verifikasi` Admin, halaman detail pemeriksaan proposal, tombol `Setujui`, serta tombol `Tolak` dengan input wajib `Catatan Perbaikan`.
  - [x] **Backend:** Simpan log ke tabel `event_verifications` (`admin_id`, `event_id`, `status`, `catatan`, `tanggal_verifikasi`) dan ubah `events.status` menjadi `'Terverifikasi'` (jika disetujui) atau `'Perlu Revisi'` (jika ditolak).
  - [x] **Testing:** Uji skenario persetujuan proposal dan skenario penolakan dengan catatan revisi.

#### 22. `[x]` SCRUM-23 *(Proposal: SCRUM-22)*: Mempublikasikan Event ke Katalog
* **Kebutuhan & Use Case:** `FR-ADM-05` | `UC-19`
* **PIC:** Ilham Hilmi Widianto
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Pastikan event yang baru disetujui Admin otomatis tampil pada seksi katalog di Landing Page dan halaman `Jelajahi Event`.
  - [x] **Backend:** Hubungkan transisi status `'Terverifikasi'` pada verifikasi proposal dengan query publikasi katalog secara otomatis.
  - [x] **Testing:** Uji persetujuan satu proposal dari akun Admin lalu periksa kemunculannya di katalog Sponsor.

#### 23. `[x]` SCRUM-24 *(Proposal: SCRUM-23)*: Melihat Dashboard Admin
* **Kebutuhan & Use Case:** `FR-ADM-06` | `UC-20`
* **PIC:** Ahfiya Zukhrotunnabila
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi dashboard operasional Admin yang menampilkan kartu ringkasan jumlah antrean verifikasi akun, antrean proposal, dan komitmen berjalan.
  - [x] **Backend:** Buat query agregasi antrean moderasi dan statistik platform untuk Admin.
  - [x] **Testing:** Uji akurasi angka antrean verifikasi secara *real-time*.

#### 24. `[ ]` SCRUM-25 *(Proposal: SCRUM-24)*: Memantau Seluruh Aktivitas Platform
* **Kebutuhan & Use Case:** `FR-ADM-07` | `UC-20`
* **PIC:** Agus Permana
* **Sub-Task Pengerjaan:**
  - [ ] **UI/Frontend:** Implementasi menu `Monitoring Platform` / tab `Dana` pada ruang kerja Admin untuk melihat seluruh daftar event, progres dana, dan seluruh transaksi komitmen yang terjadi.
  - [ ] **Backend:** Buat endpoint daftar seluruh event dan komitmen lintas pengguna dengan fitur pencarian/filter bagi Admin.
  - [ ] **Testing:** Uji penelusuran detail event dan transaksi komitmen dari panel monitoring Admin.

#### 25. `[ ]` SCRUM-26 *(Proposal: SCRUM-25)*: Menerima Notifikasi Status (Lintas Peran)
* **Kebutuhan & Use Case:** `FR-ALL-01` | `UC-26`
* **PIC:** Mas Moh Abdulloh Al Wahabi
* **Sub-Task Pengerjaan:**
  - [ ] **UI/Frontend:** Implementasi ikon lonceng notifikasi beserta lencana jumlah belum dibaca (`status_baca == false`) di Topbar/Sidebar dan daftar pesan notifikasi.
  - [ ] **Backend:** Buat *helper* / *service* `Notifikasi::kirim($userId, $pesan)` yang dipanggil otomatis saat akun diverifikasi, proposal disetujui/ditolak, komitmen masuk/diterima/ditolak/dinegosiasi, serta laporan dikirim/ditanggapi. Sediakan fitur `tandaiDibaca()`.
  - [ ] **Testing:** Uji pengiriman dan pembacaan notifikasi pada akun Organizer, Sponsor, dan Admin.

---

### SPRINT 4 — Modul Pelaporan Pertanggungjawaban (`16 SP`)

#### 26. `[x]` SCRUM-68 *(Proposal: SCRUM-26)*: Menyusun Laporan Keuangan
* **Kebutuhan & Use Case:** `FR-ORG-10` | `UC-21`
* **PIC:** Ilham Hilmi Widianto
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman *Workspace* `Laporan Keuangan` (dropdown pemilih event berstatus `Selesai`, kartu tenggat laporan, tabel dinamis input `Pos Pengeluaran` + `Nominal Aktual` + unggah `Berkas Bukti`, kalkulasi otomatis `Total Pengeluaran` & `Sisa Dana`, tombol `Simpan Draft` & `Kirim ke Sponsor`, serta panel kanan `Status Pelaporan`).
  - [x] **Backend:** Buat controller penyimpanan `reports` (`jenis = 'keuangan'`) dan `report_items`, validasi berkas bukti (`PDF/JPG/PNG` $\le 5\text{ MB}$), hitung sisa dana otomatis ($\text{Dana Terkumpul} - \text{Total Pengeluaran}$), ubah status laporan menjadi `'Dikirim'`, dan kirim notifikasi ke Sponsor pendana.
  - [x] **Testing:** Uji simpan draft, kirim laporan keuangan ke Sponsor, serta uji penolakan berkas bukti yang melebihi $5\text{ MB}$.

#### 27. `[x]` SCRUM-69 *(Proposal: SCRUM-27)*: Melaporkan Hasil Program
* **Kebutuhan & Use Case:** `FR-ORG-11` | `UC-22`
* **PIC:** Mas Moh Abdulloh Al Wahabi
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman *Workspace* `Laporan Hasil Program` (dropdown pemilih event, input `Ringkasan pelaksanaan`, tabel `Capaian Program` [Indikator, Target, Realisasi], input `Evaluasi dan kendala`, unggahan foto dokumentasi & berkas laporan, serta panel kanan `Tinjauan Sponsor`).
  - [x] **Backend:** Buat controller penyimpanan `reports` (`jenis = 'hasil_program'`), dukung penyimpanan `Draft`, pengiriman ke Sponsor (`status = 'Dikirim'`), perbaikan ulang saat berstatus `'Perlu Revisi'`, dan ubah `events.status` menjadi `'Dilaporkan'` apabila kedua laporan telah dikirim.
  - [x] **Testing:** Uji pengiriman laporan hasil program dan alur perbaikan ketika diminta revisi oleh Sponsor.

#### 28. `[x]` SCRUM-70 *(Proposal: SCRUM-28)*: Melihat Laporan Program (Sisi Sponsor)
* **Kebutuhan & Use Case:** `FR-SPO-09` | `UC-23`
* **PIC:** Ahfiya Zukhrotunnabila
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Implementasi halaman peninjauan laporan bagi Sponsor (menampilkan rincian Laporan Keuangan beserta tautan unduh/lihat bukti pengeluaran dan Laporan Hasil Program beserta dokumentasinya).
  - [x] **Backend:** Buat endpoint pembacaan dan pengunduhan berkas laporan dengan proteksi akses (`NFR-SPO-04`) agar hanya dapat dibuka oleh Organizer pemilik event, Sponsor yang komitmennya berstatus `'Diterima'` pada event tersebut, dan Admin.
  - [x] **Testing:** Uji akses halaman laporan menggunakan akun Sponsor pendana vs akun Sponsor lain yang tidak mendanai event tersebut (pastikan ditolak `403 Forbidden`).

#### 29. `[x]` SCRUM-71 *(Proposal: SCRUM-29)*: Menanggapi Laporan (Sisi Sponsor)
* **Kebutuhan & Use Case:** `FR-SPO-10` | `UC-24`
* **PIC:** Agus Permana
* **Sub-Task Pengerjaan:**
  - [x] **UI/Frontend:** Tambahkan panel keputusan tanggapan pada halaman laporan di sisi Sponsor: pilihan tombol `Terima Laporan` atau `Minta Revisi` disertai textarea wajib `Catatan Revisi`.
  - [x] **Backend:** Simpan tanggapan ke tabel `report_reviews` (`report_id`, `sponsor_id`, `status`, `catatan`, `tanggal_tanggapan`). Jika ada yang memilih `Minta Revisi`, ubah `reports.status` menjadi `'Perlu Revisi'`; jika seluruh Sponsor pendana pada event tersebut telah menyetujui, ubah `reports.status` menjadi `'Diterima'`. Kirim notifikasi ke Organizer.
  - [x] **Testing:** Uji skenario penerimaan laporan dan skenario permintaan revisi beserta pengiriman ulang oleh Organizer.

#### 30. `[ ]` SCRUM-72 *(Proposal: SCRUM-30)*: Memantau Kepatuhan Pelaporan (Sisi Admin)
* **Kebutuhan & Use Case:** `FR-ADM-08` | `UC-25`
* **PIC:** Reika Elvaretta Putri Utomo
* **Sub-Task Pengerjaan:**
  - [ ] **UI/Frontend:** Tambahkan tab `Pelaporan` pada menu `Monitoring Platform` Admin yang menampilkan daftar event berstatus `Selesai`, `tanggal_selesai`, `batas_laporan`, status Laporan Keuangan & Hasil Program, serta filter untuk event yang **Belum Melapor** atau **Terlambat Melapor**.
  - [ ] **Backend:** Buat metode `cekKeterlambatanLaporan()` yang membandingkan tanggal hari ini dengan `events.batas_laporan` pada event yang belum berstatus `'Dilaporkan'`.
  - [ ] **Testing:** Uji filter event yang melewati batas waktu pelaporan pada dashboard Admin.

---

## 6. Checklist Pengujian Integrasi Akhir (Sprint 5 — Finalisasi)

Sebelum demo akhir mata kuliah PPL, pastikan seluruh alur *End-to-End* (E2E) berikut telah tercentang:
- [ ] **Alur 1 (Onboarding & Verifikasi Dua Arah):** Organizer dan Sponsor mendaftar $\rightarrow$ Admin memverifikasi kedua akun di Pusat Verifikasi $\rightarrow$ Kedua akun berstatus `Terverifikasi`.
- [ ] **Alur 2 (Publikasi Proposal):** Organizer membuat proposal (`Draft`) $\rightarrow$ Mengajukan verifikasi $\rightarrow$ Admin menolak dengan catatan (`Perlu Revisi`) $\rightarrow$ Organizer merevisi & mengajukan ulang $\rightarrow$ Admin menyetujui (`Terverifikasi`) $\rightarrow$ Event tampil di katalog Sponsor.
- [ ] **Alur 3 (Komitmen & Transparansi Progres):** Sponsor mencari event di katalog $\rightarrow$ Mengajukan komitmen lewat modal 3 langkah (`Menunggu Tanggapan`) $\rightarrow$ Organizer menerima komitmen (`Diterima`) $\rightarrow$ *Progress bar* dana event otomatis naik secara *real-time*.
- [ ] **Alur 4 (Penyelesaian & Akuntabilitas Laporan):** Organizer menandai event `Selesai` $\rightarrow$ Menyusun & mengirim Laporan Keuangan dan Laporan Hasil Program (`Dikirim`) $\rightarrow$ Sponsor memeriksa bukti & meminta revisi (`Perlu Revisi`) $\rightarrow$ Organizer memperbaiki laporan $\rightarrow$ Sponsor menyetujui laporan (`Diterima`).