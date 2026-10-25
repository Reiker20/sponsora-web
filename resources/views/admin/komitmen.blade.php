@extends('layouts.panel')
@section('title', 'Pemantauan Komitmen')
@section('heading', 'Pemantauan Komitmen')
@section('sub', 'Seluruh pengajuan komitmen dana di platform.')
@section('content')
<div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold"><a href="{{ route('admin.komitmen') }}" class="rounded-full bg-white px-3 py-1.5">Semua</a>
  @foreach(['Menunggu Tanggapan','Diterima','Negosiasi','Ditolak'] as $s)<a href="{{ route('admin.komitmen', ['status' => $s]) }}" class="rounded-full px-3 py-1.5 {{ request('status') == $s ? 'bg-brand text-white' : 'bg-white' }}">{{ $s }}</a>@endforeach</div>
<div class="card overflow-x-auto !p-0"><table class="min-w-full"><thead class="bg-gray-50"><tr><th class="th">Sponsor</th><th class="th">Event</th><th class="th">Organizer</th><th class="th">Nominal</th><th class="th">Tanggal</th><th class="th">Status</th></tr></thead>
<tbody class="divide-y divide-gray-100">@forelse($commitments as $c)<tr><td class="td font-medium">{{ $c->sponsor->nama_perusahaan }}</td><td class="td">{{ $c->event->judul }}</td><td class="td">{{ $c->event->organizer->nama_organisasi }}</td>
  <td class="td font-semibold">@rp($c->nominal)</td><td class="td">{{ $c->tanggal_pengajuan->format('d M Y') }}</td><td class="td"><x-badge :s="$c->status"/></td></tr>
@empty<tr><td colspan="6" class="td py-8 text-center text-gray-500">Belum ada komitmen.</td></tr>@endforelse</tbody></table></div>
@endsection
