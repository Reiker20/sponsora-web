@extends('layouts.panel')
@section('title', 'Laporan Pertanggungjawaban')
@section('crumb', 'Laporan Pertanggungjawaban')
@section('heading', 'Laporan Pertanggungjawaban Kegiatan')
@section('sub', 'Susun dan laporkan realisasi penggunaan dana sponsorship serta hasil program pasca-kegiatan.')

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
          Pelaksanaan: <span class="font-medium text-gray-700">{{ $e->tanggal_pelaksanaan->format('d M Y') }}</span>
          @if($e->batas_laporan)
            · Tenggat Laporan: <span class="font-medium text-amber-700">{{ $e->batas_laporan->format('d M Y') }}</span>
          @endif
          · Dana Terkumpul: <span class="font-semibold text-brand">@rp($e->dana_terkumpul)</span>
        </p>

        {{-- Status masing-masing laporan --}}
        <div class="flex flex-wrap items-center gap-3 pt-2 text-xs">
          <div class="flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-1">
            <span class="text-gray-500">Laporan Keuangan:</span>
            @if($lapKeuangan)
              <x-badge :s="$lapKeuangan->status"/>
            @else
              <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-semibold text-gray-600">Belum Disusun</span>
            @endif
          </div>

          <div class="flex items-center gap-1.5 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-1">
            <span class="text-gray-500">Hasil Program:</span>
            @if($lapHasil)
              <x-badge :s="$lapHasil->status"/>
            @else
              <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-semibold text-gray-600">Belum Disusun</span>
            @endif
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('organizer.laporan.show', $e) }}" class="btn btn-primary btn-sm whitespace-nowrap">
          Kelola Laporan →
        </a>
      </div>
    </div>
  @empty
    <div class="card py-12 text-center text-gray-500">
      <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
      </svg>
      <p class="mt-3 text-sm font-medium">Belum ada kegiatan yang membutuhkan pelaporan.</p>
      <p class="mt-1 text-xs text-gray-400">Kewajiban pelaporan akan aktif secara otomatis setelah Anda menandai suatu event menjadi <b>Selesai</b>.</p>
      <a href="{{ route('organizer.proposals') }}" class="btn btn-primary btn-sm mt-4">Lihat Daftar Proposal</a>
    </div>
  @endforelse
</div>
@endsection
