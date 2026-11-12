@extends('layouts.panel')
@section('title', 'Riwayat Kemitraan')
@section('heading', 'Riwayat Kemitraan')
@section('sub', 'Rekam jejak kerja sama sponsorship yang sudah terjalin.')
@section('content')
<div class="grid gap-4 sm:grid-cols-3"><x-stat label="Total Kemitraan" :value="$items->count()"/><x-stat label="Total Dana Diterima" :value="'Rp '.number_format($items->sum('nominal'),0,',','.')"/>
  <x-stat label="Sponsor Berbeda" :value="$items->pluck('sponsor_id')->unique()->count()"/></div>
<div class="card mt-6 overflow-x-auto !p-0"><table class="min-w-full"><thead class="bg-gray-50"><tr><th class="th">Sponsor</th><th class="th">Event</th><th class="th">Nominal</th><th class="th">Bentuk</th><th class="th">Tanggal</th></tr></thead>
<tbody class="divide-y divide-gray-100">@forelse($items as $i)<tr><td class="td font-medium">{{ $i->sponsor->nama_perusahaan }}</td><td class="td"><a class="text-brand underline" href="{{ route('events.show', $i->event) }}">{{ $i->event->judul }}</a></td>
  <td class="td font-semibold">@rp($i->nominal)</td><td class="td">{{ $i->bentuk_dukungan }}</td><td class="td">{{ $i->updated_at->format('d M Y') }}</td></tr>
@empty<tr><td colspan="5" class="td py-8 text-center text-gray-500">Belum ada riwayat.</td></tr>@endforelse</tbody></table></div>
@endsection