<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\Notifikasi;
use App\Models\OrganizerProfile;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrganizerController extends Controller
{
    private function me()
    {
        $user = Auth::user();

        abort_unless($user, 401);

        return OrganizerProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nama_organisasi' => $user->name,
                'jenis' => 'HIMA/UKM',
                'kontak' => '-',
            ]
        );
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
            'nama_bank' => 'nullable|required_with:nomor_rekening,atas_nama_rekening|string|max:60',
            'nomor_rekening' => ['nullable', 'required_with:nama_bank,atas_nama_rekening', 'string', 'max:40', 'regex:/^[0-9\- ]{5,40}$/'],
            'atas_nama_rekening' => 'nullable|required_with:nama_bank,nomor_rekening|string|max:100',
            'qris_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'hapus_qris' => 'nullable|boolean',
        ];
    }

    private function preparePaymentMethodData(Request $request, array $data, ?Event $event = null): array
    {
        $hasBank = filled($data['nama_bank'] ?? null)
            && filled($data['nomor_rekening'] ?? null)
            && filled($data['atas_nama_rekening'] ?? null);

        $hapusQris = $request->boolean('hapus_qris');
        $hasQris = $request->hasFile('qris_image')
            || ($event && filled($event->qris_image) && ! $hapusQris);

        if (! $hasBank && ! $hasQris) {
            throw ValidationException::withMessages([
                'metode_pembayaran' => 'Minimal lengkapi salah satu metode penerimaan donasi: Rekening Bank atau unggah Foto QRIS.',
            ]);
        }

        unset($data['hapus_qris']);

        if (! $hasBank) {
            $data['nama_bank'] = null;
            $data['nomor_rekening'] = null;
            $data['atas_nama_rekening'] = null;
        }

        if ($request->hasFile('qris_image')) {
            if ($event?->qris_image) {
                Storage::disk('public')->delete($event->qris_image);
            }
            $data['qris_image'] = $request->file('qris_image')->store('qris', 'public');
        } elseif ($hapusQris && $event?->qris_image) {
            Storage::disk('public')->delete($event->qris_image);
            $data['qris_image'] = null;
        }

        return $data;
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(true), [
            'nomor_rekening.regex' => 'Nomor rekening hanya boleh berisi angka, spasi, atau tanda hubung.',
        ]);

        $data = $this->preparePaymentMethodData($request, $data);

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

        $data = $request->validate($this->rules(false), [
            'nomor_rekening.regex' => 'Nomor rekening hanya boleh berisi angka, spasi, atau tanda hubung.',
        ]);

        $data = $this->preparePaymentMethodData($request, $data, $event);

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
        if ($event->qris_image) {
            Storage::disk('public')->delete($event->qris_image);
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
                ->where('nominal', '>', 0)
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

    // /** UC-06 Meninjau komitmen dana */
    // public function keputusan(Request $request, Commitment $commitment)
    // {
    //     $this->own($commitment->event);
    //     abort_unless($commitment->status === 'Menunggu Tanggapan', 403);

    //     $data = $request->validate([
    //         'keputusan' => 'required|in:Diterima,Ditolak,Negosiasi',
    //         'catatan_organizer' => 'required_if:keputusan,Negosiasi|nullable|string|max:500',
    //         'nominal_usulan' => 'nullable|integer|min:50000',
    //     ]);

    //     if ($data['keputusan'] === 'Diterima' && ! $commitment->event->acceptsCommitments()) {
    //         return back()->with('error', 'Event tidak lagi menerima komitmen.');
    //     }

    //     $commitment->update([
    //         'status' => $data['keputusan'],
    //         'catatan_organizer' => $data['catatan_organizer'] ?? null,
    //         'nominal_usulan' => $data['nominal_usulan'] ?? null,
    //     ]);

    //     Notifikasi::kirim($commitment->sponsor->user_id,
    //         "Komitmen Anda pada \"{$commitment->event->judul}\": {$data['keputusan']}.", route('sponsor.dashboard'));

    //     return back()->with('success', "Komitmen ditandai {$data['keputusan']}.");
    // }

    /** Menerima komitmen dana. Opsi Tolak dan Negosiasi sudah ditiadakan. */
    public function terima(Commitment $commitment)
    {
        $this->own($commitment->event);
        abort_unless($commitment->status === 'Menunggu Tanggapan', 403);

        if (! $commitment->event->acceptsCommitments()) {
            return back()->with('error', 'Event tidak lagi menerima komitmen.');
        }

        $commitment->update([
            'status' => 'Diterima',
            'catatan_organizer' => null,
            'nominal_usulan' => null,
        ]);

        Notifikasi::kirim(
            $commitment->sponsor->user_id,
            "Komitmen Anda pada \"{$commitment->event->judul}\" telah diterima.",
            route('sponsor.dashboard')
        );

        return back()->with('success', 'Komitmen diterima.');
    }

    /** Memvalidasi bukti transfer dan menetapkan nominal aktual yang masuk ke Invoice */
    public function validasiDonasi(Request $request, Commitment $commitment)
    {
        $this->own($commitment->event);
        abort_unless(filled($commitment->bukti_pembayaran), 403, 'Bukti transfer belum diunggah.');
        abort_if($commitment->status_pembayaran === 'Terverifikasi', 403, 'Donasi ini sudah divalidasi.');

        $data = $request->validate([
            'nominal_tervalidasi' => 'required|integer|min:0|max:100000000000',
            'catatan_organizer' => 'nullable|string|max:500',
        ]);

        $validated = DB::transaction(function () use ($commitment, $data) {
            $c = Commitment::whereKey($commitment->id)->lockForUpdate()->firstOrFail();
            abort_unless(filled($c->bukti_pembayaran), 403, 'Bukti transfer belum diunggah.');
            abort_if($c->status_pembayaran === 'Terverifikasi', 403, 'Donasi ini sudah divalidasi.');

            $klaim = $c->nominal_klaim ?? $c->nominal;
            $valid = (int) $data['nominal_tervalidasi'];
            $invoiceNo = 'INV-SPO-' . now()->format('Ymd') . '-' . str_pad((string) $c->id, 4, '0', STR_PAD_LEFT);

            $c->update([
                'nominal_klaim' => $klaim,
                'nominal_tervalidasi' => $valid,
                'nominal' => $valid,
                'status' => 'Diterima',
                'status_pembayaran' => 'Terverifikasi',
                'catatan_organizer' => $data['catatan_organizer'] ?? null,
                'nominal_usulan' => null,
                'nomor_invoice' => $invoiceNo,
                'tanggal_validasi' => now(),
            ]);

            return [$c, $klaim, $valid, $invoiceNo];
        });

        [$c, $klaim, $valid, $invoiceNo] = $validated;
        $validFormatted = 'Rp ' . number_format($valid, 0, ',', '.');
        $pesan = "Invoice {$invoiceNo}: Donasi Anda untuk \"{$commitment->event->judul}\" telah divalidasi sebesar {$validFormatted}.";
        if ($klaim !== $valid) {
            $klaimFormatted = 'Rp ' . number_format($klaim, 0, ',', '.');
            $pesan .= " (Disesuaikan dari klaim awal {$klaimFormatted}).";
        }

        Notifikasi::kirim(
            $commitment->sponsor->user_id,
            $pesan,
            route('sponsor.inbox.show', $c)
        );

        return back()->with('success', "Donasi divalidasi sebesar {$validFormatted}. Invoice {$invoiceNo} terkirim ke Inbox Sponsor.");
    }

    public function riwayat()
    {
        $items = Commitment::whereIn(
            'event_id',
            $this->me()->events()->pluck('id')
        )
            ->where('status', 'Diterima')
            ->where('nominal', '>', 0)
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

        $event->load(['category', 'organizer', 'commitments' => fn ($q) => $q->where('status', 'Diterima')->where('nominal', '>', 0)]);

        $keuangan = $event->report('keuangan')
            ?->load('items.reviews.sponsor', 'reviews.sponsor');

        $hasil = $event->report('hasil_program');
        $hasil?->load('reviews.sponsor');

        $rows = ($keuangan?->items ?? collect())
            ->map(fn ($i) => [
                'uraian' => $i->uraian,
                'nominal' => $i->nominal,
                'bukti_lama' => $i->bukti_berkas,
                'id' => $i->id,
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
                ->where('nominal', '>', 0)
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
            $existing = $report->items()->get()->keyBy('id');
            $old = $existing
                ->pluck('bukti_berkas')
                ->filter()
                ->all();

            $keptIds = [];
            $keep = [];

            foreach ($rows as $i => $row) {
                $itemId = ! empty($row['id']) ? (int) $row['id'] : null;
                $existingItem = $itemId ? $existing->get($itemId) : null;

                if ($request->hasFile("items.$i.bukti")) {
                    $path = $request->file("items.$i.bukti")->store('bukti');
                } elseif (in_array($row['bukti_lama'] ?? null, $old, true)) {
                    $path = $row['bukti_lama'];
                } else {
                    $path = $existingItem?->bukti_berkas;
                }

                if ($path) {
                    $keep[] = $path;
                }

                if ($existingItem) {
                    $existingItem->update([
                        'uraian' => $row['uraian'],
                        'nominal' => $row['nominal'],
                        'bukti_berkas' => $path,
                    ]);
                    $keptIds[] = $existingItem->id;
                } else {
                    $created = $report->items()->create([
                        'uraian' => $row['uraian'],
                        'nominal' => $row['nominal'],
                        'bukti_berkas' => $path,
                    ]);
                    $keptIds[] = $created->id;
                }
            }

            if ($keptIds === []) {
                $report->items()->delete();
            } else {
                $report->items()->whereNotIn('id', $keptIds)->delete();
            }

            Storage::delete(array_diff($old, $keep));
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