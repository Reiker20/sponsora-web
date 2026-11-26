<?php

namespace App\Http\Controllers;

use App\Models\Commitment;
use App\Models\Event;
use App\Models\Notifikasi;
use App\Models\Report;
use App\Models\BankAccount;
use App\Models\Payment;
use App\Models\ReportReview;
use App\Support\SimpleInvoicePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SponsorController extends Controller
{
    private function me()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->sponsorProfile;
    }
    // private function me() { return Auth::user()->sponsorProfile; }
    // private function me() { return auth()->user()->sponsorProfile; }

    public function dashboard()
    {
        $commitments = Commitment::where('sponsor_id', $this->me()->id)
            ->with(['event' => fn ($q) => $q->withFunding(), 'event.organizer', 'latestPayment'])->latest()->get();
            // ->with(['event' => fn ($q) => $q->withFunding(), 'event.organizer'])->latest()->get(); 

        return view('sponsor.dashboard', [
            'commitments' => $commitments,
            'stats' => [
                'total' => $commitments->count(),
                'diterima' => $commitments->where('status', 'Diterima')->where('nominal', '>', 0)->count(),
                // 'berjalan' => $commitments->whereIn('status', ['Menunggu Tanggapan', 'Negosiasi'])->count(),
                'berjalan' => $commitments->where('status', 'Menunggu Tanggapan')->count(),
                'dana' => $commitments->where('status', 'Diterima')->sum('nominal'),
            ],
        ]);
    }

    /** FR-SPO-07 / UC-15: Riwayat Kemitraan Sponsorship CSR */
    public function riwayat(Request $request)
    {
        $status = $request->query('status');

        $query = Commitment::where('sponsor_id', $this->me()->id)
            ->with(['event' => fn ($q) => $q->withFunding(), 'event.organizer', 'event.category', 'event.reports'])
            ->latest();

        // if ($status && in_array($status, ['Menunggu Tanggapan', 'Diterima', 'Negosiasi', 'Ditolak'], true)) {
        if ($status && in_array($status, ['Menunggu Tanggapan', 'Diterima'], true)) {
            $query->where('status', $status);
        }

        $allCommitments = Commitment::where('sponsor_id', $this->me()->id)
            ->with(['event.organizer'])
            ->get();

        $commitments = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => $allCommitments->count(),
            'diterima' => $allCommitments->where('status', 'Diterima')->where('nominal', '>', 0)->count(),
            'dana' => $allCommitments->where('status', 'Diterima')->sum('nominal'),
            'organizer' => $allCommitments->where('status', 'Diterima')->where('nominal', '>', 0)->pluck('event.organizer_id')->unique()->count(),
        ];

        return view('sponsor.riwayat', [
            'commitments' => $commitments,
            'stats' => $stats,
            'currentStatus' => $status,
        ]);
    }
    public function profil() { return view('sponsor.profil', ['user' => Auth::user(), 'p' => $this->me()]); }
    // public function profil() { return view('sponsor.profil', ['user' => auth()->user(), 'p' => $this->me()]); }

    public function updateProfil(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'nama_perusahaan' => 'required|string|max:150',
            'kontak' => 'required|string|max:30',
            'alamat' => 'nullable|string|max:255',
            'fokus_csr' => 'required|array|min:1',
        ]);
        $request->user()->update(['name' => $data['name']]);
        $this->me()->update([
            'nama_perusahaan' => $data['nama_perusahaan'], 'kontak' => $data['kontak'],
            'alamat' => $data['alamat'] ?? null, 'fokus_csr' => implode(', ', $data['fokus_csr']),
        ]);

        return back()->with('success', 'Profil perusahaan diperbarui.');
    }

    /** UC-13 Mengajukan komitmen dana */
    public function storeCommitment(Request $request, Event $event)
    {
        $user = $request->user();
        abort_unless($event->acceptsCommitments(), 403, 'Event ini tidak lagi menerima komitmen.');
        if (! $user->isVerified()) {
            return back()->withErrors(['nominal' => 'Akun Anda harus diverifikasi Admin sebelum mengajukan komitmen.']);
        }

        $aktif = $event->commitments()->where('sponsor_id', $this->me()->id)
            // ->whereIn('status', ['Menunggu Tanggapan', 'Negosiasi', 'Diterima'])->exists();
            ->whereIn('status', ['Menunggu Tanggapan', 'Diterima'])->exists();
        if ($aktif) {
            return back()->withErrors(['nominal' => 'Anda sudah memiliki komitmen aktif pada event ini.']);
        }

        $data = $request->validate([
            'nominal' => 'required|integer|min:10000|max:100000000000',
            'bentuk_dukungan' => 'nullable|string|max:60',
            'catatan' => 'nullable|string|max:500',
            'bukti_pembayaran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'bukti_pembayaran.required' => 'Bukti transfer wajib diunggah saat memberikan donasi.',
            'bukti_pembayaran.mimes' => 'Bukti transfer harus berformat PDF, JPG, atau PNG.',
            'bukti_pembayaran.max' => 'Ukuran bukti transfer maksimal 5 MB.',
        ]);

        $path = $request->file('bukti_pembayaran')->store('pembayaran', 'local');

        $event->commitments()->create([
            'sponsor_id' => $this->me()->id,
            'nominal' => $data['nominal'],
            'nominal_klaim' => $data['nominal'],
            'bentuk_dukungan' => $data['bentuk_dukungan'] ?? 'Dana Tunai',
            'catatan' => $data['catatan'] ?? null,
            'status' => 'Menunggu Tanggapan',
            'status_pembayaran' => 'Menunggu Verifikasi',
            'bukti_pembayaran' => $path,
            'tanggal_bukti' => now(),
        ]);

        Notifikasi::kirim($event->organizer->user_id,
            $this->me()->nama_perusahaan." mengirimkan bukti donasi untuk \"{$event->judul}\". Menunggu validasi nominal.", route('organizer.komitmen'));

        return back()->with('komitmen_sukses', $data['nominal']);
    }

    /** Scrum 73: Sponsor mengunggah bukti pembayaran atas komitmen yang sudah diterima Organizer */
    public function unggahBukti(Request $request, Commitment $commitment)
    {
        abort_unless($commitment->sponsor_id === $this->me()->id, 403);
        abort_unless($commitment->status === 'Diterima', 403, 'Bukti pembayaran hanya dapat diunggah untuk komitmen yang sudah diterima Organizer.');
        abort_if($commitment->status_pembayaran === 'Terverifikasi', 403, 'Pembayaran ini sudah diverifikasi dan tidak dapat diubah.');

        $data = $request->validate([
            'bukti' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'catatan_pembayaran' => 'nullable|string|max:300',
        ], [
            'bukti.required' => 'Pilih berkas bukti pembayaran terlebih dahulu.',
            'bukti.mimes' => 'Bukti harus berformat PDF, JPG, atau PNG.',
            'bukti.max' => 'Ukuran bukti maksimal 5 MB.',
        ]);

        if ($commitment->bukti_pembayaran) {
            Storage::delete($commitment->bukti_pembayaran);
        }

        $commitment->update([
            'bukti_pembayaran' => $request->file('bukti')->store('pembayaran'),
            'catatan_pembayaran' => $data['catatan_pembayaran'] ?? null,
            'status_pembayaran' => 'Menunggu Verifikasi',
            'tanggal_bukti' => now(),
        ]);

        Notifikasi::kirim(
            $commitment->event->organizer->user_id,
            $this->me()->nama_perusahaan." mengunggah bukti pembayaran untuk \"{$commitment->event->judul}\".",
            route('organizer.komitmen')
        );

        return back()->with('success', 'Bukti pembayaran terkirim. Menunggu verifikasi Organizer.');
    }

    /** Sponsor mengajukan ulang setelah Organizer memilih Negosiasi */
    // public function resubmit(Request $request, Commitment $commitment)
    // {
    //     abort_unless($commitment->sponsor_id === $this->me()->id && $commitment->status === 'Negosiasi', 403);
    //     abort_unless($commitment->event->acceptsCommitments(), 403);

    //     $data = $request->validate(['nominal' => 'required|integer|min:50000', 'catatan' => 'nullable|string|max:500']);
    //     $commitment->update($data + ['status' => 'Menunggu Tanggapan', 'tanggal_pengajuan' => now()]);
    //     Notifikasi::kirim($commitment->event->organizer->user_id,
    //         $this->me()->nama_perusahaan." mengajukan ulang komitmen pada \"{$commitment->event->judul}\".", route('organizer.komitmen'));

    //     return back()->with('success', 'Pengajuan ulang terkirim.');
    // }

    public function laporanIndex()
    {
        $ids = $this->me()->commitments()->where('status', 'Diterima')->where('nominal', '>', 0)->pluck('event_id');
        $events = Event::whereIn('id', $ids)->with(['organizer', 'reports'])->latest()->get();

        return view('sponsor.laporan_index', compact('events'));
    }

    public function laporanShow(Event $event)
    {
        abort_unless($event->commitments()->where('sponsor_id', $this->me()->id)->where('status', 'Diterima')->where('nominal', '>', 0)->exists(), 403);
        $event->load(['organizer', 'commitments' => fn ($q) => $q->where('status', 'Diterima')->where('nominal', '>', 0)]);
        $keuangan = $event->report('keuangan')?->load(['items.reviews' => fn ($q) => $q->where('sponsor_id', $this->me()->id)]);
        $hasil = $event->report('hasil_program');
        $reviews = ReportReview::where('sponsor_id', $this->me()->id)
            ->whereIn('report_id', array_filter([$keuangan?->id, $hasil?->id]))->get()->keyBy('report_id');

        return view('sponsor.laporan_show', [
            'event' => $event,
            'keuangan' => $keuangan?->isSent() ? $keuangan : null,
            'hasil' => $hasil?->isSent() ? $hasil : null,
            'reviews' => $reviews,
            'myCommitment' => $event->commitments->firstWhere('sponsor_id', $this->me()->id),
        ]);
    }

    /** UC-24 Menanggapi laporan */
    public function tanggapi(Request $request, Report $report)
    {
        $event = $report->event;
        abort_unless($report->status !== 'Draft'
            && $event->commitments()->where('sponsor_id', $this->me()->id)->where('status', 'Diterima')->where('nominal', '>', 0)->exists(), 403);

        $data = $request->validate([
            'status' => 'required|in:Diterima,Perlu Revisi',
            'catatan' => 'required_if:status,Perlu Revisi|nullable|string|max:1000',
        ], ['catatan.required_if' => 'Catatan wajib diisi saat meminta revisi.']);

        // Simpan penilaian verifikasi per item (khusus Laporan Keuangan)
        if ($report->jenis === 'keuangan' && $request->has('items_review')) {
            $itemsReview = $request->input('items_review', []);
            foreach ($itemsReview as $itemId => $itemData) {
                if ($report->items()->where('id', $itemId)->exists()) {
                    \App\Models\ReportItemReview::updateOrCreate(
                        [
                            'report_item_id' => $itemId,
                            'sponsor_id' => $this->me()->id,
                        ],
                        [
                            'status' => in_array($itemData['status'] ?? '', ['Valid', 'Perlu Revisi', 'Kurang Bukti'], true)
                                ? $itemData['status']
                                : 'Valid',
                            'catatan' => $itemData['catatan'] ?? null,
                        ]
                    );
                }
            }
        }

        ReportReview::updateOrCreate(
            ['report_id' => $report->id, 'sponsor_id' => $this->me()->id],
            $data + ['tanggal_tanggapan' => now()]
        );
        $report->refreshStatusFromReviews();

        $label = $report->jenis === 'keuangan' ? 'laporan keuangan' : 'laporan hasil program';
        Notifikasi::kirim($event->organizer->user_id,
            $this->me()->nama_perusahaan.' menanggapi '.$label." \"{$event->judul}\": {$data['status']}.", route('organizer.laporan.show', $event));

        return back()->with('success', 'Tanggapan dan verifikasi laporan tersimpan.');
    }

    // ---------- Pembayaran: unggah bukti transfer (SCRUM-73) ----------
    private function ownCommitment(Commitment $commitment): Commitment
    {
        abort_unless($commitment->sponsor_id === $this->me()->id, 403);

        return $commitment;
    }
    public function pembayaran(Commitment $commitment)
    {
        $this->ownCommitment($commitment)->load(['event.organizer', 'payments' => fn ($q) => $q->latest()]);

        return view('sponsor.pembayaran', [
            'commitment' => $commitment,
            'accounts' => BankAccount::active()->orderBy('nama_bank')->get(),
        ]);
    }
    public function kirimBukti(Request $request, Commitment $commitment)
    {
        $this->ownCommitment($commitment);

        if ($commitment->status !== 'Diterima') {
            return back()->with('error', 'Bukti transfer hanya dapat diunggah setelah komitmen diterima Organizer.');
        }
        if (! $commitment->canUploadProof()) {
            return back()->with('error', 'Pembayaran untuk komitmen ini sudah diunggah dan sedang/telah diverifikasi Admin.');
        }

        $data = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'nama_pengirim' => 'required|string|max:100',
            'nominal_transfer' => 'required|integer|min:1',
            'tanggal_transfer' => 'required|date|before_or_equal:today',
            'bukti_transfer' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'bukti_transfer.required' => 'Bukti transfer wajib diunggah.',
            'bukti_transfer.mimes' => 'Bukti transfer harus berformat JPG, PNG, atau PDF.',
            'bukti_transfer.max' => 'Ukuran bukti transfer maksimal 2 MB.',
            'tanggal_transfer.before_or_equal' => 'Tanggal transfer tidak boleh di masa depan.',
        ]);

        $rekening = BankAccount::active()->find($data['bank_account_id']);
        if (! $rekening) {
            return back()->withErrors(['bank_account_id' => 'Rekening tujuan yang dipilih tidak aktif.'])->withInput();
        }

        $path = $request->file('bukti_transfer')->store('payments/bukti', 'local');

        Payment::create([
            'commitment_id' => $commitment->id,
            'bank_account_id' => $rekening->id,
            'rekening_tujuan' => $rekening->label(),
            'nama_pengirim' => $data['nama_pengirim'],
            'nominal_transfer' => $data['nominal_transfer'],
            'tanggal_transfer' => $data['tanggal_transfer'],
            'bukti_transfer' => $path,
            'status' => 'Menunggu Verifikasi',
        ]);

        Notifikasi::kirimAdmin(
            $this->me()->nama_perusahaan.' mengunggah bukti transfer untuk "'.$commitment->event->judul.'". Menunggu verifikasi.',
            route('admin.dashboard')
        );

        return redirect()->route('sponsor.pembayaran', $commitment)
            ->with('success', 'Bukti transfer terkirim. Admin akan memverifikasi dana Anda.');
    }

    // ---------- Inbox & Invoice Donasi Sponsor ----------
    public function inbox()
    {
        $invoices = Commitment::where('sponsor_id', $this->me()->id)
            ->where('status_pembayaran', 'Terverifikasi')
            ->whereNotNull('nomor_invoice')
            ->with(['event.organizer', 'event.category'])
            ->latest('tanggal_validasi')
            ->paginate(10);

        return view('sponsor.inbox', compact('invoices'));
    }

    public function inboxShow(Commitment $commitment)
    {
        $this->ownCommitment($commitment);
        abort_unless($commitment->hasInvoice(), 404, 'Invoice belum tersedia.');

        $commitment->load(['event.organizer', 'event.category', 'sponsor']);

        return view('sponsor.inbox_show', compact('commitment'));
    }

    public function invoiceDownload(Commitment $commitment)
    {
        $this->ownCommitment($commitment);
        abort_unless($commitment->hasInvoice(), 404, 'Invoice belum tersedia.');

        $pdf = SimpleInvoicePdf::generate($commitment);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $commitment->nomor_invoice . '.pdf"',
        ]);
    }
}
