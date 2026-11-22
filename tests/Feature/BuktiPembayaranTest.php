<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BuktiPembayaranTest extends TestCase
{
    use RefreshDatabase;

    private User $organizerUser;
    private OrganizerProfile $organizer;
    private User $sponsorUser;
    private SponsorProfile $sponsor;
    private Event $event;
    private Commitment $commitment;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->organizerUser = User::factory()->create(['role' => 'organizer', 'status_verifikasi' => 'Disetujui']);
        $this->organizer = OrganizerProfile::create([
            'user_id' => $this->organizerUser->id,
            'nama_organisasi' => 'Komunitas Hijau Bersama',
            'jenis' => 'Komunitas',
            'kontak' => '081298765432',
            'alamat' => 'Jl. Kaliurang KM 5',
        ]);

        $this->sponsorUser = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        $this->sponsor = SponsorProfile::create([
            'user_id' => $this->sponsorUser->id,
            'nama_perusahaan' => 'PT Paragon Innovation',
            'nib' => '1234567890123',
            'fokus_csr' => 'Lingkungan, Pendidikan',
            'kontak' => '081234567890',
        ]);

        $category = Category::create(['nama_kategori' => 'Lingkungan']);

        $this->event = Event::create([
            'organizer_id' => $this->organizer->id,
            'category_id' => $category->id,
            'judul' => 'Aksi Tanam 1000 Mangrove',
            'deskripsi' => 'Penanaman mangrove di pesisir utara.',
            'lokasi' => 'Semarang',
            'target_dana' => 50000000,
            'benefit_sponsor' => 'Logo pada banner dan sertifikat',
            'tanggal_pelaksanaan' => now()->addDays(20),
            'status' => 'Terverifikasi',
        ]);

        $this->commitment = Commitment::create([
            'event_id' => $this->event->id,
            'sponsor_id' => $this->sponsor->id,
            'nominal' => 15000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Diterima',
            'tanggal_pengajuan' => now(),
        ]);
    }

    private function unggah(?User $user = null, ?UploadedFile $file = null)
    {
        return $this->actingAs($user ?? $this->sponsorUser)->post(
            route('sponsor.komitmen.bukti', $this->commitment),
            ['bukti' => $file ?? UploadedFile::fake()->create('transfer.pdf', 200, 'application/pdf')]
        );
    }

    private function sponsorLain(): User
    {
        $user = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        SponsorProfile::create([
            'user_id' => $user->id,
            'nama_perusahaan' => 'PT Lain',
            'nib' => '9999999999999',
            'fokus_csr' => 'Sosial',
            'kontak' => '0811111111',
        ]);

        return $user;
    }

    public function test_sponsor_dapat_mengunggah_bukti_pembayaran(): void
    {
        $this->unggah()->assertSessionHas('success');

        $c = $this->commitment->fresh();
        $this->assertSame('Menunggu Verifikasi', $c->status_pembayaran);
        $this->assertNotNull($c->bukti_pembayaran);
        $this->assertNotNull($c->tanggal_bukti);
        Storage::disk('local')->assertExists($c->bukti_pembayaran);
        $this->assertDatabaseHas('notifications', ['user_id' => $this->organizerUser->id]);
    }

    public function test_tidak_bisa_mengunggah_sebelum_komitmen_diterima(): void
    {
        $this->commitment->update(['status' => 'Menunggu Tanggapan']);

        $this->unggah()->assertForbidden();
    }

    public function test_sponsor_lain_tidak_boleh_mengunggah(): void
    {
        $this->unggah($this->sponsorLain())->assertForbidden();
    }

    public function test_organizer_tidak_boleh_mengunggah(): void
    {
        $this->unggah($this->organizerUser)->assertForbidden();
    }

    public function test_berkas_wajib_dan_harus_valid(): void
    {
        $url = route('sponsor.komitmen.bukti', $this->commitment);

        $this->actingAs($this->sponsorUser)->post($url, [])->assertSessionHasErrors('bukti');

        $this->unggah(null, UploadedFile::fake()->create('catatan.txt', 10, 'text/plain'))
            ->assertSessionHasErrors('bukti');

        $this->unggah(null, UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf'))
            ->assertSessionHasErrors('bukti');

        $this->assertSame('Belum Dibayar', $this->commitment->fresh()->status_pembayaran);
    }

    public function test_mengganti_bukti_menghapus_berkas_lama(): void
    {
        $this->unggah();
        $lama = $this->commitment->fresh()->bukti_pembayaran;

        $this->unggah(null, UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'));
        $baru = $this->commitment->fresh()->bukti_pembayaran;

        $this->assertNotSame($lama, $baru);
        Storage::disk('local')->assertMissing($lama);
        Storage::disk('local')->assertExists($baru);
    }

    public function test_bukti_yang_sudah_terverifikasi_tidak_bisa_diganti(): void
    {
        $this->commitment->update(['status_pembayaran' => 'Terverifikasi']);

        $this->unggah()->assertForbidden();
    }

    public function test_hanya_pihak_berhak_yang_dapat_membuka_bukti(): void
    {
        $this->unggah();
        $url = route('files.show', ['pembayaran', $this->commitment->id]);

        $admin = User::factory()->create(['role' => 'admin', 'status_verifikasi' => 'Disetujui']);
        $organizerLain = User::factory()->create(['role' => 'organizer', 'status_verifikasi' => 'Disetujui']);
        OrganizerProfile::create([
            'user_id' => $organizerLain->id,
            'nama_organisasi' => 'Organisasi Lain',
            'jenis' => 'NGO',
            'kontak' => '0811111111',
        ]);

        $this->actingAs($this->sponsorUser)->get($url)->assertOk();
        $this->actingAs($this->organizerUser)->get($url)->assertOk();
        $this->actingAs($admin)->get($url)->assertOk();
        $this->actingAs($this->sponsorLain())->get($url)->assertForbidden();
        $this->actingAs($organizerLain)->get($url)->assertForbidden();
    }

    public function test_organizer_melihat_tautan_bukti_di_halaman_komitmen(): void
    {
        $this->actingAs($this->organizerUser)->get(route('organizer.komitmen'))
            ->assertOk()
            ->assertSee('Sponsor belum mengunggah bukti.');

        $this->unggah();

        $this->actingAs($this->organizerUser)->get(route('organizer.komitmen'))
            ->assertOk()
            ->assertSee('Lihat bukti pembayaran');
    }

    public function test_dashboard_sponsor_menampilkan_status_pembayaran(): void
    {
        $this->actingAs($this->sponsorUser)->get(route('sponsor.dashboard'))
            ->assertOk()
            ->assertSee('Unggah Bukti Pembayaran');

        $this->unggah();

        $this->actingAs($this->sponsorUser)->get(route('sponsor.dashboard'))
            ->assertOk()
            ->assertSee('Ganti Bukti');
    }
}