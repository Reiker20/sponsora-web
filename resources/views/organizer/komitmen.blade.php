
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

            <p class="text-xl font-bold text-brand">
                @rp($c->nominal)
            </p>
        </div>

        @if($c->status === 'Menunggu Tanggapan')
            @if($c->event->acceptsCommitments())
                <form method="POST"
                      action="{{ route('organizer.komitmen.terima', $c) }}"
                      class="mt-4">
                    @csrf
                    <button class="btn btn-primary btn-sm">Terima</button>
                </form>
            @else
                <p class="mt-3 text-xs text-gray-500">
                    Event tidak lagi menerima komitmen.
                </p>
            @endif
        @endif

        {{-- Informasi Pembayaran --}}
        @if($c->status === 'Diterima')
            <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg bg-gray-50 p-3 text-xs">
                <span class="font-semibold text-gray-600">Pembayaran:</span>

                <x-badge :s="$c->status_pembayaran"/>

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
        @endif

    </div>
@empty
    <p class="card text-center text-sm text-gray-500">
        Belum ada komitmen masuk.
    </p>
@endforelse
</div>
@endsection
