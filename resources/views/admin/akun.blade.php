@extends('layouts.panel')
@section('title', 'Verifikasi Akun')
@section('heading', 'Verifikasi Akun')
@section('sub', 'Periksa identitas dan dokumen legalitas Organizer dan Sponsor.')
@section('content')
<div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
  @foreach(['Menunggu','Disetujui','Ditolak','Semua'] as $s)<a href="{{ route('admin.akun', ['status' => $s]) }}" class="rounded-full px-3 py-1.5 {{ $status == $s ? 'bg-brand text-white' : 'bg-white' }}">{{ $s }}</a>@endforeach
  <span class="mx-2 text-gray-300">|</span>
  <a href="{{ route('admin.akun', ['status' => $status, 'role' => 'organizer']) }}" class="rounded-full bg-white px-3 py-1.5">Organizer</a><a href="{{ route('admin.akun', ['status' => $status, 'role' => 'sponsor']) }}" class="rounded-full bg-white px-3 py-1.5">Sponsor</a></div>
<div class="space-y-3">
@forelse($users as $u)
  @php $p = $u->profile(); @endphp
  <div class="card" x-data="{ tolak: false }">
    <div class="flex flex-col justify-between gap-3 lg:flex-row">
      <div><p class="font-semibold">{{ $u->displayName() }} <x-badge :s="$u->status_verifikasi"/> <span class="ml-1 rounded-full bg-brand-50 px-2 py-0.5 text-xs text-brand">{{ ucfirst($u->role) }}</span></p>
        <p class="mt-1 text-sm text-gray-600">Penanggung jawab: {{ $u->name }} · {{ $p->kontak }} · {{ $u->email }}</p>
        <p class="text-xs text-gray-500">@if($u->isSponsor()) NIB: {{ $p->nib }} · Fokus CSR: {{ $p->fokus_csr }} @else Jenis: {{ $p->jenis }} · {{ $p->alamat }} @endif · Daftar {{ $u->created_at->format('d M Y') }}</p>
        @if($p->dokumen_legalitas)<a href="{{ route('files.show', ['legalitas', $u->id]) }}" target="_blank" class="mt-2 inline-block text-sm font-semibold text-brand underline">📄 Lihat dokumen legalitas</a>@endif
        @if($u->catatan_verifikasi)<p class="mt-1 text-xs text-red-600">Catatan: {{ $u->catatan_verifikasi }}</p>@endif</div>
      <div class="flex flex-wrap items-start gap-2">
        @if($u->status_verifikasi !== 'Disetujui')<form method="POST" action="{{ route('admin.akun.verifikasi', $u) }}">@csrf<input type="hidden" name="status" value="Disetujui"><button class="btn btn-primary btn-sm">Setujui</button></form>@endif
        @if($u->status_verifikasi !== 'Ditolak')<button type="button" @click="tolak=!tolak" class="btn btn-danger btn-sm">Tolak</button>@endif</div></div>
    <form x-show="tolak" x-cloak method="POST" action="{{ route('admin.akun.verifikasi', $u) }}" class="mt-3 flex flex-col gap-2 rounded-xl bg-red-50 p-3 sm:flex-row">@csrf<input type="hidden" name="status" value="Ditolak">
      <input name="catatan" required placeholder="Alasan penolakan" class="input"><button class="btn btn-danger btn-sm">Konfirmasi</button></form>
  </div>
@empty<p class="card text-center text-sm text-gray-500">Tidak ada akun pada filter ini.</p>@endforelse
</div>
@endsection