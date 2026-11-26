@extends('layouts.panel')
@section('title', 'Inbox & Invoice')
@section('crumb', 'Inbox & Invoice')
@section('heading', 'Inbox & Invoice Donasi')
@section('sub', 'Pesan konfirmasi resmi dan invoice dari Organizer setelah donasi Anda divalidasi.')

@section('content')
<div class="space-y-3">
  @forelse($invoices as $inv)
    <div class="card flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div class="space-y-1">
        <div class="flex flex-wrap items-center gap-2">
          <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand">{{ $inv->nomor_invoice }}</span>
          <x-badge :s="$inv->status_pembayaran"/>
          @if($inv->hasNominalAdjustment())
            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800">Nominal Disesuaikan</span>
          @endif
        </div>
        <h3 class="text-base font-bold text-gray-900">{{ $inv->event->judul }}</h3>
        <p class="text-xs text-gray-500">
          Dari: <span class="font-medium text-gray-700">{{ $inv->event->organizer->nama_organisasi }}</span>
          · Divalidasi {{ $inv->tanggal_validasi?->format('d M Y H:i') }}
        </p>
        @if($inv->catatan_organizer)
          <p class="text-xs text-gray-600 italic">“{{ $inv->catatan_organizer }}”</p>
        @endif
      </div>

      <div class="flex flex-col items-start gap-2 sm:items-end">
        <div class="sm:text-right">
          <span class="text-[11px] text-gray-500">Nilai Tervalidasi</span>
          <p class="text-xl font-bold text-brand">@rp($inv->nominal)</p>
          @if($inv->hasNominalAdjustment())
            <p class="text-[11px] text-amber-700">Klaim awal: @rp($inv->nominal_klaim)</p>
          @endif
        </div>
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('sponsor.inbox.show', $inv) }}" class="btn btn-outline btn-sm">Buka Pesan &amp; Invoice</a>
          <a href="{{ route('sponsor.inbox.download', $inv) }}" class="btn btn-primary btn-sm">Download PDF</a>
        </div>
      </div>
    </div>
  @empty
    <div class="card py-12 text-center text-gray-500">
      <p class="text-sm font-medium">Belum ada pesan invoice masuk.</p>
      <p class="mt-1 text-xs text-gray-400">Invoice resmi akan otomatis masuk ke Inbox ini setelah Organizer memvalidasi bukti transfer Anda.</p>
      <a href="{{ route('events.index') }}" class="btn btn-primary btn-sm mt-4">Jelajahi Event</a>
    </div>
  @endforelse

  <div class="mt-4">
    {{ $invoices->links() }}
  </div>
</div>
@endsection
