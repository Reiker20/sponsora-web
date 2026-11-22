@extends('layouts.panel')
@section('title', 'Verifikasi Dana Masuk')
@section('crumb', 'Verifikasi Dana Masuk')
@section('heading', 'Verifikasi Dana Masuk')
@section('sub', 'Cocokkan bukti transfer Sponsor dengan mutasi rekening platform sebelum dana dinyatakan sah.')
@section('content')
<div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
  <x-stat label="Menunggu Verifikasi" :value="$stats['menunggu']"/>
  <x-stat label="Total Dana Terverifikasi" :value="'Rp '.number_format($stats['terverifikasi'],0,',','.')"/>
  <x-stat label="Bukti Ditolak" :value="$stats['ditolak']"/>
</div>

<div class="mb-4 mt-6 flex flex-wrap gap-2 text-xs font-semibold">
  @foreach(['Menunggu Verifikasi','Terverifikasi','Ditolak','Semua'] as $s)
    <a href="{{ route('admin.pembayaran', ['status' => $s]) }}" class="rounded-full px-3 py-1.5 {{ $status === $s ? 'bg-brand text-white' : 'bg-white' }}">{{ $s }}</a>
  @endforeach
</div>

<div class="space-y-3">
@forelse($payments as $p)
  @php $c = $p->commitment; $selisih = $p->nominal_transfer !== (int) $c->nominal; @endphp
  <div class="card" x-data="{tolak:false}">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
      <div class="min-w-0">
        <p class="font-semibold">{{ $c->sponsor->nama_perusahaan }} <x-badge :s="$p->status"/></p>
        <p class="text-xs text-gray-500">Untuk: {{ $c->event->judul }} · {{ $c->event->organizer->nama_organisasi }}</p>
        <p class="mt-2 text-xs text-gray-600">Pengirim <b>{{ $p->nama_pengirim }}</b> · tanggal {{ $p->tanggal_transfer->format('d M Y') }} · ke {{ $p->rekening_tujuan }}</p>
        <a href="{{ $p->buktiUrl() }}" target="_blank" class="mt-1 inline-block text-xs font-semibold text-brand underline">Lihat bukti transfer</a>
        @if($selisih)<p class="mt-2 rounded-lg bg-amber-50 p-2 text-xs text-amber-800">Perhatian: nominal transfer berbeda dari komitmen (@rp($c->nominal)).</p>@endif
        @if($p->status !== 'Menunggu Verifikasi')
          <p class="mt-2 text-xs text-gray-500">Diputuskan {{ $p->tanggal_verifikasi?->format('d M Y H:i') }} oleh {{ $p->verifier?->name ?? 'Admin' }}@if($p->catatan_admin) — “{{ $p->catatan_admin }}”@endif</p>
        @endif
      </div>
      <p class="text-xl font-bold text-brand">@rp($p->nominal_transfer)</p>
    </div>

    @if($p->isPending())
      <div class="mt-4 flex flex-wrap items-start gap-2 border-t border-gray-100 pt-4">
        <form method="POST" action="{{ route('admin.pembayaran.verifikasi', $p) }}" onsubmit="return confirm('Dana ini sudah masuk ke rekening platform?')">@csrf
          <input type="hidden" name="status" value="Disetujui"><button class="btn btn-primary btn-sm">Verifikasi Dana Masuk</button></form>
        <button type="button" @click="tolak=!tolak" class="btn btn-outline btn-sm !text-red-600">Tolak</button>
        <form x-show="tolak" x-cloak method="POST" action="{{ route('admin.pembayaran.verifikasi', $p) }}" class="flex w-full flex-col gap-2 sm:flex-row">@csrf
          <input type="hidden" name="status" value="Ditolak">
          <input name="catatan" required maxlength="500" class="input flex-1" placeholder="Alasan penolakan (wajib), mis. nominal tidak sesuai mutasi">
          <button class="btn btn-primary btn-sm">Kirim Penolakan</button>
        </form>
      </div>
    @endif
  </div>
@empty
  <p class="card text-center text-sm text-gray-500">Tidak ada pembayaran pada filter ini.</p>
@endforelse
</div>
@endsection