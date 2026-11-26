<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Report;
use App\Models\ReportItem;
use App\Models\ReportItemReview;
use App\Models\ReportReview;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPelaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $organizerUser;
    private OrganizerProfile $organizerProfile;
    private User $sponsorUser;
    private SponsorProfile $sponsorProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'status_verifikasi' => 'Terverifikasi',
        ]);

        $this->organizerUser = User::factory()->create([
            'role' => 'organizer',
            'status_verifikasi' => 'Terverifikasi',
        ]);
        $this->organizerProfile = OrganizerProfile::create([
            'user_id' => $this->organizerUser->id,
            'nama_organisasi' => 'BEM Vokasi',
            'jenis' => 'HIMA/UKM',
            'kontak' => '081122334455',
            'alamat' => 'Kampus 1',
        ]);

        $this->sponsorUser = User::factory()->create([
            'role' => 'sponsor',
            'status_verifikasi' => 'Terverifikasi',
        ]);
        $this->sponsorProfile = SponsorProfile::create([
            'user_id' => $this->sponsorUser->id,
            'nama_perusahaan' => 'PT Mandiri Jaya',
            'nib' => '1122334455667',
            'fokus_csr' => 'Sosial',
            'kontak' => '082233445566',
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Sosial',
        ]);
    }

    private function createFinishedEvent(string $judul, int $targetDana = 10000000, bool $pastDeadline = false): Event
    {
        $event = Event::create([
            'organizer_id' => $this->organizerProfile->id,
            'category_id' => $this->category->id,
            'judul' => $judul,
            'deskripsi' => 'Deskripsi event.',
            'lokasi' => 'Jakarta',
            'target_dana' => $targetDana,
            'benefit_sponsor' => 'Backdrop logo',
            'tanggal_pelaksanaan' => now()->subDays(15),
            'status' => 'Selesai',
            'tanggal_selesai' => now()->subDays(15),
            'batas_laporan' => $pastDeadline ? now()->subDay() : now()->addDays(5),
        ]);

        Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'nominal' => $targetDana,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Diterima',
        ]);

        return $event;
    }

    public function test_admin_can_view_pelaporan_index_and_filters(): void
    {
        $eventTerlambat = $this->createFinishedEvent('Event Terlambat Lapor', 5000000, true);
        $eventLengkap = $this->createFinishedEvent('Event Sudah Lengkap', 10000000, false);

        // Beri laporan lengkap pada event kedua
        Report::create([
            'event_id' => $eventLengkap->id,
            'jenis' => 'keuangan',
            'status' => 'Dikirim',
            'tanggal_kirim' => now(),
        ]);
        Report::create([
            'event_id' => $eventLengkap->id,
            'jenis' => 'hasil_program',
            'status' => 'Dikirim',
            'tanggal_kirim' => now(),
        ]);

        // 1. Admin melihat semua pelaporan
        $resAll = $this->actingAs($this->adminUser)->get(route('admin.pelaporan'));
        $resAll->assertOk();
        $resAll->assertSee('Event Terlambat Lapor');
        $resAll->assertSee('Event Sudah Lengkap');
        $resAll->assertSee('Inspeksi Laporan →');

        // 2. Filter belum melapor
        $resBelum = $this->actingAs($this->adminUser)->get(route('admin.pelaporan', ['filter' => 'belum']));
        $resBelum->assertOk();
        $resBelum->assertSee('Event Terlambat Lapor');
        $resBelum->assertDontSee('Event Sudah Lengkap');

        // 3. Filter terlambat
        $resTerlambat = $this->actingAs($this->adminUser)->get(route('admin.pelaporan', ['filter' => 'terlambat']));
        $resTerlambat->assertOk();
        $resTerlambat->assertSee('Event Terlambat Lapor');
        $resTerlambat->assertDontSee('Event Sudah Lengkap');
    }

    public function test_admin_can_view_pelaporan_detail_show(): void
    {
        $event = $this->createFinishedEvent('Festival Seni Pelajar', 8000000, false);

        $keuangan = Report::create([
            'event_id' => $event->id,
            'jenis' => 'keuangan',
            'status' => 'Perlu Revisi',
            'tanggal_kirim' => now(),
        ]);
        $item = ReportItem::create([
            'report_id' => $keuangan->id,
            'uraian' => 'Sewa Panggung & Lighting',
            'nominal' => 4500000,
            'bukti_berkas' => 'bukti/panggung.jpg',
        ]);
        ReportItemReview::create([
            'report_item_id' => $item->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'status' => 'Kurang Bukti',
            'catatan' => 'Kwitansi bermaterai belum terlampir',
        ]);
        ReportReview::create([
            'report_id' => $keuangan->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'status' => 'Perlu Revisi',
            'catatan' => 'Mohon perbaiki kwitansi panggung',
            'tanggal_tanggapan' => now(),
        ]);

        $hasil = Report::create([
            'event_id' => $event->id,
            'jenis' => 'hasil_program',
            'ringkasan' => 'Festival sukses dihadiri 500 pelajar.',
            'capaian_hasil' => '100% target peserta tercapai.',
            'kendala' => 'Hujan sempat menunda acara 30 menit.',
            'berkas_dokumentasi' => 'dokumentasi/foto_festival.pdf',
            'status' => 'Dikirim',
            'tanggal_kirim' => now(),
        ]);

        // Simpan file palsu ke Storage
        Storage::disk('local')->put('bukti/panggung.jpg', 'fake image content');
        Storage::disk('local')->put('dokumentasi/foto_festival.pdf', 'fake pdf content');

        // Admin buka halaman inspeksi laporan
        $res = $this->actingAs($this->adminUser)->get(route('admin.pelaporan.show', $event));
        $res->assertOk();
        $res->assertSee('Festival Seni Pelajar');
        $res->assertSee('BEM Vokasi');
        $res->assertSee('Sewa Panggung & Lighting');
        $res->assertSee('Rp 4.500.000');
        $res->assertSee('Kwitansi bermaterai belum terlampir');
        $res->assertSee('Festival sukses dihadiri 500 pelajar.');
        $res->assertSee('PT Mandiri Jaya');

        // Admin bisa membuka file bukti nota & file dokumentasi
        $buktiRes = $this->actingAs($this->adminUser)->get(route('files.show', ['jenis' => 'bukti', 'id' => $item->id]));
        $buktiRes->assertOk();

        $dokRes = $this->actingAs($this->adminUser)->get(route('files.show', ['jenis' => 'dokumentasi', 'id' => $hasil->id]));
        $dokRes->assertOk();
    }

    public function test_non_admin_cannot_access_admin_pelaporan(): void
    {
        $event = $this->createFinishedEvent('Event Uji Hak Akses', 5000000, false);

        // Guest
        $this->get(route('admin.pelaporan'))->assertRedirect(route('login'));
        $this->get(route('admin.pelaporan.show', $event))->assertRedirect(route('login'));

        // Organizer
        $this->actingAs($this->organizerUser)->get(route('admin.pelaporan'))->assertForbidden();
        $this->actingAs($this->organizerUser)->get(route('admin.pelaporan.show', $event))->assertForbidden();

        // Sponsor
        $this->actingAs($this->sponsorUser)->get(route('admin.pelaporan'))->assertForbidden();
        $this->actingAs($this->sponsorUser)->get(route('admin.pelaporan.show', $event))->assertForbidden();
    }
}
