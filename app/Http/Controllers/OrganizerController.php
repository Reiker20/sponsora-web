<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\Notifikasi;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganizerController extends Controller
{
    private function me()
    {
        $user = Auth::user();

        return $user->organizerProfile ?? $user->organizerProfile()->create([
            'nama_organisasi' => $user->name,
            'jenis' => 'HIMA/UKM',
            'kontak' => '-',
        ]);
    }

    private function own(Event $event): Event
    {
        abort_unless($event->organizer_id === $this->me()->id, 403);

        return $event;
    }

    // ---------- Dashboard & profil ----------

    public function dashboard()
    {
        $events = $this->me()
            ->events()
            ->withFunding()
            ->with('category')
            ->latest()
            ->get();

        $commitments = Commitment::whereIn(
            'event_id',
            $events->pluck('id')
        )
            ->with(['sponsor', 'event'])
            ->latest()
            ->get();

        $chart = collect(range(5, 0))->mapWithKeys(function ($i) use ($commitments) {
            $m = now()->subMonths($i);

            return [
                $m->translatedFormat('M') => $commitments
                    ->where('status', 'Diterima')
                    ->filter(fn ($c) => $c->updated_at->isSameMonth($m))
                    ->sum('nominal'),
            ];
        });

        return view('organizer.dashboard', [
            'events' => $events->take(5),
            'commitments' => $commitments->take(5),
            'chart' => $chart,
            'stats' => [
                'aktif' => $events->where('status', 'Terverifikasi')->count(),
                'menunggu' => $events->where('status', 'Menunggu Verifikasi')->count(),
                'komitmen' => $commitments->count(),
                'dana' => $commitments
                    ->where('status', 'Diterima')
                    ->sum('nominal'),
            ],
        ]);
    }

    public function profil()
    {
        return view('organizer.profil', [
            // 'user' => auth()->user(),
            'user' => Auth::user(),
            'p' => $this->me(),
        ]);
    }

    public function updateProfil(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'nama_organisasi' => 'required|string|max:150',
            'jenis' => 'required|in:HIMA/UKM,NGO,NPO,Komunitas',
            'kontak' => 'required|string|max:30',
            'alamat' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $request->user()->update([
            'name' => $data['name'],
        ]);

        $profileData = collect($data)->except(['name', 'dokumen'])->all();

        if ($request->hasFile('dokumen')) {
            $path = $request->file('dokumen')->store('legalitas');
            $profileData['dokumen_legalitas'] = $path;
        }

        $this->me()->update($profileData);

        return back()->with(
            'success',
            'Profil organisasi berhasil diperbarui.'
        );
    }

    // ---------- Proposal ----------

    public function proposals(Request $request)
    {
        $events = $this->me()
            ->events()
            ->withFunding()
            ->with('category')
            ->when(
                $request->status,
                fn ($q, $s) => $q->where('status', $s)
            )
            ->latest()
            ->get();

        return view('organizer.proposals', [
            'events' => $events,
        ]);
    }

    /**
     * Menampilkan form pembuatan proposal.
     */
    public function create()
    {
        $event = new Event();

        $categories = Category::orderBy('nama_kategori')->get();

        return view('organizer.proposal_form', [
            'event' => $event,
            'categories' => $categories,
        ]);
    }

    private function rules(bool $creating): array
    {
        return [
            'judul' => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'deskripsi' => 'required|string|min:20',
            'lokasi' => 'required|string|max:150',
            'penerima_manfaat' => 'nullable|string|max:100',
            'tanggal_pelaksanaan' => $creating
                ? 'required|date|after_or_equal:today'
                : 'required|date',
            'target_dana' => 'required|integer|min:100000|max:100000000000',
            'benefit_sponsor' => 'required|string|min:10',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(true));

        if ($request->hasFile('cover')) {
            $data['cover'] = $request
                ->file('cover')
                ->store('covers', 'public');
        }

        $shouldAjukan = $request->input('action') === 'ajukan';

        if ($shouldAjukan && ! $this->me()->user->isVerified()) {
            return back()->withInput()->with('error', 'Akun Anda harus disetujui Admin sebelum mengajukan verifikasi proposal.');
        }

        $event = $this->me()->events()->create(
            $data + ['status' => $shouldAjukan ? 'Menunggu Verifikasi' : 'Draft']
        );

        if ($shouldAjukan) {
            Notifikasi::kirimAdmin(
                "Proposal baru menunggu verifikasi: {$event->judul}",
                route('admin.proposal.show', $event)
            );
            Notifikasi::kirim(
                $this->me()->user_id,
                "Proposal \"{$event->judul}\" berhasil diajukan untuk verifikasi. Menunggu peninjauan Admin."
            );

            return redirect()
                ->route('organizer.proposals')
                ->with(
                    'success',
                    "Proposal \"{$event->judul}\" berhasil disimpan dan diajukan untuk verifikasi Admin."
                );
        }

        return redirect()
            ->route('organizer.proposals')
            ->with(
                'success',
                "Proposal \"{$event->judul}\" disimpan sebagai Draft."
            );
    }

    /**
     * Menampilkan form edit proposal beserta daftar kategori.
     */
    public function edit(Event $event)
    {
        $this->own($event);

        abort_unless(
            $event->isEditable(),
            403,
            'Proposal yang sudah diverifikasi tidak dapat diubah.'
        );

        $categories = Category::orderBy('nama_kategori')->get();

        return view('organizer.proposal_form', [
            'event' => $event,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $this->own($event);

        abort_unless($event->isEditable(), 403);

        $data = $request->validate($this->rules(false));

        if ($request->hasFile('cover')) {
            if ($event->cover) {
                Storage::disk('public')->delete($event->cover);
            }

            $data['cover'] = $request
                ->file('cover')
                ->store('covers', 'public');
        }

        $shouldAjukan = $request->input('action') === 'ajukan';

        if ($shouldAjukan && ! $this->me()->user->isVerified()) {
            return back()->withInput()->with('error', 'Akun Anda harus disetujui Admin sebelum mengajukan verifikasi proposal.');
        }

        if ($shouldAjukan) {
            $data['status'] = 'Menunggu Verifikasi';
        }

        $event->update($data);

        if ($shouldAjukan) {
            Notifikasi::kirimAdmin(
                "Proposal baru menunggu verifikasi: {$event->judul}",
                route('admin.proposal.show', $event)
            );
            Notifikasi::kirim(
                $this->me()->user_id,
                "Proposal \"{$event->judul}\" berhasil diajukan untuk verifikasi. Menunggu peninjauan Admin."
            );

            return redirect()
                ->route('organizer.proposals')
                ->with('success', "Proposal \"{$event->judul}\" berhasil diperbarui dan diajukan untuk verifikasi Admin.");
        }

        return redirect()
            ->route('organizer.proposals')
            ->with('success', 'Proposal diperbarui.');
    }

    public function destroy(Event $event)
    {
        $this->own($event);

        abort_unless(
            $event->isEditable(),
            403,
            'Hanya proposal Draft / Perlu Revisi yang dapat dihapus.'
        );

        if ($event->cover) {
            Storage::disk('public')->delete($event->cover);
        }

        $event->delete();

        return redirect()
            ->route('organizer.proposals')
            ->with('success', 'Proposal dihapus.');
    }

    public function ajukan(Event $event)
    {
        $this->own($event);

        abort_unless($event->isEditable(), 403, 'Hanya proposal Draft atau Perlu Revisi yang dapat diajukan untuk verifikasi.');

        if (! $this->me()->user->isVerified()) {
            return back()->with(
                'error',
                'Akun Anda harus disetujui Admin sebelum mengajukan verifikasi proposal.'
            );
        }

        $event->update([
            'status' => 'Menunggu Verifikasi',
        ]);

        Notifikasi::kirimAdmin(
            "Proposal baru menunggu verifikasi: {$event->judul}",
            route('admin.proposal.show', $event)
        );

        Notifikasi::kirim(
            $this->me()->user_id,
            "Proposal \"{$event->judul}\" berhasil diajukan untuk verifikasi. Menunggu peninjauan Admin."
        );

        return back()->with(
            'success',
            "Proposal \"{$event->judul}\" berhasil diajukan. Tunggu hasil peninjauan Admin."
        );
    }

    public function selesai(Event $event)
    {
        $this->own($event);

        abort_unless($event->status === 'Terverifikasi', 403);

        $event->update([
            'status' => 'Selesai',
            'tanggal_selesai' => today(),
            'batas_laporan' => today()->addDays(14),
        ]);

        foreach (
            $event->commitments()
                ->where('status', 'Diterima')
                ->with('sponsor')
                ->get() as $c
        ) {
            Notifikasi::kirim(
                $c->sponsor->user_id,
                "Event \"{$event->judul}\" telah selesai. Laporan akan segera dikirim."
            );
        }

        return back()->with(
            'success',
            'Event ditandai selesai. Kewajiban laporan dibuka (batas 14 hari).'
        );
    }

    // ---------- Komitmen ----------

    public function komitmen(Request $request)
    {
        $commitments = Commitment::whereIn('event_id', $this->me()->events()->pluck('id'))
            ->with(['sponsor', 'event'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()->get();

        return view('organizer.komitmen', compact('commitments'));
    }

    /** UC-06 Meninjau komitmen dana */
    public function keputusan(Request $request, Commitment $commitment)
    {
        $this->own($commitment->event);
        abort_unless($commitment->status === 'Menunggu Tanggapan', 403);

        $data = $request->validate([
            'keputusan' => 'required|in:Diterima,Ditolak,Negosiasi',
            'catatan_organizer' => 'required_if:keputusan,Negosiasi|nullable|string|max:500',
            'nominal_usulan' => 'nullable|integer|min:50000',
        ]);

        if ($data['keputusan'] === 'Diterima' && ! $commitment->event->acceptsCommitments()) {
            return back()->with('error', 'Event tidak lagi menerima komitmen.');
        }

        $commitment->update([
            'status' => $data['keputusan'],
            'catatan_organizer' => $data['catatan_organizer'] ?? null,
            'nominal_usulan' => $data['nominal_usulan'] ?? null,
        ]);

        Notifikasi::kirim($commitment->sponsor->user_id,
            "Komitmen Anda pada \"{$commitment->event->judul}\": {$data['keputusan']}.", route('sponsor.dashboard'));

        return back()->with('success', "Komitmen ditandai {$data['keputusan']}.");
    }

    public function riwayat()
    {
        $items = Commitment::whereIn(
            'event_id',
            $this->me()->events()->pluck('id')
        )
            ->where('status', 'Diterima')
            ->with(['sponsor', 'event'])
            ->latest()
            ->get();

        return view('organizer.riwayat', [
            'items' => $items,
        ]);
    }

    // ---------- Laporan ----------

    public function laporanIndex()
    {
        $events = $this->me()
            ->events()
            ->whereIn('status', ['Selesai', 'Dilaporkan'])
            ->with('reports')
            ->withFunding()
            ->latest()
            ->get();

        return view('organizer.laporan_index', compact('events'));
    }

    public function laporanShow(Event $event)
    {
        $this->own($event);

        abort_unless(
            in_array($event->status, ['Selesai', 'Dilaporkan'], true),
            403,
            'Laporan dibuka setelah event berstatus Selesai.'
        );

        $event->load(['category', 'organizer', 'commitments' => fn ($q) => $q->where('status', 'Diterima')]);

        $keuangan = $event->report('keuangan')
            ?->load('items.reviews.sponsor', 'reviews.sponsor');

        $hasil = $event->report('hasil_program');
        $hasil?->load('reviews.sponsor');

        $rows = ($keuangan?->items ?? collect())
            ->map(fn ($i) => [
                'id' => $i->id,
                'uraian' => $i->uraian,
                'nominal' => $i->nominal,
                'bukti_lama' => $i->bukti_berkas,
                'reviews' => $i->reviews->map(fn ($r) => [
                    'sponsor_nama' => $r->sponsor?->nama_perusahaan ?? 'Sponsor',
                    'status' => $r->status,
                    'catatan' => $r->catatan,
                ])->values()->all(),
            ])
            ->values();

        return view(
            'organizer.laporan_show',
            compact('event', 'keuangan', 'hasil', 'rows')
        );
    }

    private function editableReport(
        Event $event,
        string $jenis
    ): Report {
        $this->own($event);

        abort_unless(
            in_array($event->status, ['Selesai', 'Dilaporkan'], true),
            403
        );

        $report = Report::firstOrCreate(
            [
                'event_id' => $event->id,
                'jenis' => $jenis,
            ],
            [
                'status' => 'Draft',
            ]
        );

        abort_unless(
            $report->isEditable(),
            403,
            'Laporan sudah dikirim dan tidak dapat diubah.'
        );

        return $report;
    }

    private function kirimLaporan(
        Event $event,
        Report $report,
        string $label
    ): void {
        $report->reviews()->delete();

        $report->update([
            'status' => 'Dikirim',
            'tanggal_kirim' => now(),
        ]);

        foreach (
            $event->commitments()
                ->where('status', 'Diterima')
                ->with('sponsor')
                ->get()
                ->unique('sponsor_id') as $c
        ) {
            Notifikasi::kirim(
                $c->sponsor->user_id,
                ucfirst($label) . " \"{$event->judul}\" telah dikirim.",
                route('sponsor.laporan.show', $event)
            );
        }

        $event->load('reports');

        $sent = fn ($j) => $event->reports
            ->firstWhere('jenis', $j)
            ?->isSent();

        if (
            $sent('keuangan')
            && $sent('hasil_program')
        ) {
            $event->update([
                'status' => 'Dilaporkan',
            ]);
        }
    }

    /** UC-21 Menyusun laporan keuangan */
    public function simpanKeuangan(
        Request $request,
        Event $event
    ) {
        $report = $this->editableReport($event, 'keuangan');

        $kirim = $request->input('aksi') === 'kirim';

        $request->validate([
            'items' => ($kirim ? 'required' : 'nullable') . '|array',
            'items.*.uraian' => 'required|string|max:200',
            'items.*.nominal' => 'required|integer|min:0',
            'items.*.bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'items.*.bukti_lama' => 'nullable|string',
            'items.*.id' => 'nullable|integer',
        ], [
            'items.*.bukti.max' => 'Berkas bukti maksimal 5 MB.',
            'items.*.bukti.mimes' => 'Bukti harus PDF, JPG, atau PNG.',
        ]);

        $rows = $request->input('items', []);

        if ($kirim) {
            foreach ($rows as $i => $row) {
                if (
                    ! $request->hasFile("items.$i.bukti")
                    && empty($row['bukti_lama'])
                ) {
                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Setiap rincian pengeluaran wajib memiliki bukti.'
                        );
                }
            }
        }

        DB::transaction(function () use (
            $request,
            $report,
            $rows
        ) {
            $existingItems = $report->items()->get()->keyBy('id');
            $oldFiles = $existingItems->pluck('bukti_berkas')->filter()->all();

            $keptItemIds = [];
            $keepFiles = [];

            foreach ($rows as $i => $row) {
                $itemId = ! empty($row['id']) ? (int) $row['id'] : null;
                $existingItem = $itemId ? $existingItems->get($itemId) : null;

                $path = null;
                if ($request->hasFile("items.$i.bukti")) {
                    $path = $request->file("items.$i.bukti")->store('bukti');
                } elseif ($existingItem && ! empty($row['bukti_lama']) && $row['bukti_lama'] === $existingItem->bukti_berkas) {
                    $path = $existingItem->bukti_berkas;
                } elseif (! empty($row['bukti_lama']) && in_array($row['bukti_lama'], $oldFiles, true)) {
                    $path = $row['bukti_lama'];
                }

                if ($path) {
                    $keepFiles[] = $path;
                }

                if ($existingItem) {
                    $existingItem->update([
                        'uraian' => $row['uraian'],
                        'nominal' => $row['nominal'],
                        'bukti_berkas' => $path,
                    ]);
                    $keptItemIds[] = $existingItem->id;
                } else {
                    $newItem = $report->items()->create([
                        'uraian' => $row['uraian'],
                        'nominal' => $row['nominal'],
                        'bukti_berkas' => $path,
                    ]);
                    $keptItemIds[] = $newItem->id;
                }
            }

            // Hapus item lama yang di-remove oleh organizer
            $itemsToDelete = $existingItems->whereNotIn('id', $keptItemIds);
            foreach ($itemsToDelete as $delItem) {
                $delItem->delete();
            }

            // Hapus berkas fisik yang tidak terpakai lagi
            Storage::delete(array_diff($oldFiles, $keepFiles));
        });

        if ($kirim) {
            $this->kirimLaporan(
                $event,
                $report,
                'laporan keuangan'
            );

            return back()->with(
                'success',
                'Laporan keuangan dikirim ke Sponsor.'
            );
        }

        return back()->with(
            'success',
            'Draft laporan keuangan tersimpan.'
        );
    }

    /** UC-22 Melaporkan hasil program */
    public function simpanHasil(
        Request $request,
        Event $event
    ) {
        $report = $this->editableReport($event, 'hasil_program');

        $kirim = $request->input('aksi') === 'kirim';

        $data = $request->validate([
            'ringkasan' => ($kirim ? 'required' : 'nullable') . '|string|max:3000',
            'capaian_hasil' => ($kirim ? 'required' : 'nullable') . '|string|max:3000',
            'kendala' => 'nullable|string|max:3000',
            'dokumentasi' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $update = collect($data)
            ->only(['ringkasan', 'capaian_hasil', 'kendala'])
            ->all();

        if ($request->hasFile('dokumentasi')) {
            if ($report->berkas_dokumentasi) {
                Storage::delete($report->berkas_dokumentasi);
            }

            $update['berkas_dokumentasi'] = $request
                ->file('dokumentasi')
                ->store('dokumentasi');
        }

        $report->update($update);

        if ($kirim) {
            $this->kirimLaporan(
                $event,
                $report,
                'laporan hasil program'
            );

            return back()->with(
                'success',
                'Laporan hasil program dikirim ke Sponsor.'
            );
        }

        return back()->with(
            'success',
            'Draft laporan hasil program tersimpan.'
        );
    }
}