@extends('layouts.panel')
@section('title', 'Dasbor Administrasi')
@section('crumb', 'Dasbor Administrasi')
@section('heading', 'Dasbor Administrasi')
@section('sub', 'Pantau seluruh aktivitas platform Sponsora.')
@section('content')

<div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
  <x-stat label="Akun Menunggu" :value="$stats['akun']"/>
  <x-stat label="Proposal Menunggu" :value="$stats['proposal']"/>
  <x-stat label="Kegiatan Aktif" :value="$stats['aktif']"/>
  <x-stat label="Komitmen Diterima" :value="'Rp '.number_format($stats['dana'], 0, ',', '.')"/>
  <x-stat label="Laporan Terlambat" :value="$stats['terlambat']"/>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
  <div class="card lg:col-span-2">
    <h2 class="font-semibold">Dana tercatat per bulan</h2>
    <div class="mt-4">
      <x-chart :data="$chart"/>
    </div>
  </div>

  <div class="card">
    <h2 class="font-semibold">Kegiatan per kategori</h2>
    <ul class="mt-3 space-y-2 text-sm">
      @foreach($perKategori as $k)
        <li>
          <div class="flex justify-between">
            <span>{{ $k->category->nama_kategori }}</span>
            <b>{{ $k->total }}</b>
          </div>
          <div class="h-1.5 rounded-full bg-gray-100">
            <div class="h-1.5 rounded-full bg-brand" @style(['width: '.min(100, $k->total * 20).'%'])></div>
          </div>
        </li>
      @endforeach
    </ul>
  </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
  <div class="card">
    <div class="flex justify-between">
      <h2 class="font-semibold">Antrean proposal</h2>
      <a href="{{ route('admin.proposal') }}" class="text-sm font-semibold text-brand">Semua →</a>
    </div>
    <ul class="mt-3 divide-y divide-gray-100 text-sm">
      @forelse($antrean as $e)
        <li class="flex items-center justify-between py-2.5">
          <div>
            <p class="font-medium">{{ $e->judul }}</p>
            <p class="text-xs text-gray-500">{{ $e->organizer->nama_organisasi }}</p>
          </div>
          <a class="btn btn-outline btn-sm" href="{{ route('admin.proposal.show', $e) }}">Tinjau</a>
        </li>
      @empty
        <li class="py-3 text-gray-500">Tidak ada antrean.</li>
      @endforelse
    </ul>
  </div>

  <div class="card">
    <div class="flex justify-between">
      <h2 class="font-semibold">Akun menunggu verifikasi</h2>
      <a href="{{ route('admin.akun') }}" class="text-sm font-semibold text-brand">Semua →</a>
    </div>
    <ul class="mt-3 divide-y divide-gray-100 text-sm">
      @forelse($akunBaru as $a)
        <li class="flex items-center justify-between py-2.5">
          <div>
            <p class="font-medium">{{ $a->displayName() }}</p>
            <p class="text-xs text-gray-500">{{ ucfirst($a->role) }} · {{ $a->email }}</p>
          </div>
          <a class="btn btn-outline btn-sm" href="{{ route('admin.akun') }}">Tinjau</a>
        </li>
      @empty
        <li class="py-3 text-gray-500">Tidak ada antrean.</li>
      @endforelse
    </ul>
  </div>
</div>

@endsection