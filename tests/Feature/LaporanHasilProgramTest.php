<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Report;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaporanHasilProgramTest extends TestCase
{
    use RefreshDatabase;

    private const RINGKASAN = 'Kegiatan berlangsung lancar dengan 120 peserta dari 15 sekolah.';
    private const CAPAIAN = 'Seluruh target tercapai: 120 siswa mengikuti pelatihan dan 90 persen lulus asesmen akhir.';

    private User $organizerUser;
    private OrganizerProfile $organizerProfile;
    private User $sponsorUser;
    private SponsorProfile $sponsorProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->organizerUser = User::factory()->create(['role' => 'organizer', 'status_verifikasi' => 'Terverifikasi']);
        $this->organizerProfile = OrganizerProfile::create([
            'user_id' => $this->organizerUser->id,
            'nama_organisasi' => 'BEM Kampus Harapan',
            'jenis' => 'HIMA/UKM',
            'kontak' => '08123456789',
            'alamat' => 'Kampus Terpadu',
        ]);

        $this->sponsorUser = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Terverifikasi']);
        $this->sponsorProfile = SponsorProfile::create([
            'user_id' => $this->sponsorUser->id,
            'nama_perusahaan' => 'PT Nusantara Sejahtera',
            'nib' => '1234567890123',
            'fokus_csr' => 'Pendidikan',
            'kontak' => '08129876543',
        ]);

        $this->category = Category::create(['nama_kategori' => 'Pendidikan']);
    }

    private function buatEvent(string $status = 'Selesai'): Event
    {
        $event = Event::create([
            'organizer_id' => $this->organizerProfile->id,
            'category_id' => $this->category->id,
            'judul' => 'Pelatihan Coding untuk Pelajar',
            'deskripsi' => 'Pelatihan web dasar untuk siswa SMK.',
            'lokasi' => 'Surabaya',
            'target_dana' => 10000000,
            'benefit_sponsor' => 'Logo pada backdrop',
            'tanggal_pelaksanaan' => now()->subDays(5),
            'status' => $status,
        ]);

        Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'nominal' => 10000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Diterima',
        ]);

        return $event;
    }

    private function kirimHasil(Event $event, array $override = [], ?User $user = null)
    {
        return $this->actingAs($user ?? $this->organizerUser)->post(route('organizer.laporan.hasil', $event), array_merge([
            'aksi' => 'kirim',
            'ringkasan' => self::RINGKASAN,
            'capaian_hasil' => self::CAPAIAN,
        ], $override));
    }

    private function laporanHasil(Event $event): Report
    {
        return Report::where('event_id', $event->id)->where('jenis', 'hasil_program')->firstOrFail();
    }

    public function test_organizer_dapat_menyimpan_draft_dengan_dokumentasi(): void
    {
        $event = $this->buatEvent();

        $this->actingAs($this->organizerUser)->post(route('organizer.laporan.hasil', $event), [
            'aksi' => 'draft',
            'ringkasan' => 'Ringkasan awal',
            'dokumentasi' => UploadedFile::fake()->create('dokumentasi.pdf', 300, 'application/pdf'),
        ])->assertSessionHas('success');

        $laporan = $this->laporanHasil($event);
        $this->assertSame('Draft', $laporan->status);
        $this->assertSame('Ringkasan awal', $laporan->ringkasan);
        Storage::disk('local')->assertExists($laporan->berkas_dokumentasi);
        $this->assertDatabaseMissing('notifications', ['user_id' => $this->sponsorUser->id]);
    }

    public function test_draft_tidak_mewajibkan_isian(): void
    {
        $event = $this->buatEvent();

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.laporan.hasil', $event), ['aksi' => 'draft'])
            ->assertSessionHas('success');
    }

    public function test_organizer_dapat_mengirim_laporan_hasil_ke_sponsor(): void
    {
        $event = $this->buatEvent();

        $this->kirimHasil($event)->assertSessionHas('success');

        $laporan = $this->laporanHasil($event);
        $this->assertSame('Dikirim', $laporan->status);
        $this->assertNotNull($laporan->tanggal_kirim);
        $this->assertDatabaseHas('notifications', ['user_id' => $this->sponsorUser->id]);
    }

    public function test_kirim_wajib_ringkasan_dan_capaian_yang_memadai(): void
    {
        $event = $this->buatEvent();

        $this->kirimHasil($event, ['ringkasan' => '', 'capaian_hasil' => ''])
            ->assertSessionHasErrors(['ringkasan', 'capaian_hasil']);

        $this->kirimHasil($event, ['ringkasan' => 'Singkat', 'capaian_hasil' => 'Singkat'])
            ->assertSessionHasErrors(['ringkasan', 'capaian_hasil']);

        $this->assertSame('Draft', $this->laporanHasil($event)->status);
    }

    public function test_dokumentasi_harus_berformat_dan_berukuran_valid(): void
    {
        $event = $this->buatEvent();

        $this->kirimHasil($event, ['dokumentasi' => UploadedFile::fake()->create('catatan.txt', 10, 'text/plain')])
            ->assertSessionHasErrors('dokumentasi');

        $this->kirimHasil($event, ['dokumentasi' => UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf')])
            ->assertSessionHasErrors('dokumentasi');

        $this->assertSame('Draft', $this->laporanHasil($event)->status);
    }

    public function test_mengganti_dokumentasi_menghapus_berkas_lama(): void
    {
        $event = $this->buatEvent();
        $draft = ['aksi' => 'draft'];

        $this->actingAs($this->organizerUser)->post(route('organizer.laporan.hasil', $event), $draft + [
            'dokumentasi' => UploadedFile::fake()->create('lama.pdf', 100, 'application/pdf'),
        ]);
        $lama = $this->laporanHasil($event)->berkas_dokumentasi;

        $this->actingAs($this->organizerUser)->post(route('organizer.laporan.hasil', $event), $draft + [
            'dokumentasi' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
        ]);
        $baru = $this->laporanHasil($event)->berkas_dokumentasi;

        $this->assertNotSame($lama, $baru);
        Storage::disk('local')->assertMissing($lama);
        Storage::disk('local')->assertExists($baru);
    }

    public function test_laporan_yang_sudah_dikirim_tidak_bisa_diubah(): void
    {
        $event = $this->buatEvent();
        $this->kirimHasil($event);

        $this->kirimHasil($event, ['ringkasan' => 'Ringkasan baru yang cukup panjang untuk lolos validasi.'])
            ->assertForbidden();

        $this->assertSame(self::RINGKASAN, $this->laporanHasil($event)->ringkasan);
    }

    public function test_laporan_hasil_tidak_bisa_dibuat_untuk_event_yang_belum_selesai(): void
    {
        $event = $this->buatEvent('Terverifikasi');

        $this->kirimHasil($event)->assertForbidden();

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_organizer_lain_tidak_boleh_mengisi_laporan(): void
    {
        $event = $this->buatEvent();

        $lain = User::factory()->create(['role' => 'organizer', 'status_verifikasi' => 'Terverifikasi']);
        OrganizerProfile::create(['user_id' => $lain->id, 'nama_organisasi' => 'Organisasi Lain', 'jenis' => 'NGO', 'kontak' => '0811']);

        $this->kirimHasil($event, [], $lain)->assertForbidden();
    }

    public function test_sponsor_baru_melihat_laporan_hasil_setelah_dikirim(): void
    {
        $event = $this->buatEvent();

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.laporan.hasil', $event), ['aksi' => 'draft', 'ringkasan' => 'Masih draft']);

        $this->actingAs($this->sponsorUser)->get(route('sponsor.laporan.show', $event))
            ->assertOk()
            ->assertSee('belum dikirim oleh pihak Organizer')
            ->assertDontSee('Masih draft');

        $this->kirimHasil($event);

        $this->actingAs($this->sponsorUser)->get(route('sponsor.laporan.show', $event))
            ->assertOk()
            ->assertSee(self::RINGKASAN)
            ->assertSee(self::CAPAIAN);
    }

    public function test_dokumentasi_hanya_dapat_dibuka_pihak_berhak(): void
    {
        $event = $this->buatEvent();

        $this->kirimHasil($event, ['dokumentasi' => UploadedFile::fake()->create('dokumentasi.pdf', 100, 'application/pdf')]);
        $laporan = $this->laporanHasil($event);
        $url = route('files.show', ['jenis' => 'dokumentasi', 'id' => $laporan->id]);

        $sponsorLain = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Terverifikasi']);
        SponsorProfile::create([
            'user_id' => $sponsorLain->id, 'nama_perusahaan' => 'PT Lain', 'nib' => '999', 'fokus_csr' => 'Sosial', 'kontak' => '0811',
        ]);

        $this->actingAs($this->organizerUser)->get($url)->assertOk();
        $this->actingAs($this->sponsorUser)->get($url)->assertOk();
        $this->actingAs($sponsorLain)->get($url)->assertForbidden();
    }

    public function test_dokumentasi_draft_belum_bisa_dibuka_sponsor(): void
    {
        $event = $this->buatEvent();

        $this->actingAs($this->organizerUser)->post(route('organizer.laporan.hasil', $event), [
            'aksi' => 'draft',
            'dokumentasi' => UploadedFile::fake()->create('dokumentasi.pdf', 100, 'application/pdf'),
        ]);
        $laporan = $this->laporanHasil($event);

        $this->actingAs($this->sponsorUser)
            ->get(route('files.show', ['jenis' => 'dokumentasi', 'id' => $laporan->id]))
            ->assertForbidden();
    }

    public function test_organizer_melihat_catatan_revisi_dan_dapat_mengirim_ulang(): void
    {
        $event = $this->buatEvent();
        $this->kirimHasil($event);
        $laporan = $this->laporanHasil($event);

        $this->actingAs($this->sponsorUser)->post(route('sponsor.laporan.tanggapi', $laporan), [
            'status' => 'Perlu Revisi',
            'catatan' => 'Mohon cantumkan jumlah peserta per sekolah.',
        ])->assertSessionHas('success');

        $this->assertSame('Perlu Revisi', $laporan->fresh()->status);

        $this->actingAs($this->organizerUser)->get(route('organizer.laporan.show', $event))
            ->assertOk()
            ->assertSee('Sponsor meminta revisi laporan hasil program')
            ->assertSee('Mohon cantumkan jumlah peserta per sekolah.')
            ->assertSee('Tanggapan Sponsor');

        $this->kirimHasil($event, ['capaian_hasil' => self::CAPAIAN.' Rincian peserta per sekolah terlampir.'])
            ->assertSessionHas('success');

        $laporan->refresh();
        $this->assertSame('Dikirim', $laporan->status);
        $this->assertSame(0, $laporan->reviews()->count());
    }

    public function test_event_menjadi_dilaporkan_setelah_laporan_keuangan_dan_hasil_terkirim(): void
    {
        $event = $this->buatEvent();
        Report::create(['event_id' => $event->id, 'jenis' => 'keuangan', 'status' => 'Dikirim', 'tanggal_kirim' => now()]);

        $this->kirimHasil($event);

        $this->assertSame('Dilaporkan', $event->fresh()->status);
    }
}