<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Payment;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $sponsorUser;
    private SponsorProfile $sponsor;
    private Event $event;
    private BankAccount $bank;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->sponsorUser = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        $this->sponsor = SponsorProfile::create(['user_id' => $this->sponsorUser->id, 'nama_perusahaan' => 'PT Satu', 'kontak' => '0811']);

        $orgUser = User::factory()->create(['role' => 'organizer', 'status_verifikasi' => 'Disetujui']);
        $org = OrganizerProfile::create(['user_id' => $orgUser->id, 'nama_organisasi' => 'Komunitas A', 'jenis' => 'Komunitas', 'kontak' => '0822']);
        $cat = Category::create(['nama_kategori' => 'Lingkungan']);

        $this->event = Event::create([
            'organizer_id' => $org->id, 'category_id' => $cat->id, 'judul' => 'Pesisir Bersih', 'deskripsi' => 'x', 'lokasi' => 'Surabaya',
            'target_dana' => 50000000, 'benefit_sponsor' => 'Logo', 'tanggal_pelaksanaan' => now()->addDays(20), 'status' => 'Terverifikasi',
        ]);
        $this->bank = BankAccount::create(['nama_bank' => 'BCA', 'nomor_rekening' => '1234567890', 'atas_nama' => 'Sponsora', 'is_active' => true]);
    }

    private function commitment(string $status = 'Diterima', ?SponsorProfile $sponsor = null): Commitment
    {
        return Commitment::create([
            'event_id' => $this->event->id, 'sponsor_id' => ($sponsor ?? $this->sponsor)->id,
            'nominal' => 5000000, 'bentuk_dukungan' => 'Dana Tunai', 'status' => $status,
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'bank_account_id' => $this->bank->id,
            'nama_pengirim' => 'PT Satu',
            'nominal_transfer' => 5000000,
            'tanggal_transfer' => now()->toDateString(),
            'bukti_transfer' => UploadedFile::fake()->image('bukti.jpg'),
        ], $over);
    }

    public function test_sponsor_sees_active_bank_accounts_on_payment_page(): void
    {
        BankAccount::create(['nama_bank' => 'BNI', 'nomor_rekening' => '555000', 'atas_nama' => 'Mati', 'is_active' => false]);
        $c = $this->commitment();

        $this->actingAs($this->sponsorUser)->get(route('sponsor.pembayaran', $c))
            ->assertOk()->assertSee('1234567890')->assertDontSee('555000');
    }

    public function test_sponsor_can_upload_proof_after_commitment_accepted(): void
    {
        $c = $this->commitment();

        $this->actingAs($this->sponsorUser)->post(route('sponsor.pembayaran.store', $c), $this->payload())
            ->assertRedirect(route('sponsor.pembayaran', $c));

        $p = Payment::first();
        $this->assertSame('Menunggu Verifikasi', $p->status);
        Storage::disk('local')->assertExists($p->bukti_transfer);
    }

    public function test_cannot_upload_if_commitment_not_accepted(): void
    {
        $c = $this->commitment('Menunggu Tanggapan');

        $this->actingAs($this->sponsorUser)->post(route('sponsor.pembayaran.store', $c), $this->payload())
            ->assertSessionHas('error');
        $this->assertSame(0, Payment::count());
    }

    public function test_cannot_upload_twice_while_pending(): void
    {
        $c = $this->commitment();
        $this->actingAs($this->sponsorUser)->post(route('sponsor.pembayaran.store', $c), $this->payload());
        $this->actingAs($this->sponsorUser)->post(route('sponsor.pembayaran.store', $c), $this->payload())
            ->assertSessionHas('error');

        $this->assertSame(1, Payment::count());
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        $c = $this->commitment();

        $this->actingAs($this->sponsorUser)->post(route('sponsor.pembayaran.store', $c),
            $this->payload(['bukti_transfer' => UploadedFile::fake()->create('virus.exe', 10)]))
            ->assertSessionHasErrors('bukti_transfer');
    }

    public function test_other_sponsor_cannot_access_payment(): void
    {
        $c = $this->commitment();
        $otherUser = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        SponsorProfile::create(['user_id' => $otherUser->id, 'nama_perusahaan' => 'PT Dua', 'kontak' => '0833']);

        $this->actingAs($otherUser)->get(route('sponsor.pembayaran', $c))->assertForbidden();
        $this->actingAs($otherUser)->post(route('sponsor.pembayaran.store', $c), $this->payload())->assertForbidden();
    }

    public function test_proof_file_is_private(): void
    {
        $c = $this->commitment();
        $this->actingAs($this->sponsorUser)->post(route('sponsor.pembayaran.store', $c), $this->payload());
        $p = Payment::first();

        $this->actingAs($this->sponsorUser)->get($p->buktiUrl())->assertOk();

        $otherUser = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        SponsorProfile::create(['user_id' => $otherUser->id, 'nama_perusahaan' => 'PT Dua', 'kontak' => '0833']);
        $this->actingAs($otherUser)->get($p->buktiUrl())->assertForbidden();
    }
}