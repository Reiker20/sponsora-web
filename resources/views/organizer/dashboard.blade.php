@extends('layouts.panel')
@section('title', 'Dashboard Organizer')
@section('crumb', 'Dashboard Organizer')
@section('heading', 'Selamat Datang, ' . auth()->user()->displayName())
@section('sub', 'Pantau komitmen dan temukan peluang dampak baru.')
@section('actions')<a href="{{ route('organizer.proposals.create') }}" class="btn btn-primary">+ Buat Proposal Baru</a>@endsection
@section('content')
@unless(auth()->user()->isVerified())
  <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">Akun Anda berstatus <b>{{ auth()->user()->status_verifikasi }}</b>.
    @if(auth()->user()->catatan_verifikasi) Catatan Admin: {{ auth()->user()->catatan_verifikasi }} @else Anda dapat menyusun draft, tetapi proposal baru bisa diajukan setelah akun disetujui Admin. @endif</div>
@endunless
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
  <x-stat label="Proposal Aktif" :value="$stats['aktif']"/><x-stat label="Menunggu Verifikasi" :value="$stats['menunggu']"/>
  <x-stat label="Komitmen Masuk" :value="$stats['komitmen']"/><x-stat label="Dana Terkumpul" :value="'Rp '.number_format($stats['dana'],0,',','.')"/>
</div>
<div class="mt-6 grid gap-6 lg:grid-cols-3">
  <div class="card lg:col-span-2"><h2 class="font-semibold">Pendanaan 6 bulan terakhir</h2><div class="mt-4"><x-chart :data="$chart"/></div></div>
  <div class="card"><h2 class="font-semibold">Komitmen terbaru</h2>
    <ul class="mt-3 divide-y divide-gray-100 text-sm">
      @forelse($commitments as $c)<li class="flex items-center justify-between gap-2 py-2.5"><div class="min-w-0"><p class="truncate font-medium">{{ $c->sponsor->nama_perusahaan }}</p><p class="truncate text-xs text-gray-500">{{ $c->event->judul }}</p></div>
        <div class="text-right"><p class="font-semibold">@rp($c->nominal)</p><x-badge :s="$c->status"/></div></li>
      @empty<li class="py-4 text-gray-500">Belum ada komitmen.</li>@endforelse</ul>
    <a href="{{ route('organizer.komitmen') }}" class="mt-3 block text-sm font-semibold text-brand">Lihat semua →</a></div>
</div>
<div class="card mt-6 !p-0 overflow-x-auto"><div class="flex items-center justify-between p-5"><h2 class="font-semibold">Proposal terbaru</h2><a href="{{ route('organizer.proposals') }}" class="text-sm font-semibold text-brand">Semua proposal →</a></div>
  <table class="min-w-full"><thead class="bg-gray-50"><tr><th class="th">Event</th><th class="th">Tanggal</th><th class="th">Terkumpul</th><th class="th">Status</th><th class="th"></th></tr></thead>
  <tbody class="divide-y divide-gray-100">@foreach($events as $e)<tr><td class="td font-medium">{{ $e->judul }}</td><td class="td">{{ $e->tanggal_pelaksanaan->format('d M Y') }}</td><td class="td">@rp($e->dana_terkumpul)</td><td class="td"><x-badge :s="$e->status"/></td>
    <td class="td text-right"><a class="font-semibold text-brand" href="{{ route('events.show', $e) }}">Detail</a></td></tr>@endforeach</tbody></table></div>
@endsection