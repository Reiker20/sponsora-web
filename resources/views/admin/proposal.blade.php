@extends('layouts.panel')
@section('title', 'Verifikasi Proposal')
@section('heading', 'Verifikasi Proposal')
@section('sub', 'Tinjau kelengkapan proposal sebelum tayang di katalog.')
@section('content')
<div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">@foreach(['Menunggu Verifikasi','Terverifikasi','Perlu Revisi','Semua'] as $s)<a href="{{ route('admin.proposal', ['status' => $s]) }}" class="rounded-full px-3 py-1.5 {{ $status == $s ? 'bg-brand text-white' : 'bg-white' }}">{{ $s }}</a>@endforeach</div>
<div class="card overflow-x-auto !p-0"><table class="min-w-full"><thead class="bg-gray-50"><tr><th class="th">Proposal</th><th class="th">Organizer</th><th class="th">Kategori</th><th class="th">Target</th><th class="th">Status</th><th class="th"></th></tr></thead>
<tbody class="divide-y divide-gray-100">@forelse($events as $e)<tr><td class="td font-medium">{{ $e->judul }}</td><td class="td">{{ $e->organizer->nama_organisasi }}</td><td class="td">{{ $e->category->nama_kategori }}</td><td class="td">@rp($e->target_dana)</td><td class="td"><x-badge :s="$e->status"/></td>
  <td class="td text-right"><a class="btn btn-outline btn-sm" href="{{ route('admin.proposal.show', $e) }}">Tinjau</a></td></tr>
@empty<tr><td colspan="6" class="td py-8 text-center text-gray-500">Tidak ada proposal.</td></tr>@endforelse</tbody></table></div>
@endsection
