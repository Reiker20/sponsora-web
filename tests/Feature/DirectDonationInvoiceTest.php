<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\Notifikasi;
use App\Models\OrganizerProfile;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectDonationInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $organizerUser;
    private OrganizerProfile $organizer;
    private User $sponsorUser;
    private SponsorProfile $sponsor;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');

        $this->organizerUser = User::factory()->create([
            'role' => 'organizer',
            'status_verifikasi' => 'Disetujui',
        ]);
        $this->organizer = OrganizerProfile::create([
            'user_id' => $this->organizerUser->id,
            'nama_organisasi' => 'Komunitas Peduli Negeri',
            'jenis' => 'Komunitas',
            'kontak' => '081234567890',
            'alamat' => 'Surabaya',
        ]);

        $this->sponsorUser = User::factory()->create([
            'role' => 'sponsor',
            'status_verifikasi' => 'Disetujui',
        ]);
        $this->sponsor = SponsorProfile::create([
            'user_id' => $this->sponsorUser->id,
            'nama_perusahaan' => 'PT Sinar Harapan',
            'nib' => '1234567890123',
            'fokus_csr' => 'Sosial, Pendidikan',
            'kontak' => '082233445566',
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Sosial',
        ]);
    }

    private function baseProposalPayload(array $overrides = []): array
    {
        return array_merge([
            'judul' => 'Program Bantuan Pendidikan Pesisir',
            'category_id' => $this->category->id,
            'deskripsi' => 'Program bantuan alat tulis, buku bacaan, dan beasiswa untuk anak nelayan.',
            'lokasi' => 'Surabaya',
            'penerima_manfaat' => '250 pelajar',
            'tanggal_pelaksanaan' => now()->addDays(20)->format('Y-m-d'),
            'target_dana' => 25000000,
            'benefit_sponsor' => 'Logo di spanduk kegiatan dan laporan dokumentasi resmi.',
            'action' => 'draft',
        ], $overrides);
    }

    private function createVerifiedEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category->id,
            'judul' => 'Gerakan Pangan Gratis',
            'deskripsi' => 'Pembagian paket sembako dan pangan sehat bagi keluarga prasejahtera.',
            'lokasi' => 'Bandung',
            'target_dana' => 20000000,
            'benefit_sponsor' => 'Logo perusahaan pada paket bantuan dan backdrop.',
            'tanggal_pelaksanaan' => now()->addDays(15),
            'nama_bank' => 'BCA',
            'nomor_rekening' => '1234567890',
            'atas_nama_rekening' => 'Komunitas Peduli Negeri',
            'qris_image' => 'qris/sample-qris.png',
            'status' => 'Terverifikasi',
        ], $overrides));
    }

    public function test_proposal_requires_at_least_bank_account_or_qris(): void
    {
        // 1. Kosong keduanya -> ditolak validasi
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload())
            ->assertSessionHasErrors('metode_pembayaran');
        $this->assertSame(0, Event::count());

        // 2. Rekening bank saja -> berhasil
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'judul' => 'Proposal Bank Saja',
                'nama_bank' => 'Mandiri',
                'nomor_rekening' => '1400012345678',
                'atas_nama_rekening' => 'Komunitas Peduli Negeri',
            ]))
            ->assertRedirect(route('organizer.proposals'));

        $this->assertDatabaseHas('events', [
            'judul' => 'Proposal Bank Saja',
            'nama_bank' => 'Mandiri',
            'nomor_rekening' => '1400012345678',
            'qris_image' => null,
        ]);

        // 3. QRIS saja -> berhasil
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'judul' => 'Proposal QRIS Saja',
                'qris_image' => UploadedFile::fake()->image('qris.png', 400, 400),
            ]))
            ->assertRedirect(route('organizer.proposals'));

        $qrisEvent = Event::where('judul', 'Proposal QRIS Saja')->firstOrFail();
        $this->assertNotNull($qrisEvent->qris_image);
        Storage::disk('public')->assertExists($qrisEvent->qris_image);

        // 4. Edit proposal QRIS saja dengan menghapus QRIS tanpa mengisi rekening -> gagal
        $this->actingAs($this->organizerUser)
            ->put(route('organizer.proposals.update', $qrisEvent), $this->baseProposalPayload([
                'judul' => 'Proposal QRIS Saja',
                'hapus_qris' => '1',
            ]))
            ->assertSessionHasErrors('metode_pembayaran');

        // 5. Keduanya sekaligus -> berhasil
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'judul' => 'Proposal Bank dan QRIS',
                'nama_bank' => 'BRI',
                'nomor_rekening' => '001122334455',
                'atas_nama_rekening' => 'Komunitas Peduli Negeri',
                'qris_image' => UploadedFile::fake()->image('qris-2.jpg', 400, 400),
            ]))
            ->assertRedirect(route('organizer.proposals'));

        $bothEvent = Event::where('judul', 'Proposal Bank dan QRIS')->firstOrFail();
        $this->assertSame('BRI', $bothEvent->nama_bank);
        $this->assertNotNull($bothEvent->qris_image);
    }

    public function test_event_detail_displays_organizer_bank_and_qris_and_sponsor_can_donate_with_proof(): void
    {
        $event = $this->createVerifiedEvent();

        $this->actingAs($this->sponsorUser)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('BCA')
            ->assertSee('1234567890')
            ->assertSee('Komunitas Peduli Negeri')
            ->assertSee('sample-qris.png');

        // Tanpa bukti transfer -> gagal validasi
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 5000000,
                'bentuk_dukungan' => 'Dana Tunai',
            ])
            ->assertSessionHasErrors('bukti_pembayaran');

        // Dengan nominal + bukti transfer -> berhasil masuk antrean validasi Organizer
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 5000000,
                'bentuk_dukungan' => 'Dana Tunai',
                'catatan' => 'Sudah transfer via mobile banking BCA.',
                'bukti_pembayaran' => UploadedFile::fake()->image('bukti-tf.jpg'),
            ])
            ->assertSessionHas('komitmen_sukses', 5000000);

        $commitment = Commitment::firstOrFail();
        $this->assertSame(5000000, $commitment->nominal);
        $this->assertSame(5000000, $commitment->nominal_klaim);
        $this->assertNull($commitment->nominal_tervalidasi);
        $this->assertSame('Menunggu Tanggapan', $commitment->status);
        $this->assertSame('Menunggu Verifikasi', $commitment->status_pembayaran);
        $this->assertNotNull($commitment->bukti_pembayaran);
        Storage::disk('local')->assertExists($commitment->bukti_pembayaran);

        // Sebelum divalidasi Organizer, dana_terkumpul event belum bertambah
        $this->assertSame(0, $event->fresh()->dana_terkumpul);
        $this->assertSame(0, $event->fresh()->mitra_count);
    }

    public function test_organizer_validates_exact_nominal_generates_invoice_and_notifies_sponsor(): void
    {
        $event = $this->createVerifiedEvent();
        $commitment = Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsor->id,
            'nominal' => 5000000,
            'nominal_klaim' => 5000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Menunggu Verifikasi',
            'bukti_pembayaran' => 'pembayaran/bukti.jpg',
            'tanggal_bukti' => now(),
            'tanggal_pengajuan' => now(),
        ]);

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), [
                'nominal_tervalidasi' => 5000000,
                'catatan_organizer' => 'Dana telah masuk ke rekening BCA kami.',
            ])
            ->assertSessionHas('success');

        $fresh = $commitment->fresh();
        $this->assertSame('Diterima', $fresh->status);
        $this->assertSame('Terverifikasi', $fresh->status_pembayaran);
        $this->assertSame(5000000, $fresh->nominal);
        $this->assertSame(5000000, $fresh->nominal_klaim);
        $this->assertSame(5000000, $fresh->nominal_tervalidasi);
        $this->assertStringStartsWith('INV-SPO-', $fresh->nomor_invoice);
        $this->assertNotNull($fresh->tanggal_validasi);

        $this->assertSame(5000000, $event->fresh()->dana_terkumpul);
        $this->assertSame(1, $event->fresh()->mitra_count);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sponsorUser->id,
            'url' => route('sponsor.inbox.show', $commitment),
        ]);
    }

    public function test_organizer_adjusts_fraudulent_nominal_and_invoice_uses_validated_nominal(): void
    {
        $event = $this->createVerifiedEvent();

        // Kasus 1: Sponsor klaim Rp 10.000.000 padahal mutasi hanya Rp 500.000
        $c1 = Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsor->id,
            'nominal' => 10000000,
            'nominal_klaim' => 10000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Menunggu Verifikasi',
            'bukti_pembayaran' => 'pembayaran/curang.jpg',
            'tanggal_bukti' => now(),
            'tanggal_pengajuan' => now(),
        ]);

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $c1), [
                'nominal_tervalidasi' => 500000,
                'catatan_organizer' => 'Mutasi masuk hanya Rp 500.000',
            ])
            ->assertSessionHas('success');

        $c1->refresh();
        $this->assertSame(10000000, $c1->nominal_klaim);
        $this->assertSame(500000, $c1->nominal_tervalidasi);
        $this->assertSame(500000, $c1->nominal);
        $this->assertSame(500000, $event->fresh()->dana_terkumpul);
        $this->assertSame(1, $event->fresh()->mitra_count);

        // Kasus 2: Bukti fiktif -> Organizer validasi Rp 0 (tanpa status Ditolak)
        $sponsor2User = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        $sponsor2 = SponsorProfile::create([
            'user_id' => $sponsor2User->id,
            'nama_perusahaan' => 'PT Fiktif Jaya',
            'kontak' => '0899999999',
        ]);
        $c2 = Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $sponsor2->id,
            'nominal' => 7000000,
            'nominal_klaim' => 7000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Menunggu Verifikasi',
            'bukti_pembayaran' => 'pembayaran/fiktif.jpg',
            'tanggal_bukti' => now(),
            'tanggal_pengajuan' => now(),
        ]);

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $c2), [
                'nominal_tervalidasi' => 0,
                'catatan_organizer' => 'Dana tidak ditemukan di mutasi rekening.',
            ])
            ->assertSessionHas('success');

        $c2->refresh();
        $this->assertSame(0, $c2->nominal);
        $this->assertSame(0, $c2->nominal_tervalidasi);
        $this->assertSame('Terverifikasi', $c2->status_pembayaran);
        $this->assertSame(500000, $event->fresh()->dana_terkumpul);
        $this->assertSame(1, $event->fresh()->mitra_count);
    }

    public function test_sponsor_can_view_inbox_and_download_valid_pdf_invoice_while_others_are_forbidden(): void
    {
        $event = $this->createVerifiedEvent();
        $commitment = Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsor->id,
            'nominal' => 10000000,
            'nominal_klaim' => 10000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Menunggu Verifikasi',
            'bukti_pembayaran' => 'pembayaran/tf.jpg',
            'tanggal_bukti' => now(),
            'tanggal_pengajuan' => now(),
        ]);

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), [
                'nominal_tervalidasi' => 500000,
                'catatan_organizer' => 'Disesuaikan dengan mutasi bank.',
            ])
            ->assertSessionHas('success');

        // Tidak boleh validasi ulang
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), [
                'nominal_tervalidasi' => 1000000,
            ])
            ->assertForbidden();

        $commitment->refresh();

        // Sponsor pemilik bisa buka Inbox & detail pesan Invoice
        $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox'))
            ->assertOk()
            ->assertSee($commitment->nomor_invoice)
            ->assertSee('Rp 500.000');

        $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.show', $commitment))
            ->assertOk()
            ->assertSee($commitment->nomor_invoice)
            ->assertSee('Rp 500.000')
            ->assertSee('Disesuaikan dengan mutasi bank.');

        // Sponsor pemilik bisa download PDF Invoice yang valid
        $pdfRes = $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.download', $commitment));

        $pdfRes->assertOk();
        $pdfRes->assertHeader('content-type', 'application/pdf');
        $body = $pdfRes->getContent();
        $this->assertStringStartsWith('%PDF-1.4', $body);
        $this->assertStringContainsString($commitment->nomor_invoice, $body);
        $this->assertStringContainsString('Rp 500.000', $body);

        // Sponsor lain & Organizer lain dilarang akses
        $otherSponsor = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        SponsorProfile::create([
            'user_id' => $otherSponsor->id,
            'nama_perusahaan' => 'PT Penasaran',
            'kontak' => '08112233',
        ]);

        $this->actingAs($otherSponsor)->get(route('sponsor.inbox.show', $commitment))->assertForbidden();
        $this->actingAs($otherSponsor)->get(route('sponsor.inbox.download', $commitment))->assertForbidden();
        $this->actingAs($this->organizerUser)->get(route('sponsor.inbox.download', $commitment))->assertForbidden();
    }
}
