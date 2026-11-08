@extends('layouts.panel')
@section('title', 'Proposal Saya')
@section('heading', 'Proposal Saya')
@section('sub', 'Kelola seluruh proposal event Anda.')
@section('actions')<a href="{{ route('organizer.proposals.create') }}" class="btn btn-primary">+ Buat Proposal</a>@endsection
@section('content')
<div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
  <a href="{{ route('organizer.proposals') }}" class="rounded-full px-3 py-1.5 {{ ! request('status') ? 'bg-brand text-white' : 'bg-white' }}">Semua</a>
  @foreach(['Draft','Menunggu Verifikasi','Perlu Revisi','Terverifikasi','Selesai','Dilaporkan'] as $s)
    <a href="{{ route('organizer.proposals', ['status' => $s]) }}" class="rounded-full px-3 py-1.5 {{ request('status') == $s ? 'bg-brand text-white' : 'bg-white' }}">{{ $s }}</a>@endforeach</div>
<div class="space-y-3">
@forelse($events as $e)
  <div class="card flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-semibold">{{ $e->judul }}</h3><x-badge :s="$e->status"/></div>
      <p class="mt-1 text-xs text-gray-500">{{ $e->category->nama_kategori }} · {{ $e->tanggal_pelaksanaan->format('d M Y') }} · Target @rp($e->target_dana) · Terkumpul @rp($e->dana_terkumpul)</p>
      @if($e->status === 'Perlu Revisi' && $e->verifications->first())<p class="mt-2 rounded-lg bg-orange-50 p-2 text-xs text-orange-700">Catatan Admin: {{ $e->verifications->first()->catatan }}</p>@endif</div>
    <div class="flex flex-wrap gap-2">
      <a href="{{ route('events.show', $e) }}" class="btn btn-outline btn-sm">Detail</a>
      @if($e->isEditable())
        <a href="{{ route('organizer.proposals.edit', $e) }}" class="btn btn-outline btn-sm">Ubah</a>
        <form method="POST" action="{{ route('organizer.proposals.ajukan', $e) }}">@csrf<button class="btn btn-primary btn-sm">Ajukan Verifikasi</button></form>
        <form method="POST" action="{{ route('organizer.proposals.destroy', $e) }}" onsubmit="return confirm('Hapus proposal ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Hapus</button></form>
      @endif
      @if($e->status === 'Terverifikasi')
        <form method="POST" action="{{ route('organizer.proposals.selesai', $e) }}" onsubmit="return confirm('Tandai event selesai? Event tidak lagi menerima komitmen dan kewajiban laporan dibuka.')">@csrf<button class="btn btn-lime btn-sm">Tandai Selesai</button></form>
      @endif
      @if(in_array($e->status, ['Selesai','Dilaporkan']))<a href="{{ route('organizer.laporan.show', $e) }}" class="btn btn-lime btn-sm">Laporan</a>@endif
    </div>
  </div>
@empty<p class="card text-center text-sm text-gray-500">Belum ada proposal. Mulai dengan membuat proposal baru.</p>@endforelse
</div>
@endsection