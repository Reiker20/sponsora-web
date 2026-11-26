# Matriks Test Case End-to-End & Anti-Kecurangan: Donasi Langsung, Rekening/QRIS, Validasi Nominal & Invoice PDF

Dokumen ini mendefinisikan skenario pengujian komprehensif dari awal hingga akhir (*End-to-End*) beserta skenario *Negative & Anti-Fraud Testing* yang sengaja dirancang untuk membuktikan bahwa manipulasi nominal, *IDOR*, eskalasi hak akses, dan pemalsuan bukti tidak dapat lolos.

Seluruh 12 skenario di bawah ini terotomasi di `tests/Feature/EndToEndAndAntiFraudTest.php`.

---

## 1. Penguatan Keamanan (*Anti-Fraud Hardening*) yang Diverifikasi

1. **Isolasi Sponsor Fiktif (`nominal_tervalidasi = 0`)**:
   - Apabila Organizer memvalidasi bukti fiktif dengan `0`, Sponsor tersebut tetap mendapat Invoice `Rp 0`, namun tidak dihitung sebagai `mitra_count` dan **diblokir (`403 Forbidden`)** dari seluruh akses Laporan Keuangan, berkas nota privat (`/berkas/bukti/...`), serta fitur permintaan revisi laporan.
2. **Larangan Validasi Tanpa Bukti Transfer**:
   - Endpoint `POST /organizer/komitmen/{commitment}/validasi` menolak (`403 Forbidden`) apabila `bukti_pembayaran` belum diunggah (`null`).

---

## 2. Daftar Matriks Test Case

### A. Skenario Positif (*End-to-End Happy Path*)

| ID | Aktor | Skenario & Langkah Uji | Data Uji | Ekspektasi Hasil | Test Method PHPUnit |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-E2E-01** | Organizer → Admin → Sponsor → Organizer → Sponsor | **Alur Penuh End-to-End**: (1) Organizer buat proposal + Bank + QRIS & ajukan; (2) Admin cek Bank/QRIS & setujui; (3) Sponsor buka detail Event, transfer ke Rekening/QRIS, input nominal & unggah bukti; (4) Sebelum validasi, cek `dana_terkumpul`; (5) Organizer cek bukti & validasi nominal aktual; (6) Sponsor buka Inbox & unduh PDF Invoice; (7) Organizer tandai Selesai & kirim Laporan Keuangan → Sponsor buka laporan. | Bank `Mandiri 1400098765432`, QRIS PNG, Nominal `10.000.000`, Nota PDF | Sebelum validasi `dana_terkumpul = 0`; pasca validasi `dana_terkumpul = 10.000.000`, `mitra_count = 1`, `nomor_invoice` terbit (`INV-SPO-...`), PDF valid (`%PDF-1.4`), dan Sponsor dapat melihat laporan keuangan. | `test_e2e_full_lifecycle_from_proposal_to_invoice_pdf_and_financial_report` |
| **TC-E2E-02** | Organizer | **Variasi Metode Pembayaran Proposal**: Buat proposal QRIS saja → ganti gambar QRIS → beralih dari QRIS ke Bank (`hapus_qris=1`). | QRIS PNG v1/v2, Bank `BNI 0987654321` | Semua variasi tersimpan valid; file QRIS lama otomatis terhapus dari storage publik saat diganti atau dihapus. | `test_e2e_proposal_payment_method_variants_and_qris_replacement` |

### B. Skenario Negatif & Anti-Kecurangan (*Dirancang untuk Gagal*)

