@extends('layouts.panel')
@section('title', 'Laporan Pertanggungjawaban')
@section('crumb', 'Laporan Program')
@section('heading', 'Laporan Pertanggungjawaban Event')
@section('sub', 'Tinjau dan verifikasi laporan keuangan serta hasil kegiatan dari event yang Anda danai.')

@section('content')
<div class="space-y-4">
  @forelse($events as $e)
    @php
      $lapKeuangan = $e->reports->firstWhere('jenis', 'keuangan');
      $lapHasil = $e->reports->firstWhere('jenis', 'hasil_program');
    @endphp
    <div class="card flex flex-col justify-between gap-4 transition hover:shadow-md lg:flex-row lg:items-center">
      <div class="min-w-0 space-y-1.5">
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="text-lg font-bold text-brand">{{ $e->judul }}</h2>
          <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand">
            {{ $e->category->nama_kategori }}
          </span>
          <x-badge :s="$e->status"/>
        </div>

        <p class="text-xs text-gray-500">
          Penyelenggara: <span class="font-medium text-gray-700">{{ $e->organizer->nama_organisasi }}</span>
          · Pelaksanaan: <span class="font-medium text-gray-700">{{ $e->tanggal_pelaksanaan->format('d M Y') }}</span>
          · Total Dana Event: <span class="font-semibold text-brand">@rp($e->dana_terkumpul)</span>
        </p>

        <div class="flex flex-wrap items-center gap-3 pt-2 text-xs">
          <div class="flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-1">
            <span class="text-gray-500">Laporan Keuangan:</span>
            @if($lapKeuangan && $lapKeuangan->isSent())
              <x-badge :s="$lapKeuangan->status"/>
            @else
              <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-semibold text-gray-600">Belum Dikirim Organizer</span>
            @endif
          </div>

          <div class="flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-1">
            <span class="text-gray-500">Hasil Program:</span>
            @if($lapHasil && $lapHasil->isSent())
              <x-badge :s="$lapHasil->status"/>
            @else
              <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-semibold text-gray-600">Belum Dikirim Organizer</span>
            @endif
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('sponsor.laporan.show', $e) }}" class="btn btn-primary btn-sm whitespace-nowrap">
          Tinjau &amp; Verifikasi Laporan →
        </a>
      </div>
    </div>
  @empty
    <div class="card py-12 text-center text-gray-500">
      <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <p class="mt-3 text-sm font-medium">Belum ada laporan kegiatan yang tersedia.</p>
      <p class="mt-1 text-xs text-gray-400">Laporan akan muncul setelah event yang Anda danai selesai dan Organizer mengirimkan laporannya.</p>
      <a href="{{ route('sponsor.riwayat') }}" class="btn btn-primary btn-sm mt-4">Lihat Riwayat Kemitraan</a>
    </div>
  @endforelse
</div>
@endsection
