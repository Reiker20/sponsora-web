<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorRiwayatTest extends TestCase
{
    use RefreshDatabase;

    private User $sponsorUser;
    private SponsorProfile $sponsorProfile;
    private User $organizerUser;
    private OrganizerProfile $organizerProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sponsorUser = User::factory()->create([
            'role' => 'sponsor',
            'status_verifikasi' => 'Terverifikasi',
        ]);
        $this->sponsorProfile = SponsorProfile::create([
            'user_id' => $this->sponsorUser->id,
            'nama_perusahaan' => 'PT Paragon Innovation',
            'nib' => '1234567890123',
            'fokus_csr' => 'Lingkungan, Pendidikan',
            'kontak' => '081234567890',
        ]);

        $this->organizerUser = User::factory()->create([
            'role' => 'organizer',
            'status_verifikasi' => 'Terverifikasi',
        ]);
        $this->organizerProfile = OrganizerProfile::create([
            'user_id' => $this->organizerUser->id,
            'nama_organisasi' => 'Komunitas Hijau Bersama',
            'jenis' => 'Komunitas',
            'kontak' => '081298765432',
            'alamat' => 'Jl. Kaliurang KM 5',
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Lingkungan',
        ]);
    }

    public function test_guest_cannot_access_sponsor_riwayat(): void
    {
        $response = $this->get(route('sponsor.riwayat'));
        $response->assertRedirect(route('login'));
    }

    public function test_organizer_cannot_access_sponsor_riwayat(): void
    {
        $response = $this->actingAs($this->organizerUser)->get(route('sponsor.riwayat'));
        $response->assertForbidden();
    }

    public function test_sponsor_can_view_riwayat_page_with_commitments(): void
    {
        $event = Event::create([
            'organizer_id' => $this->organizerProfile->id,
            'category_id' => $this->category->id,
            'judul' => 'Aksi Tanam 1000 Mangrove',
            'deskripsi' => 'Penanaman mangrove di pesisir utara.',
            'lokasi' => 'Semarang',
            'target_dana' => 50000000,
            'benefit_sponsor' => 'Logo pada banner dan sertifikat',
            'tanggal_pelaksanaan' => now()->addDays(20),
            'status' => 'Terverifikasi',
        ]);

        Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'nominal' => 15000000,
            'bentuk_dukungan' => 'Dana Segar',
            'status' => 'Diterima',
            'tanggal_pengajuan' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->sponsorUser)->get(route('sponsor.riwayat'));

        $response->assertOk();
        $response->assertSee('Riwayat Kemitraan Sponsorship');
        $response->assertSee('Aksi Tanam 1000 Mangrove');
        $response->assertSee('Komunitas Hijau Bersama');
        $response->assertSee('Rp 15.000.000');
    }

    public function test_sponsor_can_filter_riwayat_by_status(): void
    {
        $event = Event::create([
            'organizer_id' => $this->organizerProfile->id,
            'category_id' => $this->category->id,
            'judul' => 'Bersih Sungai 2026',
            'deskripsi' => 'Aksi membersihkan sungai.',
            'lokasi' => 'Yogyakarta',
            'target_dana' => 20000000,
            'benefit_sponsor' => 'Logo perusahaan',
            'tanggal_pelaksanaan' => now()->addDays(15),
            'status' => 'Terverifikasi',
        ]);

        Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'nominal' => 5000000,
            'bentuk_dukungan' => 'Dana',
            'status' => 'Menunggu Tanggapan',
            'tanggal_pengajuan' => now()->subDays(1),
        ]);

        $responseDiterima = $this->actingAs($this->sponsorUser)->get(route('sponsor.riwayat', ['status' => 'Diterima']));
        $responseDiterima->assertOk();
        $responseDiterima->assertDontSee('Bersih Sungai 2026');

        $responseMenunggu = $this->actingAs($this->sponsorUser)->get(route('sponsor.riwayat', ['status' => 'Menunggu Tanggapan']));
        $responseMenunggu->assertOk();
        $responseMenunggu->assertSee('Bersih Sungai 2026');
    }
}
