@extends('layouts.panel')
@section('title', 'Manajemen Kegiatan')
@section('heading', 'Manajemen Kegiatan')
@section('sub', 'Seluruh event yang terdaftar di platform.')
@section('content')
<form class="mb-4 grid gap-2 sm:grid-cols-[1fr_220px_auto]"><input name="q" value="{{ request('q') }}" placeholder="Cari judul kegiatan" class="input">
  <select name="status" class="input"><option value="">Semua status</option>@foreach(['Draft','Menunggu Verifikasi','Perlu Revisi','Terverifikasi','Selesai','Dilaporkan'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select><button class="btn btn-primary">Filter</button></form>
<div class="card overflow-x-auto !p-0"><table class="min-w-full"><thead class="bg-gray-50"><tr><th class="th">Kegiatan</th><th class="th">Organizer</th><th class="th">Terkumpul</th><th class="th">Mitra</th><th class="th">Status</th><th class="th"></th></tr></thead>
<tbody class="divide-y divide-gray-100">@forelse($events as $e)<tr><td class="td font-medium">{{ $e->judul }}</td><td class="td">{{ $e->organizer->nama_organisasi }}</td>
  <td class="td">@rp($e->dana_terkumpul) <span class="text-xs text-gray-400">/ @rp($e->target_dana)</span></td><td class="td">{{ $e->mitra_count }}</td><td class="td"><x-badge :s="$e->status"/></td>
  <td class="td"><div class="flex justify-end gap-2"><a class="btn btn-outline btn-sm" href="{{ route('events.show', $e) }}">Detail</a>
    <form method="POST" action="{{ route('admin.kegiatan.destroy', $e) }}" onsubmit="return confirm('Hapus kegiatan beserta seluruh komitmen dan laporannya?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Hapus</button></form></div></td></tr>
@empty<tr><td colspan="6" class="td py-8 text-center text-gray-500">Tidak ada kegiatan.</td></tr>@endforelse</tbody></table></div>
@endsection