| ID | Aktor / Penyerang | Skenario Kecurangan / Input Ilegal | Data / Payload Uji | Ekspektasi Hasil (Harus Gagal) | Test Method PHPUnit |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-FRD-01** | Organizer | **Kosong Keduanya & Input Ilegal Proposal**: Submit tanpa Bank & tanpa QRIS; Bank parsial; No. Rek berisi script; QRIS file `.php` / >2 MB; hapus QRIS saat edit tanpa isi Bank. | `nomor_rekening = "<script>alert(1)</script>"`, `shell.php`, `huge.png (3MB)` | **GAGAL VALIDASI**: Muncul error `metode_pembayaran` / `nomor_rekening` / `qris_image`; data tidak tersimpan. | `test_fail_proposal_empty_both_bank_and_qris_and_invalid_inputs` |
| **TC-FRD-02** | Organizer Nakal | **Bait-and-Switch Rekening Pasca-Approve**: Setelah Admin menyetujui proposal, Organizer mencoba mengubah rekening ke rekening pribadi. | `PUT /organizer/proposal/{id}` dengan `atas_nama_rekening = "Rekening Pribadi Penipu"` | **DITOLAK (`403 Forbidden`)**: Data rekening & QRIS di database tetap tidak berubah. | `test_fraud_fail_organizer_bait_and_switch_bank_after_admin_approval` |
| **TC-FRD-03** | Sponsor Nakal | **Markup Nominal Transfer**: Sponsor transfer asli `Rp 150.000` tetapi mengklaim `Rp 50.000.000` (100% target). | `nominal = 50000000`, lalu Organizer validasi `nominal_tervalidasi = 150000` | **KECURANGAN TERBENDUNG**: Sebelum validasi progres tetap `0%` & invoice `404`; pasca validasi `dana_terkumpul = 150.000`, Inbox & PDF Invoice mencatat `Rp 150.000` + peringatan penyesuaian dari `Rp 50.000.000`. | `test_fraud_fail_sponsor_nominal_markup_corrected_by_organizer` |
| **TC-FRD-04** | Sponsor Fiktif | **Bukti Transfer Fiktif Divalidasi Rp 0**: Sponsor unggah gambar palsu klaim `Rp 20.000.000`, Organizer isi `nominal_tervalidasi = 0`. Sponsor lalu mencoba mengakses & merevisi Laporan Keuangan. | `nominal_tervalidasi = 0`, akses `sponsor.laporan.show`, `sponsor.laporan.tanggapi`, `files.show` | **KECURANGAN GAGAL**: `dana_terkumpul = 0`, `mitra_count = 0`, Invoice tercatat `Rp 0`, dan Sponsor fiktif mendapat **`403 Forbidden`** saat membuka laporan, nota privat, maupun mengirim revisi. | `test_fraud_fail_fake_transfer_validated_zero_gets_no_funding_or_report_access` |
| **TC-FRD-05** | Sponsor Peretas | **Mass Assignment / Self-Approval**: Menyuntikkan field internal lewat POST donasi agar langsung terverifikasi. | `status=Diterima`, `status_pembayaran=Terverifikasi`, `nominal_tervalidasi=99999999`, `nomor_invoice=INV-HACK-9999` | **GAGAL**: Field ilegal diabaikan; komitmen tetap `Menunggu Tanggapan`, `dana_terkumpul = 0`, akses invoice `404`. | `test_fraud_fail_sponsor_mass_assignment_self_approval_payload` |
| **TC-FRD-06** | Sponsor / Akun Belum Verif | **Donasi Tanpa Bukti, Format Berbahaya, Nominal Minus, & Duplikat**: Submit tanpa file, `.exe`, `6MB` PDF, nominal `-500000`/`0`/`5000`, akun status `Menunggu`, event `Draft`/`Selesai`, serta donasi ganda di event sama. | `malware.exe`, `nominal = -500000`, status event `Draft`/`Selesai` | **GAGAL**: Ditolak error validasi atau `403 Forbidden`; duplikasi komitmen dicegah. | `test_fail_invalid_donation_submissions_and_unauthorized_states` |
| **TC-FRD-07** | Guest / Sponsor / Admin / Org Lain | **Privilege Escalation & IDOR Validasi**: Guest, Sponsor sendiri, Admin, atau Organizer B memanggil endpoint validasi Organizer A. | `POST /organizer/komitmen/{id}/validasi` | **DITOLAK (`302` untuk Guest, `403 Forbidden` untuk Sponsor, Admin, & Org Lain)**: Status pembayaran tetap belum terverifikasi. | `test_fraud_fail_privilege_escalation_and_idor_on_validation` |
| **TC-FRD-08** | Organizer | **Replay Attack Validasi Ganda & Validasi Tanpa Bukti**: Validasi komitmen yang `bukti_pembayaran = null`, input nominal negatif/string, atau validasi ulang komitmen yang sudah terverifikasi. | `nominal_tervalidasi = -100000`, `'lima juta'`, dan replay POST kedua | **GAGAL (`403 Forbidden` / error validasi)**: Nilai & nomor invoice pertama terkunci permanen. | `test_fraud_fail_replay_double_validation_no_proof_and_negative_amount` |
| **TC-FRD-09** | Sponsor B / Org B | **Evidence Tampering & IDOR Invoice/Bukti**: Sponsor mengganti bukti pasca validasi; Sponsor B mengakses Inbox/PDF/Bukti Sponsor A; Org B mengintip bukti Org A. | Akses `sponsor.inbox.show`, `sponsor.inbox.download`, `files.show` | **DITOLAK (`403 Forbidden`)**: Berkas privat dan invoice hanya dapat diakses pihak berhak. | `test_fraud_fail_evidence_tampering_and_invoice_proof_idor` |
| **TC-FRD-10** | Sponsor / Organizer | **Stored XSS & PDF Operator Injection**: Menyuntikkan tag `<script>` dan karakter PDF `)(/JavaScript` ke catatan. | `<script>alert('inv')</script> )(/JavaScript (alert))` | **TERNETRALISIR**: Tag `<script>` ter-escape di HTML Blade (`&lt;script&gt;`), dan tanda kurung pada stream PDF ter-escape (`\(`, `\)`) dengan header valid `%PDF-1.4`. | `test_security_xss_and_pdf_injection_payloads_safely_neutralized` |

---

## 3. Cara Menjalankan Pengujian

### Otomatis (PHPUnit)
```powershell
php artisan test --filter=EndToEndAndAntiFraudTest
```
Untuk memverifikasi seluruh fitur terkait sekaligus tanpa regresi:
```powershell
php artisan test --filter="DirectDonationInvoiceTest|EndToEndAndAntiFraudTest|BuktiPembayaranTest|AjukanProposalVerifikasiTest|SponsorRiwayatTest|AdminPelaporanTest|BankAccountTest|PaymentUploadTest|PaymentVerificationTest"
```

### Manual di Browser (Lokal)
1. Jalankan `php artisan migrate` (opsional `php artisan db:seed` untuk akun demo).
2. Pastikan `php artisan storage:link` aktif (`public/storage` tersedia).
3. Uji skenario `TC-E2E-01` melalui akun Organizer (`organizer@sponsora.com`), Admin (`admin@sponsora.com`), dan Sponsor (`sponsor@sponsora.com`), lalu coba skenario `TC-FRD-01` s.d. `TC-FRD-04` untuk membuktikan sistem menolak pengosongan rekening+QRIS dan mengunci nilai Invoice sesuai hasil validasi Organizer.
