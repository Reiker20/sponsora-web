@extends('layouts.panel')
@section('title', 'Tinjau Proposal')
@section('heading', 'Tinjau Proposal')
@section('sub', $event->judul)
@section('actions')<a href="{{ route('admin.proposal') }}" class="btn btn-outline">← Kembali</a>@endsection
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
  <div class="card lg:col-span-2">
    <x-cover :event="$event" class="h-56 rounded-xl"/>
    <div class="mt-4 flex flex-wrap items-center gap-2"><h2 class="text-xl font-bold">{{ $event->judul }}</h2><x-badge :s="$event->status"/></div>
    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
      <div><dt class="text-xs text-gray-500">Kategori</dt><dd class="font-medium">{{ $event->category->nama_kategori }}</dd></div>
      <div><dt class="text-xs text-gray-500">Tanggal pelaksanaan</dt><dd class="font-medium">{{ $event->tanggal_pelaksanaan->format('d M Y') }}</dd></div>
      <div><dt class="text-xs text-gray-500">Lokasi</dt><dd class="font-medium">{{ $event->lokasi }}</dd></div>
      <div><dt class="text-xs text-gray-500">Target dana</dt><dd class="font-medium">@rp($event->target_dana)</dd></div></dl>
    <h3 class="mt-5 font-semibold">Deskripsi</h3><p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $event->deskripsi }}</p>
    <h3 class="mt-5 font-semibold">Benefit sponsor</h3><p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $event->benefit_sponsor }}</p>
  </div>
  <div class="space-y-6">
    <div class="card text-sm"><h3 class="font-semibold">Organizer</h3><p class="mt-2">{{ $event->organizer->nama_organisasi }} ({{ $event->organizer->jenis }})</p>
      <p class="text-gray-500">{{ $event->organizer->user->name }} · {{ $event->organizer->kontak }}</p><div class="mt-1"><x-badge :s="$event->organizer->user->status_verifikasi"/></div></div>
    @if($event->status === 'Menunggu Verifikasi')
    <form method="POST" action="{{ route('admin.proposal.verifikasi', $event) }}" class="card space-y-3" x-data="{s:'Disetujui'}">@csrf
      <h3 class="font-semibold">Keputusan</h3>
      <div class="flex gap-4 text-sm"><label><input type="radio" name="status" value="Disetujui" x-model="s"> Setujui</label><label><input type="radio" name="status" value="Ditolak" x-model="s"> Tolak</label></div>
      <textarea name="catatan" rows="4" x-bind:required="s==='Ditolak'" class="input" placeholder="Catatan perbaikan (wajib jika ditolak)"></textarea>
      <button class="btn btn-primary w-full" x-text="s==='Disetujui' ? 'Setujui & Publikasikan' : 'Tolak dengan Catatan'"></button></form>
    @endif
    <div class="card text-sm"><h3 class="font-semibold">Riwayat verifikasi</h3>
      @forelse($event->verifications as $v)<div class="mt-2 border-t border-gray-100 pt-2"><x-badge :s="$v->status"/> <span class="text-xs text-gray-500">{{ $v->tanggal_verifikasi->format('d M Y H:i') }} · {{ $v->admin->name }}</span>@if($v->catatan)<p class="mt-1 text-gray-600">{{ $v->catatan }}</p>@endif</div>
      @empty<p class="mt-2 text-gray-500">Belum ada.</p>@endforelse</div>
  </div>
</div>
@endsection
