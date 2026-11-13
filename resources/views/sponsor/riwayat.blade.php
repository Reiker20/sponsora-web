@extends('layouts.panel')
@section('title', 'Riwayat Kemitraan')
@section('crumb', 'Riwayat Kemitraan')
@section('heading', 'Riwayat Kemitraan Sponsorship')
@section('sub', 'Catatan lengkap rekam jejak kontribusi CSR dan kerja sama sponsorship perusahaan Anda.')
@section('actions')
  <a href="{{ route('events.index') }}" class="btn btn-primary">Jelajahi Event →</a>
@endsection

@section('content')
{{-- Kartu Statistik Ringkasan CSR --}}
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
  <x-stat label="Total Kemitraan" :value="$stats['total']"/>
  <x-stat label="Kemitraan Diterima" :value="$stats['diterima']"/>
  <x-stat label="Total Dana CSR" :value="'Rp '.number_format($stats['dana'], 0, ',', '.')"/>
  <x-stat label="Mitra Organizer" :value="$stats['organizer']"/>
</div>

{{-- Filter Status --}}
<div class="mt-8 flex flex-wrap items-center gap-2 text-xs font-semibold">
  <span class="mr-1 text-gray-500">Filter Status:</span>
  <a href="{{ route('sponsor.riwayat') }}"
     class="rounded-full px-3 py-1.5 transition {{ empty($currentStatus) ? 'bg-brand text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-brand-50' }}">
    Semua ({{ $stats['total'] }})
  </a>
  @foreach(['Diterima', 'Menunggu Tanggapan', 'Negosiasi', 'Ditolak'] as $st)
    <a href="{{ route('sponsor.riwayat', ['status' => $st]) }}"
       class="rounded-full px-3 py-1.5 transition {{ $currentStatus === $st ? 'bg-brand text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-brand-50' }}">
      {{ $st }}
    </a>
  @endforeach
</div>

{{-- Daftar Komitmen Riwayat --}}
<div class="mt-4 space-y-3">
  @forelse($commitments as $c)
    <div class="card transition hover:shadow-md" x-data="{ re: false }">
      <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
        <div class="min-w-0 flex-1 space-y-2">
          <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('events.show', $c->event) }}" class="text-base font-bold text-brand hover:underline">
              {{ $c->event->judul }}
            </a>
            <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold text-brand">
              {{ $c->event->category->nama_kategori ?? 'Umum' }}
            </span>
            <x-badge :s="$c->status"/>
          </div>

          <p class="text-xs text-gray-500">
            Penyelenggara: <span class="font-medium text-gray-700">{{ $c->event->organizer->nama_organisasi }}</span>
            · Diajukan: <span class="font-medium text-gray-700">{{ $c->tanggal_pengajuan ? $c->tanggal_pengajuan->format('d M Y') : $c->created_at->format('d M Y') }}</span>
            · Bentuk: <span class="font-medium text-gray-700">{{ $c->bentuk_dukungan }}</span>
          </p>

          {{-- Progress bar pendanaan event --}}
          <div class="max-w-md pt-1">
            <div class="flex justify-between text-[11px] text-gray-500">
              <span>Progres Dana Event: @rp($c->event->dana_terkumpul) / @rp($c->event->target_dana)</span>
              <span class="font-semibold text-brand">{{ $c->event->progress }}%</span>
            </div>
            <div class="mt-1 h-1.5 rounded-full bg-gray-100">
              <div class="h-1.5 rounded-full bg-lime transition-all" style="width: {{ $c->event->progress }}%"></div>
            </div>
          </div>

          @if($c->catatan)
            <p class="text-xs text-gray-600 bg-gray-50 rounded-lg p-2 max-w-xl">
              <span class="font-semibold">Catatan Pengajuan Anda:</span> {{ $c->catatan }}
            </p>
          @endif
        </div>

        {{-- Nominal & Aksi --}}
        <div class="flex flex-col items-start gap-2 sm:items-end">
          <div class="text-left sm:text-right">
            <span class="text-xs text-gray-400">Nominal Komitmen</span>
            <p class="text-2xl font-bold text-brand">@rp($c->nominal)</p>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('events.show', $c->event) }}" class="btn btn-outline btn-sm">
              Lihat Event
            </a>

            @if($c->status === 'Diterima' && in_array($c->event->status, ['Selesai', 'Dilaporkan']))
              <a href="{{ route('sponsor.laporan.show', $c->event) }}" class="btn btn-lime btn-sm">
                Lihat Laporan Program
              </a>
            @endif

            @if($c->status === 'Negosiasi')
              <button type="button" @click="re = !re" class="btn btn-primary btn-sm">
                Ajukan Ulang
              </button>
            @endif
          </div>
        </div>
      </div>

      {{-- Form Ajukan Ulang saat Negosiasi --}}
      @if($c->status === 'Negosiasi')
        <div class="mt-4 border-t border-sky-100 pt-3">
          <div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-xs text-sky-800">
            <p class="font-bold">Organizer Mengajukan Negosiasi:</p>
            <p class="mt-1">{{ $c->catatan_organizer ?: 'Organizer meminta penyesuaian nominal komitmen.' }}</p>
            @if($c->nominal_usulan)
              <p class="mt-1 font-semibold text-brand">Usulan Nominal dari Organizer: @rp($c->nominal_usulan)</p>
            @endif
          </div>

          <form x-show="re" x-cloak method="POST" action="{{ route('sponsor.komitmen.resubmit', $c) }}" class="mt-3 grid gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3 sm:grid-cols-3">
            @csrf
            <div>
              <label class="label">Nominal Baru (Rp) *</label>
              <input type="number" name="nominal" value="{{ $c->nominal_usulan ?: $c->nominal }}" min="50000" required class="input">
            </div>
            <div class="sm:col-span-2">
              <label class="label">Catatan Tambahan untuk Organizer</label>
              <input name="catatan" placeholder="Contoh: Kami menyetujui penyesuaian nominal sebesar..." class="input">
            </div>
            <div class="flex justify-end gap-2 sm:col-span-3">
              <button type="button" @click="re = false" class="btn btn-outline btn-sm">Batal</button>
              <button class="btn btn-primary btn-sm">Kirim Pengajuan Ulang</button>
            </div>
          </form>
        </div>
      @endif

      @if($c->status === 'Ditolak' && $c->catatan_organizer)
        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-2 text-xs text-red-700">
          <span class="font-bold">Alasan Penolakan dari Organizer:</span> {{ $c->catatan_organizer }}
        </div>
      @endif
    </div>
  @empty
    <div class="card py-12 text-center text-gray-500">
      <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <p class="mt-3 text-sm font-medium">Belum ada riwayat kemitraan{{ $currentStatus ? ' dengan status ' . $currentStatus : '' }}.</p>
      <p class="mt-1 text-xs text-gray-400">Temukan kegiatan sosial yang berdampak dan ajukan komitmen pendanaan perusahaan Anda.</p>
      <a href="{{ route('events.index') }}" class="btn btn-primary btn-sm mt-4">Jelajahi Katalog Event</a>
    </div>
  @endforelse

  {{-- Paginasi --}}
  <div class="mt-4">
    {{ $commitments->links() }}
  </div>
</div>
@endsection
