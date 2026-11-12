<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Notifikasi;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjukanProposalVerifikasiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status_verifikasi' => 'Terverifikasi',
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Lingkungan',
        ]);
    }

    private function createOrganizer(string $statusVerifikasi = 'Terverifikasi'): User
    {
        $user = User::factory()->create([
            'role' => 'organizer',
            'status_verifikasi' => $statusVerifikasi,
        ]);

        OrganizerProfile::create([
            'user_id' => $user->id,
            'nama_organisasi' => 'Komunitas Peduli Alam',
            'jenis' => 'Komunitas',
            'kontak' => '081234567890',
            'alamat' => 'Surabaya',
        ]);

        return $user;
    }

    private function createEvent(User $organizerUser, string $status = 'Draft'): Event
    {
        return Event::create([
            'organizer_id' => $organizerUser->organizerProfile->id,
            'category_id' => $this->category->id,
            'judul' => 'Penanaman Mangrove Pesisir',
            'deskripsi' => 'Aksi restorasi pesisir pantai bersama masyarakat lokal.',
            'lokasi' => 'Pantai Kenjeran, Surabaya',
            'penerima_manfaat' => '1.000 warga pesisir',
            'tanggal_pelaksanaan' => now()->addDays(20),
            'target_dana' => 20000000,
            'benefit_sponsor' => 'Pencantuman logo perusahaan pada banner dan media publikasi.',
            'status' => $status,
        ]);
    }

    public function test_guest_cannot_ajukan_proposal(): void
    {
        $organizer = $this->createOrganizer();
        $event = $this->createEvent($organizer);

        $response = $this->post(route('organizer.proposals.ajukan', $event));
        $response->assertRedirect(route('login'));
    }

    public function test_organizer_cannot_ajukan_other_organizers_proposal(): void
    {
        $organizerA = $this->createOrganizer();
        $organizerB = $this->createOrganizer();
        $eventA = $this->createEvent($organizerA);

        $response = $this->actingAs($organizerB)->post(route('organizer.proposals.ajukan', $eventA));
        $response->assertForbidden();
    }

    public function test_unverified_organizer_cannot_ajukan_proposal(): void
    {
        $organizer = $this->createOrganizer(statusVerifikasi: 'Menunggu');
        $event = $this->createEvent($organizer);

        $response = $this->actingAs($organizer)->post(route('organizer.proposals.ajukan', $event));

        $response->assertSessionHas('error');
        $this->assertEquals('Draft', $event->fresh()->status);
    }

    public function test_verified_organizer_can_ajukan_draft_proposal(): void
    {
        $organizer = $this->createOrganizer(statusVerifikasi: 'Terverifikasi');
        $event = $this->createEvent($organizer, status: 'Draft');

        $response = $this->actingAs($organizer)->post(route('organizer.proposals.ajukan', $event));

        $response->assertSessionHas('success');
        $this->assertEquals('Menunggu Verifikasi', $event->fresh()->status);

        // Notifikasi ke admin terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'pesan' => "Proposal baru menunggu verifikasi: {$event->judul}",
        ]);

        // Notifikasi ke organizer terkirim
        $this->assertDatabaseHas('notifications', [
            'user_id' => $organizer->id,
        ]);
    }

    public function test_verified_organizer_can_ajukan_revisi_proposal(): void
    {
        $organizer = $this->createOrganizer(statusVerifikasi: 'Terverifikasi');
        $event = $this->createEvent($organizer, status: 'Perlu Revisi');

        $response = $this->actingAs($organizer)->post(route('organizer.proposals.ajukan', $event));

        $response->assertSessionHas('success');
        $this->assertEquals('Menunggu Verifikasi', $event->fresh()->status);
    }

    public function test_cannot_ajukan_already_verified_or_finished_event(): void
    {
        $organizer = $this->createOrganizer(statusVerifikasi: 'Terverifikasi');
        $eventVerified = $this->createEvent($organizer, status: 'Terverifikasi');
        $eventSelesai = $this->createEvent($organizer, status: 'Selesai');

        $this->actingAs($organizer)->post(route('organizer.proposals.ajukan', $eventVerified))
            ->assertForbidden();

        $this->actingAs($organizer)->post(route('organizer.proposals.ajukan', $eventSelesai))
            ->assertForbidden();
    }

    public function test_organizer_can_create_and_directly_ajukan_proposal(): void
    {
        $organizer = $this->createOrganizer(statusVerifikasi: 'Terverifikasi');

        $response = $this->actingAs($organizer)->post(route('organizer.proposals.store'), [
            'judul' => 'Festival Musik Amal',
            'category_id' => $this->category->id,
            'deskripsi' => 'Konser penggalangan dana kemanusiaan untuk korban bencana alam.',
            'lokasi' => 'Stadion Utama Surabaya',
            'penerima_manfaat' => '5.000 jiwa',
            'tanggal_pelaksanaan' => now()->addDays(30)->format('Y-m-d'),
            'target_dana' => 75000000,
            'benefit_sponsor' => 'Plakat penghargaan dan ruang promosi banner panggung.',
            'action' => 'ajukan',
        ]);

        $response->assertRedirect(route('organizer.proposals'));
        $this->assertDatabaseHas('events', [
            'judul' => 'Festival Musik Amal',
            'status' => 'Menunggu Verifikasi',
        ]);
    }

    public function test_organizer_can_update_and_directly_ajukan_proposal(): void
    {
        $organizer = $this->createOrganizer(statusVerifikasi: 'Terverifikasi');
        $event = $this->createEvent($organizer, status: 'Perlu Revisi');

        $response = $this->actingAs($organizer)->put(route('organizer.proposals.update', $event), [
            'judul' => 'Penanaman Mangrove Pesisir (Revisi)',
            'category_id' => $this->category->id,
            'deskripsi' => 'Aksi restorasi pesisir pantai bersama 500 relawan dan nelayan.',
            'lokasi' => 'Pantai Kenjeran Timur, Surabaya',
            'penerima_manfaat' => '1.500 warga pesisir',
            'tanggal_pelaksanaan' => now()->addDays(20)->format('Y-m-d'),
            'target_dana' => 25000000,
            'benefit_sponsor' => 'Pencantuman logo pada seluruh materi digital dan seragam relawan.',
            'action' => 'ajukan',
        ]);

        $response->assertRedirect(route('organizer.proposals'));
        $this->assertEquals('Menunggu Verifikasi', $event->fresh()->status);
        $this->assertEquals('Penanaman Mangrove Pesisir (Revisi)', $event->fresh()->judul);
    }
}
