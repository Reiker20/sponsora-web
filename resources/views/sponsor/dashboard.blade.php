
@extends('layouts.panel')
@section('title', 'Dashboard Sponsor')
@section('crumb', 'Dashboard Sponsor')
@section('heading', 'Selamat Datang, ' . auth()->user()->displayName())
@section('sub', 'Ringkasan komitmen dan riwayat kemitraan perusahaan Anda.')
@section('actions')
    <a href="{{ route('events.index') }}" class="btn btn-primary">Jelajahi Event →</a>
@endsection

@section('content')
@unless(auth()->user()->isVerified())
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        Akun Anda berstatus <b>{{ auth()->user()->status_verifikasi }}</b>. Anda dapat menjelajah katalog, namun komitmen dana baru dapat diajukan setelah Admin menyetujui akun.
        @if(auth()->user()->catatan_verifikasi)
            <br>Catatan Admin: {{ auth()->user()->catatan_verifikasi }}
        @endif
    </div>
@endunless

<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <x-stat label="Komitmen Diajukan" :value="$stats['total']"/>
    <x-stat label="Diterima" :value="$stats['diterima']"/>
    <x-stat label="Masih Berjalan" :value="$stats['berjalan']"/>
    <x-stat label="Total Dana Diterima" :value="'Rp '.number_format($stats['dana'],0,',','.')"/>
</div>

<div class="mb-3 mt-8 flex items-center justify-between">
    <h2 class="text-lg font-semibold">Komitmen & Kemitraan Terbaru</h2>
    <a href="{{ route('sponsor.riwayat') }}" class="text-xs font-semibold text-brand hover:underline">
        Lihat Seluruh Riwayat →
    </a>
</div>

<div class="space-y-3">
@forelse($commitments as $c)
    <div class="card">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div>
                <a href="{{ route('events.show', $c->event) }}" class="font-semibold text-brand hover:underline">
                    {{ $c->event->judul }}
                </a>
                <x-badge :s="$c->status"/>

                <p class="text-xs text-gray-500">
                    {{ $c->event->organizer->nama_organisasi }}
                    · diajukan {{ $c->tanggal_pengajuan->format('d M Y') }}
                    · {{ $c->bentuk_dukungan }}
                </p>

                <div class="mt-2 max-w-sm">
                    <div class="flex justify-between text-[11px] text-gray-500">
                        <span>Progres dana event</span>
                        <span>{{ $c->event->progress }}%</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-gray-100">
                        <div class="h-1.5 rounded-full bg-lime" style="width: {{ $c->event->progress }}%"></div>
                    </div>
                </div>
            </div>

            <p class="text-xl font-bold">@rp($c->nominal)</p>
        </div>

        @include('sponsor._pembayaran', ['c' => $c])

        @if($c->status === 'Diterima' && in_array($c->event->status, ['Selesai','Dilaporkan']))
            <a class="mt-3 inline-block text-sm font-semibold text-brand underline"
               href="{{ route('sponsor.laporan.show', $c->event) }}">
                Lihat laporan program →
            </a>
        @endif
    </div>
@empty
    <p class="card text-center text-sm text-gray-500">
        Belum ada komitmen.
        <a class="font-semibold text-brand underline" href="{{ route('events.index') }}">
            Cari event
        </a>
        untuk mulai berkontribusi.
    </p>
@endforelse
</div>
@endsection
