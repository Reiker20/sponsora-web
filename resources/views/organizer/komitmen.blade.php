
@extends('layouts.panel')
@section('title', 'Komitmen Masuk')
@section('heading', 'Komitmen Masuk')
@section('sub', 'Terima pengajuan dana dari Sponsor untuk mendanai kegiatan Anda.')

@section('content')
<div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
    <a href="{{ route('organizer.komitmen') }}"
       class="rounded-full px-3 py-1.5 {{ ! request('status') ? 'bg-brand text-white' : 'bg-white' }}">
        Semua
    </a>
    @foreach(['Menunggu Tanggapan','Diterima'] as $s)
        <a href="{{ route('organizer.komitmen', ['status' => $s]) }}"
           class="rounded-full px-3 py-1.5 {{ request('status') == $s ? 'bg-brand text-white' : 'bg-white' }}">
            {{ $s }}
        </a>
    @endforeach
</div>
<div class="space-y-3">
@forelse($commitments as $c)
    <div class="card">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div>
                <p class="font-semibold">
                    {{ $c->sponsor->nama_perusahaan }}
                    <x-badge :s="$c->status"/>
                </p>

                <p class="text-xs text-gray-500">
                    Untuk: {{ $c->event->judul }}
                    · {{ $c->tanggal_pengajuan->format('d M Y') }}
                    · {{ $c->bentuk_dukungan }}
                </p>

                @if($c->catatan)
                    <p class="mt-2 rounded-lg bg-gray-50 p-2 text-sm">
                        “{{ $c->catatan }}”
                    </p>
                @endif
            </div>

            <div class="text-left sm:text-right">
                <p class="text-xs text-gray-500">
                    {{ $c->status_pembayaran === 'Terverifikasi' ? 'Nominal Tervalidasi' : 'Nominal Klaim Sponsor' }}
                </p>
                <p class="text-xl font-bold text-brand">
                    @rp($c->nominal)
                </p>
                @if($c->hasNominalAdjustment())
                    <p class="text-[11px] text-amber-700">
                        Klaim awal: @rp($c->nominal_klaim)
                    </p>
                @endif
            </div>
        </div>

        {{-- Informasi Bukti Pembayaran & Form Validasi Nominal --}}
        <div class="mt-3 rounded-xl bg-gray-50 p-3 text-xs space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-gray-600">Pembayaran:</span>

                <x-badge :s="$c->status_pembayaran ?? 'Belum Dibayar'"/>

                @if($c->bukti_pembayaran)
                    <a href="{{ route('files.show', ['pembayaran', $c->id]) }}"
                       target="_blank"
                       class="font-semibold text-brand underline">
                        Lihat bukti pembayaran
                    </a>

                    <span class="text-gray-400">
                        · diunggah {{ $c->tanggal_bukti?->format('d M Y H:i') }}
                    </span>

                    @if($c->catatan_pembayaran)
                        <span class="text-gray-500">
                            · “{{ $c->catatan_pembayaran }}”
                        </span>
                    @endif
                @else
                    <span class="text-gray-400">
                        Sponsor belum mengunggah bukti.
                    </span>
                @endif
            </div>

            @if($c->bukti_pembayaran && $c->status_pembayaran !== 'Terverifikasi')
                <form method="POST"
                      action="{{ route('organizer.komitmen.validasi', $c) }}"
                      class="grid gap-3 border-t border-gray-200 pt-3 sm:grid-cols-3 items-end">
                    @csrf
                    <div>
                        <label class="label">Nominal Tervalidasi (Rp) *</label>
                        <input type="number"
                               name="nominal_tervalidasi"
                               min="0"
                               value="{{ old('nominal_tervalidasi', $c->nominal_klaim ?? $c->nominal) }}"
                               required
                               class="input !py-2 text-xs">
                        <p class="mt-1 text-[10px] text-gray-500">Isi sesuai mutasi bank/QRIS (isi 0 jika bukti tidak sah).</p>
                    </div>
                    <div>
                        <label class="label">Catatan untuk Sponsor (opsional)</label>
                        <input type="text"
                               name="catatan_organizer"
                               maxlength="500"
                               value="{{ old('catatan_organizer') }}"
                               placeholder="mis. Mutasi masuk sesuai / disesuaikan"
                               class="input !py-2 text-xs">
                    </div>
                    <div class="flex justify-end">
                        <button class="btn btn-primary btn-sm w-full sm:w-auto">
                            Validasi &amp; Kirim Invoice
                        </button>
                    </div>
                </form>
            @elseif($c->hasInvoice())
                <div class="border-t border-gray-200 pt-2 flex flex-wrap items-center justify-between gap-2 text-emerald-800">
                    <div>
                        <span class="font-semibold">No. Invoice: {{ $c->nomor_invoice }}</span>
                        <span class="text-gray-500">· divalidasi {{ $c->tanggal_validasi?->format('d M Y H:i') }}</span>
                        @if($c->catatan_organizer)
                            <p class="mt-0.5 text-gray-600">Catatan: “{{ $c->catatan_organizer }}”</p>
                        @endif
                    </div>
                </div>
            @elseif($c->status === 'Menunggu Tanggapan')
                @if($c->event->acceptsCommitments())
                    <form method="POST"
                          action="{{ route('organizer.komitmen.terima', $c) }}"
                          class="pt-1">
                        @csrf
                        <button class="btn btn-primary btn-sm">Terima Komitmen</button>
                    </form>
                @else
                    <p class="text-xs text-gray-500">
                        Event tidak lagi menerima komitmen.
                    </p>
                @endif
            @endif
        </div>

    </div>
@empty
    <p class="card text-center text-sm text-gray-500">
        Belum ada komitmen masuk.
    </p>
@endforelse
</div>
@endsection
