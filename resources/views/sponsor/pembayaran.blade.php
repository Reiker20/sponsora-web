@extends('layouts.panel')
@section('title', 'Pembayaran Komitmen')
@section('crumb', 'Pembayaran Komitmen')
@section('heading', 'Pembayaran Komitmen')
@section('sub', 'Transfer ke rekening platform Sponsora, lalu unggah bukti transfer untuk diverifikasi Admin.')
@section('actions')<a href="{{ route('sponsor.dashboard') }}" class="btn btn-outline">← Dashboard</a>@endsection
@section('content')
<div class="card">
  <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
    <div><p class="text-xs text-gray-500">Event</p><p class="font-semibold text-brand">{{ $commitment->event->judul }}</p>
      <p class="text-xs text-gray-500">{{ $commitment->event->organizer->nama_organisasi }}</p></div>
    <div class="sm:text-right"><p class="text-xs text-gray-500">Nominal komitmen</p><p class="text-2xl font-bold text-brand">@rp($commitment->nominal)</p><x-badge :s="$commitment->status"/></div>
  </div>
</div>

@if($commitment->status !== 'Diterima')
  <p class="card mt-4 text-center text-sm text-gray-500">Pembayaran baru dapat dilakukan setelah komitmen diterima Organizer.</p>
@else
  <div class="mt-6 grid gap-6 lg:grid-cols-2">
    {{-- Instruksi transfer --}}
    <div class="card">
      <h2 class="font-semibold text-brand">1. Transfer ke rekening platform</h2>
      <p class="mt-1 text-xs text-gray-500">Dana tidak dikirim langsung ke Organizer. Admin akan memverifikasi dana masuk terlebih dahulu.</p>
      <div class="mt-4 space-y-3">
        @forelse($accounts as $a)
          <div class="rounded-xl border border-gray-100 bg-brand-50 p-4">
            <p class="text-sm font-semibold">{{ $a->nama_bank }}</p>
            <p class="text-xl font-bold tracking-wide text-brand">{{ $a->nomor_rekening }}</p>
            <p class="text-sm text-gray-600">a.n. {{ $a->atas_nama }}</p>
            @if($a->catatan)<p class="mt-1 text-xs text-gray-500">{{ $a->catatan }}</p>@endif
          </div>
        @empty
          <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Admin belum menetapkan rekening tujuan. Silakan coba lagi nanti.</p>
        @endforelse
      </div>
    </div>

    {{-- Form unggah bukti --}}
    <div class="card">
      <h2 class="font-semibold text-brand">2. Unggah bukti transfer</h2>
      @if($commitment->canUploadProof() && $accounts->isNotEmpty())
        <form method="POST" action="{{ route('sponsor.pembayaran.store', $commitment) }}" enctype="multipart/form-data" class="mt-4 space-y-3">@csrf
          <div><label class="label">Rekening tujuan yang Anda gunakan</label>
            <select name="bank_account_id" required class="input">@foreach($accounts as $a)<option value="{{ $a->id }}" @selected(old('bank_account_id') == $a->id)>{{ $a->nama_bank }} — {{ $a->nomor_rekening }}</option>@endforeach</select></div>
          <div><label class="label">Nama pengirim (sesuai rekening Anda)</label><input name="nama_pengirim" value="{{ old('nama_pengirim', auth()->user()->displayName()) }}" required maxlength="100" class="input"></div>
          <div class="grid gap-3 sm:grid-cols-2">
            <div><label class="label">Nominal transfer (Rp)</label><input type="number" name="nominal_transfer" value="{{ old('nominal_transfer', $commitment->nominal) }}" min="1" required class="input"></div>
            <div><label class="label">Tanggal transfer</label><input type="date" name="tanggal_transfer" value="{{ old('tanggal_transfer', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="input"></div>
          </div>
          <div><label class="label">Bukti transfer (JPG/PNG/PDF, maks 2 MB)</label><input type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png,.pdf" required class="input"></div>
          <button class="btn btn-primary w-full">Kirim Bukti Transfer</button>
        </form>
      @elseif(! $commitment->canUploadProof())
        <p class="mt-3 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-800">Bukti transfer sudah dikirim. Pantau statusnya pada riwayat di bawah.</p>
      @endif
    </div>
  </div>
@endif

{{-- Riwayat pembayaran --}}
<h2 class="mb-3 mt-8 text-lg font-semibold">Riwayat Pembayaran</h2>
<div class="space-y-3">
  @forelse($commitment->payments as $p)
    <div class="card">
      <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
        <div>
          <p class="font-semibold">@rp($p->nominal_transfer) <x-badge :s="$p->status"/></p>
          <p class="text-xs text-gray-500">{{ $p->tanggal_transfer->format('d M Y') }} · pengirim {{ $p->nama_pengirim }} · ke {{ $p->rekening_tujuan }}</p>
          <a href="{{ $p->buktiUrl() }}" target="_blank" class="mt-1 inline-block text-xs font-semibold text-brand underline">Lihat bukti transfer</a>
                  @if($p->status === 'Ditolak' && $p->catatan_admin)<p class="mt-2 rounded-lg bg-red-50 p-2 text-xs text-red-700">Catatan Admin: {{ $p->catatan_admin }}. Silakan transfer/unggah ulang bukti yang benar.</p>@endif
        </div>
      </div>
    </div>
  @empty
    <p class="card text-center text-sm text-gray-500">Belum ada bukti transfer yang diunggah.</p>
  @endforelse
</div>
@endsection