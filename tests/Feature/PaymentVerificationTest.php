<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\Notifikasi;
use App\Models\OrganizerProfile;
use App\Models\Payment;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $sponsorUser;
    private User $orgUser;
    private Event $event;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status_verifikasi' => 'Disetujui']);
        $this->sponsorUser = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        $sponsor = SponsorProfile::create(['user_id' => $this->sponsorUser->id, 'nama_perusahaan' => 'PT Satu', 'kontak' => '0811']);
        $this->orgUser = User::factory()->create(['role' => 'organizer', 'status_verifikasi' => 'Disetujui']);
        $org = OrganizerProfile::create(['user_id' => $this->orgUser->id, 'nama_organisasi' => 'Komunitas A', 'jenis' => 'Komunitas', 'kontak' => '0822']);
        $cat = Category::create(['nama_kategori' => 'Lingkungan']);

        $this->event = Event::create([
            'organizer_id' => $org->id, 'category_id' => $cat->id, 'judul' => 'Pesisir Bersih', 'deskripsi' => 'x', 'lokasi' => 'Surabaya',
            'target_dana' => 50000000, 'benefit_sponsor' => 'Logo', 'tanggal_pelaksanaan' => now()->addDays(20), 'status' => 'Terverifikasi',
        ]);
        $bank = BankAccount::create(['nama_bank' => 'BCA', 'nomor_rekening' => '1234567890', 'atas_nama' => 'Sponsora', 'is_active' => true]);
        $c = Commitment::create(['event_id' => $this->event->id, 'sponsor_id' => $sponsor->id, 'nominal' => 5000000, 'bentuk_dukungan' => 'Dana Tunai', 'status' => 'Diterima']);

        $this->payment = Payment::create([
            'commitment_id' => $c->id, 'bank_account_id' => $bank->id, 'rekening_tujuan' => $bank->label(),
            'nama_pengirim' => 'PT Satu', 'nominal_transfer' => 5000000, 'tanggal_transfer' => now()->toDateString(),
            'bukti_transfer' => 'payments/bukti/dummy.jpg', 'status' => 'Menunggu Verifikasi',
        ]);
    }

    public function test_only_admin_can_open_verification_page(): void
    {
        $this->actingAs($this->sponsorUser)->get(route('admin.pembayaran'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.pembayaran'))->assertOk()->assertSee('PT Satu');
    }

    public function test_admin_can_verify_payment_and_parties_are_notified(): void
    {
        $this->actingAs($this->admin)->post(route('admin.pembayaran.verifikasi', $this->payment), ['status' => 'Disetujui'])
            ->assertSessionHas('success');

        $p = $this->payment->fresh();
        $this->assertSame('Terverifikasi', $p->status);
        $this->assertSame($this->admin->id, $p->verified_by);
        $this->assertNotNull($p->tanggal_verifikasi);
        $this->assertSame(5000000, $this->event->fresh()->dana_terverifikasi);
        $this->assertSame(1, Notifikasi::where('user_id', $this->sponsorUser->id)->count());
        $this->assertSame(1, Notifikasi::where('user_id', $this->orgUser->id)->count());
    }

    public function test_reject_requires_note_and_does_not_count_as_funds(): void
    {
        $this->actingAs($this->admin)->post(route('admin.pembayaran.verifikasi', $this->payment), ['status' => 'Ditolak'])
            ->assertSessionHasErrors('catatan');

        $this->actingAs($this->admin)->post(route('admin.pembayaran.verifikasi', $this->payment), ['status' => 'Ditolak', 'catatan' => 'Nominal tidak sesuai mutasi'])
            ->assertSessionHas('success');

        $this->assertSame('Ditolak', $this->payment->fresh()->status);
        $this->assertSame(0, $this->event->fresh()->dana_terverifikasi);
    }

    public function test_cannot_verify_twice(): void
    {
        $this->actingAs($this->admin)->post(route('admin.pembayaran.verifikasi', $this->payment), ['status' => 'Disetujui']);
        $this->actingAs($this->admin)->post(route('admin.pembayaran.verifikasi', $this->payment), ['status' => 'Ditolak', 'catatan' => 'x'])
            ->assertForbidden();
    }

    public function test_sponsor_can_reupload_after_rejection(): void
    {
        $this->actingAs($this->admin)->post(route('admin.pembayaran.verifikasi', $this->payment), ['status' => 'Ditolak', 'catatan' => 'Buram']);

        $this->assertTrue($this->payment->commitment->fresh()->canUploadProof());
    }
}