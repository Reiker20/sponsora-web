@extends('layouts.site')
@section('title', 'Notifikasi')
@section('content')
<div class="w-full px-4 py-10 sm:px-8 lg:px-12">
  <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="text-sm text-gray-500 hover:text-brand">← Kembali ke Dasbor</a>
  <h1 class="mt-4 text-3xl font-bold text-brand">Notifikasi</h1>
  <p class="mt-1 text-sm text-gray-500">Perubahan status proposal, komitmen, akun, dan laporan.</p>

  <div class="card mt-6 divide-y divide-gray-100 overflow-hidden !p-0">
    @forelse($items as $n)
      <a href="{{ $n->url ?: '#' }}" class="flex items-start gap-3 px-5 py-4 hover:bg-brand-50/50 {{ $unread->contains($n->id) ? 'bg-lime/10' : '' }}">
        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $unread->contains($n->id) ? 'bg-brand' : 'bg-gray-300' }}"></span>
        <div><p class="text-sm">{{ $n->pesan }}</p><p class="text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</p></div></a>
    @empty
      <p class="p-8 text-center text-sm text-gray-500">Belum ada notifikasi.</p>
    @endforelse
  </div>
  <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection