<?php

namespace App\Http\Controllers;

use App\Models\Commitment;
use App\Models\Event;
use App\Models\Notifikasi;
use App\Models\Report;
use App\Models\ReportReview;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    private function me() { return auth()->user()->sponsorProfile; }

    public function dashboard()
    {
        $commitments = Commitment::where('sponsor_id', $this->me()->id)
            ->with(['event' => fn ($q) => $q->withFunding(), 'event.organizer'])->latest()->get();

        return view('sponsor.dashboard', [
            'commitments' => $commitments,
            'stats' => [
                'total' => $commitments->count(),
                'diterima' => $commitments->where('status', 'Diterima')->count(),
                'berjalan' => $commitments->whereIn('status', ['Menunggu Tanggapan', 'Negosiasi'])->count(),
                'dana' => $commitments->where('status', 'Diterima')->sum('nominal'),
            ],
        ]);
    }

    public function profil() { return view('sponsor.profil', ['user' => auth()->user(), 'p' => $this->me()]); }

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
            ->whereIn('status', ['Menunggu Tanggapan', 'Negosiasi', 'Diterima'])->exists();
        if ($aktif) {
            return back()->withErrors(['nominal' => 'Anda sudah memiliki komitmen aktif pada event ini.']);
        }

        $data = $request->validate([
            'nominal' => 'required|integer|min:50000|max:100000000000',
            'bentuk_dukungan' => 'required|string|max:60',
            'catatan' => 'nullable|string|max:500',
        ]);

        $event->commitments()->create($data + ['sponsor_id' => $this->me()->id, 'status' => 'Menunggu Tanggapan']);
        Notifikasi::kirim($event->organizer->user_id,
            $this->me()->nama_perusahaan." mengajukan komitmen pada \"{$event->judul}\".", route('organizer.komitmen'));

        return back()->with('komitmen_sukses', $data['nominal']);
    }

    /** Sponsor mengajukan ulang setelah Organizer memilih Negosiasi */
    public function resubmit(Request $request, Commitment $commitment)
    {
        abort_unless($commitment->sponsor_id === $this->me()->id && $commitment->status === 'Negosiasi', 403);
        abort_unless($commitment->event->acceptsCommitments(), 403);

        $data = $request->validate(['nominal' => 'required|integer|min:50000', 'catatan' => 'nullable|string|max:500']);
        $commitment->update($data + ['status' => 'Menunggu Tanggapan', 'tanggal_pengajuan' => now()]);
        Notifikasi::kirim($commitment->event->organizer->user_id,
            $this->me()->nama_perusahaan." mengajukan ulang komitmen pada \"{$commitment->event->judul}\".", route('organizer.komitmen'));

        return back()->with('success', 'Pengajuan ulang terkirim.');
    }

    public function laporanIndex()
    {
        $ids = $this->me()->commitments()->where('status', 'Diterima')->pluck('event_id');
        $events = Event::whereIn('id', $ids)->with(['organizer', 'reports'])->latest()->get();

        return view('sponsor.laporan_index', compact('events'));
    }

    public function laporanShow(Event $event)
    {
        abort_unless($event->commitments()->where('sponsor_id', $this->me()->id)->where('status', 'Diterima')->exists(), 403);
        $event->load('organizer');
        $keuangan = $event->report('keuangan')?->load('items');
        $hasil = $event->report('hasil_program');
        $reviews = ReportReview::where('sponsor_id', $this->me()->id)
            ->whereIn('report_id', array_filter([$keuangan?->id, $hasil?->id]))->get()->keyBy('report_id');

        return view('sponsor.laporan_show', [
            'event' => $event, 'keuangan' => $keuangan?->isSent() ? $keuangan : null,
            'hasil' => $hasil?->isSent() ? $hasil : null, 'reviews' => $reviews,
        ]);
    }

    /** UC-24 Menanggapi laporan */
    public function tanggapi(Request $request, Report $report)
    {
        $event = $report->event;
        abort_unless($report->status !== 'Draft'
            && $event->commitments()->where('sponsor_id', $this->me()->id)->where('status', 'Diterima')->exists(), 403);

        $data = $request->validate([
            'status' => 'required|in:Diterima,Perlu Revisi',
            'catatan' => 'required_if:status,Perlu Revisi|nullable|string|max:1000',
        ], ['catatan.required_if' => 'Catatan wajib diisi saat meminta revisi.']);

        ReportReview::updateOrCreate(
            ['report_id' => $report->id, 'sponsor_id' => $this->me()->id],
            $data + ['tanggal_tanggapan' => now()]
        );
        $report->refreshStatusFromReviews();

        $label = $report->jenis === 'keuangan' ? 'laporan keuangan' : 'laporan hasil program';
        Notifikasi::kirim($event->organizer->user_id,
            $this->me()->nama_perusahaan.' menanggapi '.$label." \"{$event->judul}\": {$data['status']}.", route('organizer.laporan.show', $event));

        return back()->with('success', 'Tanggapan tersimpan.');
    }
}
