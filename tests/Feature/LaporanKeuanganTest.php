<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Report;
use App\Models\ReportItem;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaporanKeuanganTest extends TestCase
{
    use RefreshDatabase;

    private User $organizerUser;
    private OrganizerProfile $organizerProfile;
    private User $sponsorUser;
    private SponsorProfile $sponsorProfile;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->organizerUser = User::factory()->create([
            'role' => 'organizer',
            'status_verifikasi' => 'Terverifikasi',
        ]);
        $this->organizerProfile = OrganizerProfile::create([
            'user_id' => $this->organizerUser->id,
            'nama_organisasi' => 'BEM Kampus Harapan',
            'jenis' => 'HIMA/UKM',
            'kontak' => '08123456789',
            'alamat' => 'Kampus Terpadu',
        ]);

        $this->sponsorUser = User::factory()->create([
            'role' => 'sponsor',
            'status_verifikasi' => 'Terverifikasi',
        ]);
        $this->sponsorProfile = SponsorProfile::create([
            'user_id' => $this->sponsorUser->id,
            'nama_perusahaan' => 'PT Nusantara Sejahtera',
            'nib' => '1234567890123',
            'fokus_csr' => 'Pendidikan',
            'kontak' => '08129876543',
        ]);

        $this->category = Category::create([
            'nama_kategori' => 'Pendidikan',
        ]);
    }

    private function createFinishedEvent(): Event
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
            'status' => 'Selesai',
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

    public function test_organizer_can_view_laporan_index_and_show(): void
    {
        $event = $this->createFinishedEvent();

        $indexRes = $this->actingAs($this->organizerUser)->get(route('organizer.laporan.index'));
        $indexRes->assertOk();
        $indexRes->assertSee('Pelatihan Coding untuk Pelajar');

        $showRes = $this->actingAs($this->organizerUser)->get(route('organizer.laporan.show', $event));
        $showRes->assertOk();
        $showRes->assertSee('Laporan Keuangan Transparan');
    }

    public function test_organizer_cannot_manage_laporan_for_unfinished_event(): void
    {
        $unfinishedEvent = Event::create([
            'organizer_id' => $this->organizerProfile->id,
            'category_id' => $this->category->id,
            'judul' => 'Event Belum Selesai',
            'deskripsi' => 'Masih berjalan.',
            'lokasi' => 'Bandung',
            'target_dana' => 5000000,
            'benefit_sponsor' => 'Logo',
            'tanggal_pelaksanaan' => now()->addDays(10),
            'status' => 'Terverifikasi',
        ]);

        $res = $this->actingAs($this->organizerUser)->get(route('organizer.laporan.show', $unfinishedEvent));
        $res->assertForbidden();
    }

    public function test_organizer_can_save_draft_laporan_keuangan_with_receipt(): void
    {
        $event = $this->createFinishedEvent();
        $receipt = UploadedFile::fake()->create('nota_konsumsi.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->organizerUser)->post(route('organizer.laporan.keuangan', $event), [
            'aksi' => 'draft',
            'items' => [
                [
                    'uraian' => 'Konsumsi 50 Peserta Pelatihan',
                    'nominal' => 2500000,
                    'bukti' => $receipt,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'event_id' => $event->id,
            'jenis' => 'keuangan',
            'status' => 'Draft',
        ]);

        $this->assertDatabaseHas('report_items', [
            'uraian' => 'Konsumsi 50 Peserta Pelatihan',
            'nominal' => 2500000,
        ]);
    }

    public function test_organizer_can_send_laporan_keuangan_to_sponsors(): void
    {
        $event = $this->createFinishedEvent();
        $receipt = UploadedFile::fake()->create('nota_sound.png', 300, 'image/png');

        $response = $this->actingAs($this->organizerUser)->post(route('organizer.laporan.keuangan', $event), [
            'aksi' => 'kirim',
            'items' => [
                [
                    'uraian' => 'Sewa Modul & Komputer',
                    'nominal' => 7000000,
                    'bukti' => $receipt,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'event_id' => $event->id,
            'jenis' => 'keuangan',
            'status' => 'Dikirim',
        ]);
    }

    public function test_sponsor_can_view_laporan_and_verify_items(): void
    {
        $event = $this->createFinishedEvent();
        $report = Report::create([
            'event_id' => $event->id,
            'jenis' => 'keuangan',
            'status' => 'Dikirim',
            'tanggal_kirim' => now(),
        ]);
        $item1 = ReportItem::create([
            'report_id' => $report->id,
            'uraian' => 'Pengadaan Modul Belajar',
            'nominal' => 4000000,
            'bukti_berkas' => 'bukti/fake_nota.jpg',
        ]);
        $item2 = ReportItem::create([
            'report_id' => $report->id,
            'uraian' => 'Honorarium Mentor',
            'nominal' => 3000000,
            'bukti_berkas' => 'bukti/fake_mentor.jpg',
        ]);

        // Sponsor view laporan show
        $viewRes = $this->actingAs($this->sponsorUser)->get(route('sponsor.laporan.show', $event));
        $viewRes->assertOk();
        $viewRes->assertSee('Pengadaan Modul Belajar');
        $viewRes->assertSee('Honorarium Mentor');
        $viewRes->assertSee('Rp 7.000.000'); // total pengeluaran

        // Sponsor verifies items and approves report
        $postRes = $this->actingAs($this->sponsorUser)->post(route('sponsor.laporan.tanggapi', $report), [
            'status' => 'Diterima',
            'catatan' => 'Laporan keuangan sangat rapi dan transparan. Terima kasih!',
            'items_review' => [
                $item1->id => ['status' => 'Valid', 'catatan' => 'Sesuai'],
                $item2->id => ['status' => 'Valid', 'catatan' => 'Sesuai bukti'],
            ],
        ]);

        $postRes->assertSessionHas('success');

        $this->assertDatabaseHas('report_reviews', [
            'report_id' => $report->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'status' => 'Diterima',
        ]);

        $this->assertDatabaseHas('report_item_reviews', [
            'report_item_id' => $item1->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'status' => 'Valid',
        ]);

        $this->assertEquals('Diterima', $report->fresh()->status);
    }

    public function test_non_funding_sponsor_cannot_view_laporan(): void
    {
        $event = $this->createFinishedEvent();
        $otherSponsorUser = User::factory()->create([
            'role' => 'sponsor',
            'status_verifikasi' => 'Terverifikasi',
        ]);
        SponsorProfile::create([
            'user_id' => $otherSponsorUser->id,
            'nama_perusahaan' => 'PT Lainnya',
            'nib' => '9999999999999',
            'fokus_csr' => 'Kesehatan',
            'kontak' => '0899999999',
        ]);

        $res = $this->actingAs($otherSponsorUser)->get(route('sponsor.laporan.show', $event));
        $res->assertForbidden();
    }

    public function test_organizer_input_is_retained_when_missing_proof_file(): void
    {
        $event = $this->createFinishedEvent();

        $response = $this->actingAs($this->organizerUser)
            ->from(route('organizer.laporan.show', $event))
            ->post(route('organizer.laporan.keuangan', $event), [
                'aksi' => 'kirim',
                'items' => [
                    [
                        'uraian' => 'Konsumsi 50 Relawan & Panitia',
                        'nominal' => 1500000,
                    ],
                    [
                        'uraian' => 'Sewa Sound System & Genset',
                        'nominal' => 3000000,
                    ],
                ],
            ]);

        $response->assertRedirect(route('organizer.laporan.show', $event));
        $response->assertSessionHas('error', 'Setiap rincian pengeluaran wajib memiliki bukti.');
        $this->assertTrue(session()->hasOldInput('items'));
        $this->assertEquals('Konsumsi 50 Relawan & Panitia', session()->getOldInput('items.0.uraian'));
        $this->assertEquals(1500000, session()->getOldInput('items.0.nominal'));

        // Buka kembali halaman laporan dan pastikan nilai uraian & nominal tidak ter-reset
        $viewRes = $this->actingAs($this->organizerUser)
            ->get(route('organizer.laporan.show', $event));

        $viewRes->assertOk();
        $viewRes->assertSee('Konsumsi 50 Relawan', false);
        $viewRes->assertSee('1500000');
        $viewRes->assertSee('Sewa Sound System', false);
        $viewRes->assertSee('3000000');
    }

    public function test_organizer_sees_per_item_feedback_when_revision_requested(): void
    {
        $event = $this->createFinishedEvent();
        $report = Report::create([
            'event_id' => $event->id,
            'jenis' => 'keuangan',
            'status' => 'Perlu Revisi',
            'tanggal_kirim' => now(),
        ]);
        $item = ReportItem::create([
            'report_id' => $report->id,
            'uraian' => 'Konsumsi Panitia',
            'nominal' => 1000000,
            'bukti_berkas' => 'bukti/nota_konsumsi.jpg',
        ]);
        \App\Models\ReportItemReview::create([
            'report_item_id' => $item->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'status' => 'Kurang Bukti',
            'catatan' => 'Nota terpotong, tolong lampirkan kwitansi bermaterai',
        ]);

        $res = $this->actingAs($this->organizerUser)->get(route('organizer.laporan.show', $event));
        $res->assertOk();
        $res->assertSee('PT Nusantara Sejahtera');
        $res->assertSee('Kurang Bukti');
        $res->assertSee('Nota terpotong, tolong lampirkan kwitansi bermaterai');
    }

    public function test_organizer_preserves_item_id_and_reviews_when_updating_keuangan(): void
    {
        $event = $this->createFinishedEvent();
        $report = Report::create([
            'event_id' => $event->id,
            'jenis' => 'keuangan',
            'status' => 'Perlu Revisi',
            'tanggal_kirim' => now(),
        ]);
        $item1 = ReportItem::create([
            'report_id' => $report->id,
            'uraian' => 'Item Tetap Diperbarui',
            'nominal' => 1000000,
            'bukti_berkas' => 'bukti/item1.jpg',
        ]);
        $item2 = ReportItem::create([
            'report_id' => $report->id,
            'uraian' => 'Item Dihapus',
            'nominal' => 500000,
            'bukti_berkas' => 'bukti/item2.jpg',
        ]);

        $review = \App\Models\ReportItemReview::create([
            'report_item_id' => $item1->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'status' => 'Kurang Bukti',
            'catatan' => 'Perlu nota baru',
        ]);

        $newReceipt = UploadedFile::fake()->create('nota_baru.png', 200, 'image/png');
        $newReceiptItem3 = UploadedFile::fake()->create('nota_item3.png', 200, 'image/png');

        $response = $this->actingAs($this->organizerUser)->post(route('organizer.laporan.keuangan', $event), [
            'aksi' => 'draft',
            'items' => [
                [
                    'id' => $item1->id,
                    'uraian' => 'Item Tetap Diperbarui (Updated)',
                    'nominal' => 1200000,
                    'bukti' => $newReceipt,
                ],
                [
                    'uraian' => 'Item Baru Ditambahkan',
                    'nominal' => 800000,
                    'bukti' => $newReceiptItem3,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        // Pastikan Item 1 mempertahankan ID aslinya
        $this->assertDatabaseHas('report_items', [
            'id' => $item1->id,
            'uraian' => 'Item Tetap Diperbarui (Updated)',
            'nominal' => 1200000,
        ]);

        // Pastikan review sponsor untuk Item 1 tidak hilang karena cascade delete
        $this->assertDatabaseHas('report_item_reviews', [
            'id' => $review->id,
            'report_item_id' => $item1->id,
            'sponsor_id' => $this->sponsorProfile->id,
            'status' => 'Kurang Bukti',
        ]);

        // Pastikan Item 2 yang dibuang benar-benar terhapus
        $this->assertDatabaseMissing('report_items', [
            'id' => $item2->id,
        ]);

        // Pastikan Item Baru berhasil dibuat
        $this->assertDatabaseHas('report_items', [
            'report_id' => $report->id,
            'uraian' => 'Item Baru Ditambahkan',
            'nominal' => 800000,
        ]);
    }
}
