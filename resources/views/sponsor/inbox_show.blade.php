@extends('layouts.panel')
@section('title', 'Invoice ' . $commitment->nomor_invoice)
@section('crumb', 'Inbox & Invoice')
@section('heading', 'Detail Pesan & Invoice')
@section('sub', $commitment->nomor_invoice)

@section('actions')
<div class="flex flex-wrap gap-2 print:hidden">
  <a href="{{ route('sponsor.inbox') }}" class="btn btn-outline btn-sm">← Kembali ke Inbox</a>
  <button type="button" onclick="window.print()" class="btn btn-outline btn-sm">Cetak Invoice</button>
  <a href="{{ route('sponsor.inbox.download', $commitment) }}" class="btn btn-primary btn-sm">Download Invoice (PDF)</a>
</div>
@endsection

@section('content')
@php
  $event = $commitment->event;
  $organizer = $event->organizer;
  $sponsor = $commitment->sponsor;
  $klaim = $commitment->nominal_klaim ?? $commitment->nominal;
  $valid = $commitment->nominal_tervalidasi ?? $commitment->nominal;
@endphp

{{-- Pesan Konfirmasi dari Organizer --}}
<div class="mb-6 rounded-2xl border border-brand/15 bg-white p-5 shadow-sm space-y-2 print:hidden">
  <div class="flex flex-wrap items-center justify-between gap-2">
    <p class="text-sm font-bold text-brand">Pesan dari {{ $organizer->nama_organisasi }}</p>
    <span class="text-xs text-gray-400">{{ $commitment->tanggal_validasi?->format('d M Y H:i') }}</span>
  </div>
  <p class="text-sm text-gray-700 leading-relaxed">
    Terima kasih atas dukungan <b>{{ $sponsor->nama_perusahaan }}</b> pada kegiatan <b>{{ $event->judul }}</b>.
    Bukti transfer Anda telah diperiksa dan divalidasi sebesar <b>@rp($valid)</b>.
    Berikut adalah dokumen Invoice resmi yang dapat Anda unduh atau cetak sebagai arsip perusahaan.
  </p>

  @if($commitment->hasNominalAdjustment())
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
      <b>Penyesuaian Nominal:</b> Nominal resmi pada Invoice ditetapkan sebesar <b>@rp($valid)</b>
      (berbeda dari klaim awal Anda sebesar <b>@rp($klaim)</b>) sesuai hasil verifikasi mutasi transfer oleh Organizer.
    </div>
  @endif

  @if($commitment->catatan_organizer)
    <div class="rounded-xl bg-gray-50 p-3 text-xs text-gray-700">
      <span class="font-semibold">Catatan Organizer:</span> “{{ $commitment->catatan_organizer }}”
    </div>
  @endif
</div>

{{-- Dokumen Invoice Resmi --}}
<div class="card overflow-hidden !p-0 print:shadow-none print:border-0">
  <div class="bg-brand px-6 py-6 text-white flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
    <div>
      <p class="text-xs font-semibold uppercase tracking-widest text-lime">Sponsora Official Receipt</p>
      <h2 class="mt-1 text-2xl font-bold">INVOICE DUKUNGAN EVENT</h2>
    </div>
    <div class="sm:text-right">
      <p class="text-xs text-white/70">Nomor Invoice</p>
      <p class="text-lg font-bold tracking-wide">{{ $commitment->nomor_invoice }}</p>
      <span class="mt-1 inline-block rounded-full bg-lime px-2.5 py-0.5 text-[11px] font-bold text-brand">TERVERIFIKASI</span>
    </div>
  </div>

  <div class="p-6 space-y-6">
    <div class="grid gap-4 rounded-xl bg-gray-50 p-4 text-xs sm:grid-cols-3">
      <div>
        <p class="text-gray-500">Tanggal Validasi</p>
        <p class="mt-0.5 font-semibold text-gray-900">{{ $commitment->tanggal_validasi?->format('d M Y H:i') }} WIB</p>
      </div>
      <div>
        <p class="text-gray-500">Tanggal Pengiriman Bukti</p>
        <p class="mt-0.5 font-semibold text-gray-900">{{ ($commitment->tanggal_bukti ?? $commitment->created_at)?->format('d M Y H:i') }} WIB</p>
      </div>
      <div>
        <p class="text-gray-500">Bentuk Dukungan</p>
        <p class="mt-0.5 font-semibold text-gray-900">{{ $commitment->bentuk_dukungan ?: 'Dana Tunai' }}</p>
      </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 text-sm">
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Penerima Dana (Organizer)</p>
        <p class="mt-1 font-bold text-gray-900">{{ $organizer->nama_organisasi }}</p>
        <p class="text-xs text-gray-600">Kontak: {{ $organizer->kontak }}</p>
        @if($event->hasBankAccount())
          <p class="mt-1 text-xs text-gray-600">Rekening: {{ $event->nama_bank }} · {{ $event->nomor_rekening }} (a.n. {{ $event->atas_nama_rekening }})</p>
        @elseif($event->qris_image)
          <p class="mt-1 text-xs text-gray-600">Metode: QRIS Organizer</p>
        @endif
      </div>

      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Donatur (Sponsor)</p>
        <p class="mt-1 font-bold text-gray-900">{{ $sponsor->nama_perusahaan }}</p>
        <p class="text-xs text-gray-600">Kontak: {{ $sponsor->kontak }}</p>
        @if($sponsor->nib)
          <p class="text-xs text-gray-600">NIB: {{ $sponsor->nib }}</p>
        @endif
      </div>
    </div>

    <div class="border-t border-gray-100 pt-4 text-sm">
      <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Rincian Kegiatan</p>
      <p class="mt-1 font-bold text-brand">{{ $event->judul }}</p>
      <p class="text-xs text-gray-600">{{ $event->category->nama_kategori ?? '-' }} · {{ $event->lokasi }} · {{ $event->tanggal_pelaksanaan?->format('d M Y') }}</p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-100">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
          <tr>
            <th class="th">Keterangan</th>
            <th class="th text-right">Nominal</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr>
            <td class="td text-gray-600">Nominal Klaim Awal (Input Sponsor)</td>
            <td class="td text-right text-gray-600">@rp($klaim)</td>
          </tr>
          <tr class="bg-brand-50/50 font-bold text-brand">
            <td class="td">Nominal Tervalidasi Organizer (Nilai Resmi Invoice)</td>
            <td class="td text-right text-lg">@rp($valid)</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p class="border-t border-gray-100 pt-4 text-[11px] text-gray-500">
      Dokumen ini sah diterbitkan secara elektronik melalui platform Sponsora berdasarkan validasi aktual Organizer terhadap mutasi/bukti transfer.
    </p>
  </div>
</div>
@endsection
