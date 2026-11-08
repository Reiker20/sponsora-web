@extends('layouts.panel')
@section('title', 'Komitmen Masuk')
@section('heading', 'Komitmen Masuk')
@section('sub', 'Tinjau pengajuan dana dari Sponsor: terima, tolak, atau negosiasikan.')
@section('content')
<div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
  <a href="{{ route('organizer.komitmen') }}" class="rounded-full px-3 py-1.5 {{ ! request('status') ? 'bg-brand text-white' : 'bg-white' }}">Semua</a>
  @foreach(['Menunggu Tanggapan','Diterima','Negosiasi','Ditolak'] as $s)<a href="{{ route('organizer.komitmen', ['status' => $s]) }}" class="rounded-full px-3 py-1.5 {{ request('status') == $s ? 'bg-brand text-white' : 'bg-white' }}">{{ $s }}</a>@endforeach</div>
<div class="space-y-3">
@forelse($commitments as $c)
  <div class="card" x-data="{mode: null}">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
      <div><p class="font-semibold">{{ $c->sponsor->nama_perusahaan }} <x-badge :s="$c->status"/></p>
        <p class="text-xs text-gray-500">Untuk: {{ $c->event->judul }} · {{ $c->tanggal_pengajuan->format('d M Y') }} · {{ $c->bentuk_dukungan }}</p>
        @if($c->catatan)<p class="mt-2 rounded-lg bg-gray-50 p-2 text-sm">“{{ $c->catatan }}”</p>@endif
        @if($c->catatan_organizer)<p class="mt-2 rounded-lg bg-sky-50 p-2 text-xs text-sky-700">Catatan Anda: {{ $c->catatan_organizer }} @if($c->nominal_usulan) (usulan @rp($c->nominal_usulan)) @endif</p>@endif</div>
      <p class="text-xl font-bold text-brand">@rp($c->nominal)</p></div>
    @if($c->status === 'Menunggu Tanggapan')
      <div class="mt-4 flex flex-wrap gap-2">
        @if($c->event->acceptsCommitments())
        <form method="POST" action="{{ route('organizer.komitmen.keputusan', $c) }}">@csrf<input type="hidden" name="keputusan" value="Diterima"><button class="btn btn-primary btn-sm">Terima</button></form>
        <button type="button" @click="mode = mode==='nego' ? null : 'nego'" class="btn btn-outline btn-sm">Negosiasikan</button>@endif
        <button type="button" @click="mode = mode==='tolak' ? null : 'tolak'" class="btn btn-danger btn-sm">Tolak</button></div>
      <form x-show="mode==='nego'" x-cloak method="POST" action="{{ route('organizer.komitmen.keputusan', $c) }}" class="mt-3 grid gap-3 rounded-xl bg-sky-50 p-4 sm:grid-cols-3">@csrf<input type="hidden" name="keputusan" value="Negosiasi">
        <div class="sm:col-span-2"><label class="label">Catatan / syarat baru *</label><input name="catatan_organizer" class="input" required></div>
        <div><label class="label">Usulan nominal (opsional)</label><input type="number" name="nominal_usulan" class="input"></div>
        <button class="btn btn-primary btn-sm sm:col-span-3">Kirim Negosiasi</button></form>
      <form x-show="mode==='tolak'" x-cloak method="POST" action="{{ route('organizer.komitmen.keputusan', $c) }}" class="mt-3 rounded-xl bg-red-50 p-4">@csrf<input type="hidden" name="keputusan" value="Ditolak">
        <label class="label">Alasan (opsional)</label><input name="catatan_organizer" class="input"><button class="btn btn-danger btn-sm mt-3">Konfirmasi Tolak</button></form>
    @endif
  </div>
@empty<p class="card text-center text-sm text-gray-500">Belum ada komitmen masuk.</p>@endforelse
</div>
@endsection