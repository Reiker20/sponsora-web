<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin & Kategori
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
        ]);

        // 2. Akun Demo Organizer
        $organizerUser = User::updateOrCreate(
            ['email' => 'organizer@sponsora.com'],
            [
                'name' => 'Aditya Pratama (Ketua)',
                'password' => Hash::make('Password123!'),
                'role' => 'organizer',
                'status_verifikasi' => 'Disetujui',
            ]
        );

        $organizerProfile = OrganizerProfile::updateOrCreate(
            ['user_id' => $organizerUser->id],
            [
                'nama_organisasi' => 'Komunitas Aksi Muda',
                'jenis' => 'Komunitas',
                'kontak' => '081234567890',
                'alamat' => 'Jl. Pemuda Kreatif No. 12, Surabaya',
                'deskripsi' => 'Komunitas pemuda penggerak program sosial, kemanusiaan, dan pelestarian lingkungan hidup.',
            ]
        );

        // 3. Akun Demo Sponsor
        $sponsorUser = User::updateOrCreate(
            ['email' => 'sponsor@sponsora.com'],
            [
                'name' => 'Rina Salsabila (CSR Manager)',
                'password' => Hash::make('Password123!'),
                'role' => 'sponsor',
                'status_verifikasi' => 'Disetujui',
            ]
        );

        $sponsorProfile = SponsorProfile::updateOrCreate(
            ['user_id' => $sponsorUser->id],
            [
                'nama_perusahaan' => 'PT Maju Bersama Nusantara',
                'nib' => '1234567890123',
                'fokus_csr' => 'Lingkungan, Pendidikan, Kesehatan',
                'kontak' => '082198765432',
                'alamat' => 'Menara Maju Lt. 15, Jl. Jenderal Sudirman Kav. 20, Jakarta',
            ]
        );

        // 4. Data Kegiatan / Event
        $catLingkungan = Category::where('nama_kategori', 'Lingkungan')->first();
        $catPendidikan = Category::where('nama_kategori', 'Pendidikan')->first();
        $catKesehatan  = Category::where('nama_kategori', 'Kesehatan')->first();

        $event1 = Event::updateOrCreate(
            ['judul' => 'Gerakan Pesisir Bersih 2026', 'organizer_id' => $organizerProfile->id],
            [
                'category_id' => $catLingkungan->id,
                'deskripsi' => 'Aksi kolaboratif membersihkan sampah pesisir, edukasi masyarakat nelayan tentang pemilahan sampah, serta penanaman 1.000 bibit mangrove.',
                'lokasi' => 'Pesisir Pantai Kenjeran, Jawa Timur',
                'penerima_manfaat' => '1.500+ warga pesisir & nelayan',
                'tanggal_pelaksanaan' => now()->addDays(25),
                'target_dana' => 50000000,
                'benefit_sponsor' => "- Logo perusahaan pada spanduk utama, backdrop, dan seragam 300 relawan\n- Publikasi liputan media partner dan siaran pers resmi\n- Booth aktivasi program CSR di lokasi kegiatan",
                'status' => 'Terverifikasi',
            ]
        );

        $event2 = Event::updateOrCreate(
            ['judul' => 'Kelas Mimpi untuk Anak Desa', 'organizer_id' => $organizerProfile->id],
            [
                'category_id' => $catPendidikan->id,
                'deskripsi' => 'Program pelatihan literasi digital, pengadaan pojok baca anak, dan pembagian paket perlengkapan sekolah bagi siswa sekolah dasar.',
                'lokasi' => 'Desa Sukamaju, Garut, Jawa Barat',
                'penerima_manfaat' => '350 anak sekolah dasar',
                'tanggal_pelaksanaan' => now()->addDays(40),
                'target_dana' => 30000000,
                'benefit_sponsor' => "- Branding pojok baca atas nama perusahaan\n- Video dokumentasi CSR eksklusif untuk annual report perusahaan\n- Kesempatan karyawan ikut serta sebagai relawan pengajar (employee volunteering)",
                'status' => 'Terverifikasi',
            ]
        );

        $event3 = Event::updateOrCreate(
            ['judul' => 'Sehat Bersama, Tumbuh Bersama', 'organizer_id' => $organizerProfile->id],
            [
                'category_id' => $catKesehatan->id,
                'deskripsi' => 'Pemeriksaan kesehatan gratis, donor darah massal, dan penyuluhan gizi seimbang untuk pencegahan stunting pada balita.',
                'lokasi' => 'Kecamatan Babakan Madang, Bogor',
                'penerima_manfaat' => '800 warga & balita',
                'tanggal_pelaksanaan' => now()->addDays(15),
                'target_dana' => 25000000,
                'benefit_sponsor' => "- Logo di banner acara dan kartu pemeriksaan kesehatan warga\n- Ucapan terima kasih khusus dalam sambutan resmi pimpinan daerah",
                'status' => 'Terverifikasi',
            ]
        );

        // 5. Komitmen Dukungan Sponsor
        Commitment::updateOrCreate(
            ['event_id' => $event1->id, 'sponsor_id' => $sponsorProfile->id],
            [
                'nominal' => 20000000,
                'bentuk_dukungan' => 'Dukungan dana tunai untuk penyediaan peralatan kebersihan dan 1.000 bibit mangrove.',
                'catatan' => 'Mohon sertakan logo CSR perusahaan pada rompi relawan.',
                'status' => 'Diterima',
                'tanggal_pengajuan' => now()->subDays(3),
            ]
        );

        Commitment::updateOrCreate(
            ['event_id' => $event2->id, 'sponsor_id' => $sponsorProfile->id],
            [
                'nominal' => 10000000,
                'bentuk_dukungan' => 'Pengadaan 10 unit rak buku dan 500 paket buku ensiklopedia anak.',
                'catatan' => 'Tertarik untuk berkolaborasi dengan program literasi kami.',
                'status' => 'Menunggu Tanggapan',
                'tanggal_pengajuan' => now()->subDay(),
            ]
        );
    }
}
