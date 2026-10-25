@extends('layouts.panel')
@section('title', 'Kepatuhan Pelaporan')
@section('heading', 'Kepatuhan Pelaporan')
@section('sub', 'Event selesai yang belum atau terlambat mengirim laporan.')
@section('content')
<div class="mb-4 flex gap-2 text-xs font-semibold"><a href="{{ route('admin.pelaporan') }}" class="rounded-full px-3 py-1.5 {{ ! $filter ? 'bg-brand text-white' : 'bg-white' }}">Semua</a>
  <a href="{{ route('admin.pelaporan', ['filter' => 'belum']) }}" class="rounded-full px-3 py-1.5 {{ $filter == 'belum' ? 'bg-brand text-white' : 'bg-white' }}">Belum melapor</a>
  <a href="{{ route('admin.pelaporan', ['filter' => 'terlambat']) }}" class="rounded-full px-3 py-1.5 {{ $filter == 'terlambat' ? 'bg-brand text-white' : 'bg-white' }}">Terlambat</a></div>
<div class="card overflow-x-auto !p-0"><table class="min-w-full"><thead class="bg-gray-50"><tr><th class="th">Event</th><th class="th">Organizer</th><th class="th">Batas</th><th class="th">Keuangan</th><th class="th">Hasil Program</th><th class="th">Kepatuhan</th></tr></thead>
<tbody class="divide-y divide-gray-100">@forelse($rows as $r)<tr><td class="td font-medium">{{ $r->event->judul }}</td><td class="td">{{ $r->event->organizer->nama_organisasi }}</td><td class="td">{{ $r->event->batas_laporan?->format('d M Y') }}</td>
  <td class="td"><x-badge :s="$r->keuangan"/></td><td class="td"><x-badge :s="$r->hasil"/></td>
  <td class="td">@if($r->lengkap)<span class="text-xs font-semibold text-emerald-700">Lengkap</span> @elseif($r->terlambat)<x-badge s="Terlambat"/>@else<x-badge s="Menunggu"/>@endif</td></tr>
@empty<tr><td colspan="6" class="td py-8 text-center text-gray-500">Tidak ada data.</td></tr>@endforelse</tbody></table></div>
@endsection
