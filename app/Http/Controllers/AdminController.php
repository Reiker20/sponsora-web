<?php

namespace App\Http\Controllers;

use App\Models\Commitment;
use App\Models\Event;
use App\Models\EventVerification;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $accepted = Commitment::where('status', 'Diterima')->get();

        $chart = collect(range(5, 0))->mapWithKeys(function ($i) use ($accepted) {
            $m = now()->subMonths($i);

            return [
                $m->translatedFormat('M') => $accepted
                    ->filter(fn ($c) => $c->updated_at->isSameMonth($m))
                    ->sum('nominal')
            ];
        });

        return view('admin.dashboard', [
            'chart' => $chart,
            'stats' => [
                'akun' => User::whereIn('role', ['organizer', 'sponsor'])
                    ->where('status_verifikasi', 'Menunggu')->count(),
                'proposal' => Event::where('status', 'Menunggu Verifikasi')->count(),
                'aktif' => Event::where('status', 'Terverifikasi')->count(),
                'dana' => $accepted->sum('nominal'),
                'terlambat' => $this->laporanRows()
                    ->where('terlambat', true)->count(),
            ],
            'antrean' => Event::where('status', 'Menunggu Verifikasi')
                ->with('organizer')->latest()->take(5)->get(),
            'akunBaru' => User::whereIn('role', ['organizer', 'sponsor'])
                ->where('status_verifikasi', 'Menunggu')
                ->latest()->take(5)->get(),
            'perKategori' => Event::selectRaw('category_id, count(*) as total')
                ->groupBy('category_id')->with('category')->get(),
        ]);
    }

    // ---------- Verifikasi akun ----------
    public function akun(Request $request)
    {
        $status = $request->input('status', 'Menunggu');

        $users = User::whereIn('role', ['organizer', 'sponsor'])
            ->when(
                $status !== 'Semua',
                fn ($q) => $q->where('status_verifikasi', $status)
            )
            ->when(
                $request->role,
                fn ($q, $r) => $q->where('role', $r)
            )
            ->with(['organizerProfile', 'sponsorProfile'])
            ->latest()
            ->get();

        return view('admin.akun', compact('users', 'status'));
    }

    public function akunVerifikasi(Request $request, User $user)
    {
        abort_if($user->isAdmin(), 403);

        $data = $request->validate([
            'status' => 'required|in:Disetujui,Ditolak',
            'catatan' => 'nullable|required_if:status,Ditolak|string|max:500',
        ], [
            'catatan.required_if' => 'Catatan wajib diisi saat menolak akun.',
        ]);

        // Ambil catatan dengan aman jika form tidak mengirimkannya.
        $catatan = $data['catatan'] ?? null;

        $user->update([
            'status_verifikasi' => $data['status'],
            'catatan_verifikasi' => $catatan,
        ]);

        $pesan = "Verifikasi akun Anda: {$data['status']}";

        if ($catatan) {
            $pesan .= " — {$catatan}";
        } else {
            $pesan .= '.';
        }

        Notifikasi::kirim($user->id, $pesan);

        return back()->with(
            'success',
            "Akun {$user->displayName()} ditandai {$data['status']}."
        );
    }

    // ---------- Verifikasi proposal ----------
    public function proposals(Request $request)
    {
        $status = $request->input('status', 'Menunggu Verifikasi');

        $events = Event::with(['organizer', 'category'])
            ->when(
                $status !== 'Semua',
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->get();

        return view('admin.proposal', compact('events', 'status'));
    }

    public function proposalShow(Event $event)
    {
        $event->load(['organizer.user', 'category', 'verifications.admin']);

        return view('admin.proposal_show', compact('event'));
    }

    public function proposalVerifikasi(Request $request, Event $event)
    {
        abort_unless($event->status === 'Menunggu Verifikasi', 403);

        $data = $request->validate([
            'status' => 'required|in:Disetujui,Ditolak',
            'catatan' => 'nullable|required_if:status,Ditolak|string|max:1000',
        ], [
            'catatan.required_if' => 'Catatan perbaikan wajib diisi saat menolak.',
        ]);

        $catatan = $data['catatan'] ?? null;

        EventVerification::create([
            'event_id' => $event->id,
            'admin_id' => $request->user()->id,
            'status' => $data['status'],
            'catatan' => $catatan,
            'tanggal_verifikasi' => now(),
        ]);

        if ($data['status'] === 'Disetujui') {
            $event->update(['status' => 'Terverifikasi']);

            $msg = "Proposal \"{$event->judul}\" disetujui dan tayang di katalog.";
        } else {
            $event->update(['status' => 'Perlu Revisi']);

            $msg = "Proposal \"{$event->judul}\" perlu revisi: {$catatan}";
        }

        Notifikasi::kirim(
            $event->organizer->user_id,
            $msg,
            route('organizer.proposals')
        );

        return redirect()->route('admin.proposal')
            ->with('success', 'Hasil verifikasi tersimpan dan Organizer diberi tahu.');
    }

    // ---------- Monitoring ----------
    public function kegiatan(Request $request)
    {
        $events = Event::withFunding()
            ->with(['organizer', 'category'])
            ->when(
                $request->status,
                fn ($q, $s) => $q->where('status', $s)
            )
            ->when(
                $request->q,
                fn ($q, $s) => $q->where('judul', 'like', "%{$s}%")
            )
            ->latest()
            ->get();

        return view('admin.kegiatan', compact('events'));
    }

    public function kegiatanDestroy(Event $event)
    {
        if ($event->cover) {
            Storage::disk('public')->delete($event->cover);
        }

        Notifikasi::kirim(
            $event->organizer->user_id,
            "Kegiatan \"{$event->judul}\" dihapus oleh Admin."
        );

        $event->delete();

        return back()->with('success', 'Kegiatan dihapus.');
    }

    public function komitmen(Request $request)
    {
        $commitments = Commitment::with(['event.organizer', 'sponsor'])
            ->when(
                $request->status,
                fn ($q, $s) => $q->where('status', $s)
            )
            ->latest()
            ->get();

        return view('admin.komitmen', compact('commitments'));
    }

    private function laporanRows()
    {
        return Event::whereIn('status', ['Selesai', 'Dilaporkan'])
            ->with(['organizer', 'reports'])
            ->get()
            ->map(function ($e) {
                $k = $e->reports->firstWhere('jenis', 'keuangan');
                $h = $e->reports->firstWhere('jenis', 'hasil_program');

                $lengkap = $k?->isSent() && $h?->isSent();

                return (object) [
                    'event' => $e,
                    'keuangan' => $k?->status ?? 'Belum dibuat',
                    'hasil' => $h?->status ?? 'Belum dibuat',
                    'lengkap' => $lengkap,
                    'terlambat' => !$lengkap
                        && $e->batas_laporan
                        && $e->batas_laporan->isPast(),
                ];
            });
    }

    public function pelaporan(Request $request)
    {
        $rows = $this->laporanRows()
            ->when(
                $request->filter === 'belum',
                fn ($c) => $c->where('lengkap', false)
            )
            ->when(
                $request->filter === 'terlambat',
                fn ($c) => $c->where('terlambat', true)
            );

        return view('admin.pelaporan', [
            'rows' => $rows->values(),
            'filter' => $request->filter,
        ]);
    }
    private function cekAdmin()
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'admin',
            403,
            'Akses ditolak.'
        );
    }
}

