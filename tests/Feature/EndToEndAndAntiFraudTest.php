<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\Notifikasi;
use App\Models\OrganizerProfile;
use App\Models\Report;
use App\Models\ReportItem;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndAndAntiFraudTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
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

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'status_verifikasi' => 'Terverifikasi',
        ]);

        $this->organizerUser = User::factory()->create([
            'role' => 'organizer',
            'status_verifikasi' => 'Disetujui',
        ]);
        $this->organizer = OrganizerProfile::create([
            'user_id' => $this->organizerUser->id,
            'nama_organisasi' => 'Komunitas Nusantara Peduli',
            'jenis' => 'Komunitas',
            'kontak' => '081234567890',
            'alamat' => 'Jl. Merdeka No. 10, Surabaya',
        ]);

        $this->sponsorUser = User::factory()->create([
            'role' => 'sponsor',
            'status_verifikasi' => 'Disetujui',
        ]);
        $this->sponsor = SponsorProfile::create([
            'user_id' => $this->sponsorUser->id,
            'nama_perusahaan' => 'PT Sejahtera Abadi',
            'nib' => '1122334455667',
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
            'judul' => 'Ekspedisi Air Bersih Desa',
            'category_id' => $this->category->id,
            'deskripsi' => 'Pembangunan fasilitas filtrasi dan sumur bor air bersih untuk warga desa.',
            'lokasi' => 'Pacitan, Jawa Timur',
            'penerima_manfaat' => '600 kepala keluarga',
            'tanggal_pelaksanaan' => now()->addDays(30)->format('Y-m-d'),
            'target_dana' => 50000000,
            'benefit_sponsor' => 'Logo di prasasti sumur bor, banner acara, dan laporan pertanggungjawaban.',
            'action' => 'draft',
        ], $overrides);
    }

    private function createVerifiedEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'organizer_id' => $this->organizer->id,
            'category_id' => $this->category->id,
            'judul' => 'Gerakan Desa Mandiri',
            'deskripsi' => 'Pelatihan keterampilan dan bantuan modal usaha bagi warga desa.',
            'lokasi' => 'Malang',
            'target_dana' => 50000000,
            'benefit_sponsor' => 'Logo pada materi publikasi dan laporan kegiatan.',
            'tanggal_pelaksanaan' => now()->addDays(20),
            'nama_bank' => 'BCA',
            'nomor_rekening' => '8290123456',
            'atas_nama_rekening' => 'Komunitas Nusantara Peduli',
            'qris_image' => 'qris/desa-mandiri.png',
            'status' => 'Terverifikasi',
        ], $overrides));
    }

    private function createOtherOrganizer(): array
    {
        $user = User::factory()->create(['role' => 'organizer', 'status_verifikasi' => 'Disetujui']);
        $profile = OrganizerProfile::create([
            'user_id' => $user->id,
            'nama_organisasi' => 'Organisasi Sebelah',
            'jenis' => 'NGO',
            'kontak' => '085566778899',
        ]);

        return [$user, $profile];
    }

    private function createOtherSponsor(): array
    {
        $user = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);
        $profile = SponsorProfile::create([
            'user_id' => $user->id,
            'nama_perusahaan' => 'PT Kompetitor Lain',
            'kontak' => '087788990011',
        ]);

        return [$user, $profile];
    }

    // =========================================================================
    // POSITIVE END-TO-END FLOWS
    // =========================================================================

    public function test_e2e_full_lifecycle_from_proposal_to_invoice_pdf_and_financial_report(): void
    {
        // 1. Organizer membuat proposal + Bank + QRIS lalu langsung mengajukan verifikasi
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'nama_bank' => 'Mandiri',
                'nomor_rekening' => '1400098765432',
                'atas_nama_rekening' => 'Komunitas Nusantara Peduli',
                'qris_image' => UploadedFile::fake()->image('qris-ekspedisi.png', 500, 500),
                'action' => 'ajukan',
            ]))
            ->assertRedirect(route('organizer.proposals'));

        $event = Event::where('judul', 'Ekspedisi Air Bersih Desa')->firstOrFail();
        $this->assertSame('Menunggu Verifikasi', $event->status);
        Storage::disk('public')->assertExists($event->qris_image);

        // 2. Admin meninjau proposal (melihat Bank & QRIS) dan menyetujuinya
        $this->actingAs($this->adminUser)
            ->get(route('admin.proposal.show', $event))
            ->assertOk()
            ->assertSee('Mandiri')
            ->assertSee('1400098765432')
            ->assertSee($event->qris_image);

        $this->actingAs($this->adminUser)
            ->post(route('admin.proposal.verifikasi', $event), ['status' => 'Disetujui'])
            ->assertRedirect(route('admin.proposal'));

        $this->assertSame('Terverifikasi', $event->fresh()->status);

        // 3. Sponsor menemukan event di katalog & halaman detail, melihat Rekening & QRIS, lalu transfer + upload bukti
        $this->actingAs($this->sponsorUser)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Ekspedisi Air Bersih Desa');

        $this->actingAs($this->sponsorUser)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('1400098765432')
            ->assertSee($event->qris_image);

        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 10000000,
                'bentuk_dukungan' => 'Dana Tunai',
                'catatan' => 'Transfer via Mandiri tanggal hari ini.',
                'bukti_pembayaran' => UploadedFile::fake()->image('bukti-mandiri.jpg'),
            ])
            ->assertSessionHas('komitmen_sukses', 10000000);

        $commitment = Commitment::where('event_id', $event->id)->firstOrFail();
        $this->assertSame(0, $event->fresh()->dana_terkumpul);
        $this->assertSame(0, $event->fresh()->mitra_count);

        // 4. Organizer mengecek bukti di Komitmen Masuk & memvalidasi nominal aktual Rp 10.000.000
        $this->actingAs($this->organizerUser)
            ->get(route('organizer.komitmen'))
            ->assertOk()
            ->assertSee('Lihat bukti pembayaran');

        $this->actingAs($this->organizerUser)
            ->get(route('files.show', ['pembayaran', $commitment->id]))
            ->assertOk();

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), [
                'nominal_tervalidasi' => 10000000,
                'catatan_organizer' => 'Dana Rp 10.000.000 telah diterima penuh.',
            ])
            ->assertSessionHas('success');

        $commitment->refresh();
        $this->assertSame('Diterima', $commitment->status);
        $this->assertSame('Terverifikasi', $commitment->status_pembayaran);
        $this->assertStringStartsWith('INV-SPO-', $commitment->nomor_invoice);
        $this->assertSame(10000000, $event->fresh()->dana_terkumpul);
        $this->assertSame(1, $event->fresh()->mitra_count);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->sponsorUser->id,
            'url' => route('sponsor.inbox.show', $commitment),
        ]);

        // 5. Sponsor membuka Inbox, detail pesan Invoice, dan mendownload Invoice PDF yang sah
        $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox'))
            ->assertOk()
            ->assertSee($commitment->nomor_invoice)
            ->assertSee('Rp 10.000.000');

        $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.show', $commitment))
            ->assertOk()
            ->assertSee($commitment->nomor_invoice)
            ->assertSee('Dana Rp 10.000.000 telah diterima penuh.');

        $pdfRes = $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.download', $commitment));
        $pdfRes->assertOk();
        $pdfRes->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-1.4', $pdfRes->getContent());
        $this->assertStringContainsString($commitment->nomor_invoice, $pdfRes->getContent());
        $this->assertStringContainsString('Rp 10.000.000', $pdfRes->getContent());

        // 6. Organizer menandai event Selesai & mengirim Laporan Keuangan -> Sponsor sah dapat melihat laporan
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.selesai', $event))
            ->assertSessionHas('success');

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.laporan.keuangan', $event), [
                'aksi' => 'kirim',
                'items' => [
                    [
                        'uraian' => 'Pengeboran Sumur Bor Utama',
                        'nominal' => 10000000,
                        'bukti' => UploadedFile::fake()->create('nota-sumur.pdf', 150, 'application/pdf'),
                    ],
                ],
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.laporan.show', $event))
            ->assertOk()
            ->assertSee('Pengeboran Sumur Bor Utama');
    }

    public function test_e2e_proposal_payment_method_variants_and_qris_replacement(): void
    {
        // Buat proposal QRIS saja
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'judul' => 'Event QRIS Pertama',
                'qris_image' => UploadedFile::fake()->image('qris-v1.png', 300, 300),
            ]))
            ->assertRedirect(route('organizer.proposals'));

        $event = Event::where('judul', 'Event QRIS Pertama')->firstOrFail();
        $oldQris = $event->qris_image;
        Storage::disk('public')->assertExists($oldQris);

        // Ganti gambar QRIS baru -> file QRIS lama harus terhapus
        $this->actingAs($this->organizerUser)
            ->put(route('organizer.proposals.update', $event), $this->baseProposalPayload([
                'judul' => 'Event QRIS Pertama',
                'qris_image' => UploadedFile::fake()->image('qris-v2.png', 300, 300),
            ]))
            ->assertRedirect(route('organizer.proposals'));

        $newQris = $event->fresh()->qris_image;
        $this->assertNotSame($oldQris, $newQris);
        Storage::disk('public')->assertMissing($oldQris);
        Storage::disk('public')->assertExists($newQris);

        // Beralih dari QRIS ke Rekening Bank (centang hapus_qris) -> berhasil & file QRIS dihapus
        $this->actingAs($this->organizerUser)
            ->put(route('organizer.proposals.update', $event), $this->baseProposalPayload([
                'judul' => 'Event QRIS Pertama',
                'hapus_qris' => '1',
                'nama_bank' => 'BNI',
                'nomor_rekening' => '0987654321',
                'atas_nama_rekening' => 'Komunitas Nusantara Peduli',
            ]))
            ->assertRedirect(route('organizer.proposals'));

        $event->refresh();
        $this->assertNull($event->qris_image);
        $this->assertSame('BNI', $event->nama_bank);
        Storage::disk('public')->assertMissing($newQris);
    }

    // =========================================================================
    // NEGATIVE & ANTI-FRAUD TEST CASES (DIRANCANG UNTUK GAGAL)
    // =========================================================================

    public function test_fail_proposal_empty_both_bank_and_qris_and_invalid_inputs(): void
    {
        // 1. Kosong Bank dan QRIS sekaligus -> gagal
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload())
            ->assertSessionHasErrors('metode_pembayaran');

        // 2. Bank parsial (hanya nama_bank) -> gagal
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'nama_bank' => 'BCA',
            ]))
            ->assertSessionHasErrors(['nomor_rekening', 'atas_nama_rekening']);

        // 3. Nomor rekening mengandung karakter terlarang / XSS -> gagal regex
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'nama_bank' => 'BCA',
                'nomor_rekening' => '<script>alert(1)</script>',
                'atas_nama_rekening' => 'Organizer',
            ]))
            ->assertSessionHasErrors('nomor_rekening');

        // 4. File QRIS palsu (.php) dan melebihi 2 MB -> gagal
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'qris_image' => UploadedFile::fake()->create('shell.php', 50, 'text/x-php'),
            ]))
            ->assertSessionHasErrors('qris_image');

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'qris_image' => UploadedFile::fake()->image('huge.png')->size(3000),
            ]))
            ->assertSessionHasErrors('qris_image');

        $this->assertSame(0, Event::count());

        // 5. Edit proposal QRIS dengan hapus_qris=1 tanpa mengisi bank -> gagal
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.proposals.store'), $this->baseProposalPayload([
                'qris_image' => UploadedFile::fake()->image('valid-qris.png', 200, 200),
            ]))
            ->assertRedirect(route('organizer.proposals'));

        $event = Event::firstOrFail();
        $this->actingAs($this->organizerUser)
            ->put(route('organizer.proposals.update', $event), $this->baseProposalPayload([
                'hapus_qris' => '1',
            ]))
            ->assertSessionHasErrors('metode_pembayaran');
        $this->assertNotNull($event->fresh()->qris_image);
    }

    public function test_fraud_fail_organizer_bait_and_switch_bank_after_admin_approval(): void
    {
        $event = $this->createVerifiedEvent();

        // Organizer curang mencoba mengubah rekening menjadi rekening pribadi setelah Admin memverifikasi proposal
        $this->actingAs($this->organizerUser)
            ->get(route('organizer.proposals.edit', $event))
            ->assertForbidden();

        $this->actingAs($this->organizerUser)
            ->put(route('organizer.proposals.update', $event), $this->baseProposalPayload([
                'nama_bank' => 'BCA',
                'nomor_rekening' => '9999999999',
                'atas_nama_rekening' => 'Rekening Pribadi Penipu',
            ]))
            ->assertForbidden();

        $fresh = $event->fresh();
        $this->assertSame('8290123456', $fresh->nomor_rekening);
        $this->assertSame('Komunitas Nusantara Peduli', $fresh->atas_nama_rekening);
    }

    public function test_fraud_fail_sponsor_nominal_markup_corrected_by_organizer(): void
    {
        $event = $this->createVerifiedEvent(['target_dana' => 50000000]);

        // Sponsor nakal mengklaim Rp 50.000.000 (100% target) padahal mutasi transfer asli hanya Rp 150.000
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 50000000,
                'bentuk_dukungan' => 'Dana Tunai',
                'bukti_pembayaran' => UploadedFile::fake()->image('struk-asli-150rb.jpg'),
            ])
            ->assertSessionHas('komitmen_sukses');

        $commitment = Commitment::firstOrFail();

        // Sebelum validasi Organizer: progres dana harus masih 0% dan invoice belum ada (404)
        $this->assertSame(0, $event->fresh()->dana_terkumpul);
        $this->assertSame(0, $event->fresh()->progress);
        $this->assertSame(0, $event->fresh()->mitra_count);
        $this->actingAs($this->sponsorUser)->get(route('sponsor.inbox.show', $commitment))->assertNotFound();
        $this->actingAs($this->sponsorUser)->get(route('sponsor.inbox.download', $commitment))->assertNotFound();

        // Organizer mengecek mutasi dan memvalidasi hanya Rp 150.000
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), [
                'nominal_tervalidasi' => 150000,
                'catatan_organizer' => 'Mutasi masuk hanya Rp 150.000, bukan Rp 50.000.000.',
            ])
            ->assertSessionHas('success');

        $commitment->refresh();
        $this->assertSame(50000000, $commitment->nominal_klaim);
        $this->assertSame(150000, $commitment->nominal_tervalidasi);
        $this->assertSame(150000, $commitment->nominal);
        $this->assertSame(150000, $event->fresh()->dana_terkumpul);

        // Invoice & PDF wajib mencantumkan nilai hasil validasi Rp 150.000 + peringatan penyesuaian
        $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.show', $commitment))
            ->assertOk()
            ->assertSee('Rp 150.000')
            ->assertSee('Rp 50.000.000')
            ->assertSee('Penyesuaian Nominal');

        $pdfBody = $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.download', $commitment))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('Rp 150.000', $pdfBody);
        $this->assertStringContainsString('CATATAN PENYESUAIAN NOMINAL', $pdfBody);
    }

    public function test_fraud_fail_fake_transfer_validated_zero_gets_no_funding_or_report_access(): void
    {
        $event = $this->createVerifiedEvent();

        // Sponsor mengunggah bukti transfer fiktif dengan klaim Rp 20.000.000
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 20000000,
                'bukti_pembayaran' => UploadedFile::fake()->image('editan-fiktif.png'),
            ])
            ->assertSessionHas('komitmen_sukses');

        $commitment = Commitment::firstOrFail();

        // Organizer tidak menemukan dana masuk -> validasi Rp 0
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), [
                'nominal_tervalidasi' => 0,
                'catatan_organizer' => 'Bukti transfer fiktif / tidak ada dana masuk di mutasi.',
            ])
            ->assertSessionHas('success');

        $commitment->refresh();
        $this->assertSame(0, $commitment->nominal);
        $this->assertSame(0, $commitment->nominal_tervalidasi);
        $this->assertSame(0, $event->fresh()->dana_terkumpul);
        $this->assertSame(0, $event->fresh()->mitra_count);

        // Invoice tercatat Rp 0
        $pdfBody = $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.download', $commitment))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('Rp 0', $pdfBody);

        // Organizer menandai event Selesai & membuat Laporan Keuangan berisi nota privat
        $event->update([
            'status' => 'Selesai',
            'tanggal_selesai' => today(),
            'batas_laporan' => today()->addDays(14),
        ]);
        $report = Report::create([
            'event_id' => $event->id,
            'jenis' => 'keuangan',
            'status' => 'Dikirim',
            'tanggal_kirim' => now(),
        ]);
        Storage::disk('local')->put('bukti/nota-rahasia.pdf', 'pdf');
        $item = ReportItem::create([
            'report_id' => $report->id,
            'uraian' => 'Pembelian Logistik',
            'nominal' => 500000,
            'bukti_berkas' => 'bukti/nota-rahasia.pdf',
        ]);

        // Sponsor fiktif (Rp 0) DILARANG melihat/merevisi laporan & mengintip nota privat
        $this->actingAs($this->sponsorUser)->get(route('sponsor.laporan.show', $event))->assertForbidden();
        $this->actingAs($this->sponsorUser)->post(route('sponsor.laporan.tanggapi', $report), [
            'status' => 'Perlu Revisi',
            'catatan' => 'Ganggu laporan',
        ])->assertForbidden();
        $this->actingAs($this->sponsorUser)->get(route('files.show', ['bukti', $item->id]))->assertForbidden();

        // Event tidak muncul di Laporan Program Sponsor maupun Riwayat Organizer
        $this->actingAs($this->sponsorUser)->get(route('sponsor.laporan.index'))->assertOk()->assertDontSee($event->judul);
        $this->actingAs($this->organizerUser)->get(route('organizer.riwayat'))->assertOk()->assertDontSee($this->sponsor->nama_perusahaan);
    }

    public function test_fraud_fail_sponsor_mass_assignment_self_approval_payload(): void
    {
        $event = $this->createVerifiedEvent();

        // Sponsor mencoba menyuntikkan field sensitif untuk mengesahkan donasi & invoice sendiri
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 500000,
                'bentuk_dukungan' => 'Dana Tunai',
                'bukti_pembayaran' => UploadedFile::fake()->image('tf.jpg'),
                'status' => 'Diterima',
                'status_pembayaran' => 'Terverifikasi',
                'nominal_tervalidasi' => 99999999,
                'nomor_invoice' => 'INV-HACK-9999',
                'tanggal_validasi' => now()->toDateTimeString(),
            ])
            ->assertSessionHas('komitmen_sukses');

        $c = Commitment::firstOrFail();
        $this->assertSame('Menunggu Tanggapan', $c->status);
        $this->assertSame('Menunggu Verifikasi', $c->status_pembayaran);
        $this->assertNull($c->nominal_tervalidasi);
        $this->assertNull($c->nomor_invoice);
        $this->assertNull($c->tanggal_validasi);
        $this->assertSame(0, $event->fresh()->dana_terkumpul);

        $this->actingAs($this->sponsorUser)->get(route('sponsor.inbox.show', $c))->assertNotFound();
    }

    public function test_fail_invalid_donation_submissions_and_unauthorized_states(): void
    {
        $event = $this->createVerifiedEvent();

        // 1. Tanpa bukti transfer -> gagal
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), ['nominal' => 100000])
            ->assertSessionHasErrors('bukti_pembayaran');

        // 2. Bukti format berbahaya (.exe) dan ukuran > 5MB -> gagal
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 100000,
                'bukti_pembayaran' => UploadedFile::fake()->create('malware.exe', 100),
            ])
            ->assertSessionHasErrors('bukti_pembayaran');

        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 100000,
                'bukti_pembayaran' => UploadedFile::fake()->create('oversize.pdf', 6000, 'application/pdf'),
            ])
            ->assertSessionHasErrors('bukti_pembayaran');

        // 3. Nominal negatif, nol, dan di bawah minimum Rp 10.000 -> gagal
        foreach ([-500000, 0, 5000] as $badNominal) {
            $this->actingAs($this->sponsorUser)
                ->post(route('sponsor.komitmen.store', $event), [
                    'nominal' => $badNominal,
                    'bukti_pembayaran' => UploadedFile::fake()->image('tf.jpg'),
                ])
                ->assertSessionHasErrors('nominal');
        }

        // 4. Sponsor belum diverifikasi Admin -> ditolak
        $unverifiedSponsor = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Menunggu']);
        SponsorProfile::create(['user_id' => $unverifiedSponsor->id, 'nama_perusahaan' => 'PT Belum Verif', 'kontak' => '0811']);
        $this->actingAs($unverifiedSponsor)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 100000,
                'bukti_pembayaran' => UploadedFile::fake()->image('tf.jpg'),
            ])
            ->assertSessionHasErrors('nominal');

        // 5. Event belum tayang / sudah selesai -> 403 Forbidden
        foreach (['Draft', 'Menunggu Verifikasi', 'Perlu Revisi', 'Selesai'] as $badStatus) {
            $blockedEvent = $this->createVerifiedEvent(['judul' => "Event {$badStatus}", 'status' => $badStatus]);
            $this->actingAs($this->sponsorUser)
                ->post(route('sponsor.komitmen.store', $blockedEvent), [
                    'nominal' => 100000,
                    'bukti_pembayaran' => UploadedFile::fake()->image('tf.jpg'),
                ])
                ->assertForbidden();
        }

        // 6. Duplikat donasi di event yang sama oleh Sponsor yang sama -> ditolak
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 100000,
                'bukti_pembayaran' => UploadedFile::fake()->image('tf1.jpg'),
            ])
            ->assertSessionHas('komitmen_sukses');

        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 200000,
                'bukti_pembayaran' => UploadedFile::fake()->image('tf2.jpg'),
            ])
            ->assertSessionHasErrors('nominal');

        $this->assertSame(1, Commitment::count());
    }

    public function test_fraud_fail_privilege_escalation_and_idor_on_validation(): void
    {
        $event = $this->createVerifiedEvent();
        $commitment = Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsor->id,
            'nominal' => 2000000,
            'nominal_klaim' => 2000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Menunggu Verifikasi',
            'bukti_pembayaran' => 'pembayaran/valid.jpg',
            'tanggal_bukti' => now(),
        ]);

        $url = route('organizer.komitmen.validasi', $commitment);

        // Guest -> redirect ke login
        $this->post($url, ['nominal_tervalidasi' => 2000000])->assertRedirect(route('login'));

        // Sponsor mencoba memvalidasi donasinya sendiri -> 403
        $this->actingAs($this->sponsorUser)->post($url, ['nominal_tervalidasi' => 2000000])->assertForbidden();

        // Admin mencoba memanggil endpoint validasi khusus Organizer -> 403
        $this->actingAs($this->adminUser)->post($url, ['nominal_tervalidasi' => 2000000])->assertForbidden();

        // Organizer lain (IDOR) mencoba memvalidasi event milik Organizer A -> 403
        [$otherOrgUser] = $this->createOtherOrganizer();
        $this->actingAs($otherOrgUser)->post($url, ['nominal_tervalidasi' => 2000000])->assertForbidden();

        $this->assertSame('Menunggu Verifikasi', $commitment->fresh()->status_pembayaran);
        $this->assertNull($commitment->fresh()->nomor_invoice);
    }

    public function test_fraud_fail_replay_double_validation_no_proof_and_negative_amount(): void
    {
        $event = $this->createVerifiedEvent();

        // 1. Komitmen tanpa bukti pembayaran tidak boleh bisa divalidasi paksa
        $noProofCommitment = Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsor->id,
            'nominal' => 1000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Belum Dibayar',
            'bukti_pembayaran' => null,
        ]);

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $noProofCommitment), ['nominal_tervalidasi' => 1000000])
            ->assertForbidden();

        // 2. Nominal validasi negatif atau non-numerik harus gagal validasi
        $commitment = Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsor->id,
            'nominal' => 5000000,
            'nominal_klaim' => 5000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Menunggu Verifikasi',
            'bukti_pembayaran' => 'pembayaran/tf.jpg',
            'tanggal_bukti' => now(),
        ]);

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), ['nominal_tervalidasi' => -100000])
            ->assertSessionHasErrors('nominal_tervalidasi');

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), ['nominal_tervalidasi' => 'lima juta'])
            ->assertSessionHasErrors('nominal_tervalidasi');

        // 3. Validasi pertama berhasil, replay attack kedua untuk mengubah nominal/invoice harus ditolak (403)
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), ['nominal_tervalidasi' => 5000000])
            ->assertSessionHas('success');

        $firstInvoice = $commitment->fresh()->nomor_invoice;

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), ['nominal_tervalidasi' => 9000000])
            ->assertForbidden();

        $this->assertSame(5000000, $commitment->fresh()->nominal);
        $this->assertSame($firstInvoice, $commitment->fresh()->nomor_invoice);
    }

    public function test_fraud_fail_evidence_tampering_and_invoice_proof_idor(): void
    {
        $event = $this->createVerifiedEvent();
        Storage::disk('local')->put('pembayaran/asli.jpg', 'fake-jpg');

        $commitment = Commitment::create([
            'event_id' => $event->id,
            'sponsor_id' => $this->sponsor->id,
            'nominal' => 3000000,
            'nominal_klaim' => 3000000,
            'bentuk_dukungan' => 'Dana Tunai',
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Menunggu Verifikasi',
            'bukti_pembayaran' => 'pembayaran/asli.jpg',
            'tanggal_bukti' => now(),
        ]);

        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), ['nominal_tervalidasi' => 3000000])
            ->assertSessionHas('success');

        // 1. Sponsor mencoba mengganti bukti transfer setelah terverifikasi -> 403
        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.bukti', $commitment), [
                'bukti' => UploadedFile::fake()->image('palsu-baru.jpg'),
            ])
            ->assertForbidden();
        $this->assertSame('pembayaran/asli.jpg', $commitment->fresh()->bukti_pembayaran);

        // 2. Sponsor lain mencoba mengintip Inbox, Invoice PDF, atau file bukti transfer milik Sponsor A -> 403
        [$otherSponsorUser] = $this->createOtherSponsor();
        $this->actingAs($otherSponsorUser)->get(route('sponsor.inbox.show', $commitment))->assertForbidden();
        $this->actingAs($otherSponsorUser)->get(route('sponsor.inbox.download', $commitment))->assertForbidden();
        $this->actingAs($otherSponsorUser)->get(route('files.show', ['pembayaran', $commitment->id]))->assertForbidden();

        // 3. Organizer lain mencoba mengintip file bukti transfer event milik Organizer A -> 403
        [$otherOrgUser] = $this->createOtherOrganizer();
        $this->actingAs($otherOrgUser)->get(route('files.show', ['pembayaran', $commitment->id]))->assertForbidden();
    }

    public function test_security_xss_and_pdf_injection_payloads_safely_neutralized(): void
    {
        $event = $this->createVerifiedEvent();
        $xssNote = "<script>alert('xss')</script>";
        $pdfAndXssPayload = "<script>alert('inv')</script> )(/JavaScript (alert))";

        $this->actingAs($this->sponsorUser)
            ->post(route('sponsor.komitmen.store', $event), [
                'nominal' => 1500000,
                'catatan' => $xssNote,
                'bukti_pembayaran' => UploadedFile::fake()->image('tf.jpg'),
            ])
            ->assertSessionHas('komitmen_sukses');

        $commitment = Commitment::firstOrFail();

        // Organizer melihat halaman komitmen: script XSS wajib ter-escape
        $this->actingAs($this->organizerUser)
            ->get(route('organizer.komitmen'))
            ->assertOk()
            ->assertDontSee($xssNote, false)
            ->assertSee('&lt;script&gt;', false);

        // Validasi dengan payload XSS & PDF operator injection
        $this->actingAs($this->organizerUser)
            ->post(route('organizer.komitmen.validasi', $commitment), [
                'nominal_tervalidasi' => 1500000,
                'catatan_organizer' => $pdfAndXssPayload,
            ])
            ->assertSessionHas('success');

        // Halaman detail Inbox wajib meng-escape tag <script>
        $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.show', $commitment))
            ->assertOk()
            ->assertDontSee("<script>alert('inv')</script>", false)
            ->assertSee('&lt;script&gt;', false);

        // File PDF tetap valid (%PDF-1.4) dan karakter ( ) ter-escape aman
        $pdfBody = $this->actingAs($this->sponsorUser)
            ->get(route('sponsor.inbox.download', $commitment))
            ->assertOk()
            ->getContent();
        $this->assertStringStartsWith('%PDF-1.4', $pdfBody);
        $this->assertStringContainsString('\\)\\(/JavaScript \\(alert\\)\\)', $pdfBody);
    }
}
