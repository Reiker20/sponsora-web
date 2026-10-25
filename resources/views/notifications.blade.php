@extends(auth()->user()->isAdmin() || auth()->user()->isOrganizer() || auth()->user()->isSponsor() ? 'layouts.panel' : 'layouts.site')
@section('title', 'Notifikasi')
@section('heading', 'Notifikasi')
@section('sub', 'Perubahan status proposal, komitmen, akun, dan laporan.')
@section('content')
<div class="card divide-y divide-gray-100 !p-0">
  @forelse($items as $n)
    <a href="{{ $n->url ?: '#' }}" class="flex items-start gap-3 px-5 py-4 hover:bg-brand-50/50 {{ $unread->contains($n->id) ? 'bg-lime/10' : '' }}">
      <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $unread->contains($n->id) ? 'bg-brand' : 'bg-gray-300' }}"></span>
      <div><p class="text-sm">{{ $n->pesan }}</p><p class="text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</p></div></a>
  @empty <p class="p-8 text-center text-sm text-gray-500">Belum ada notifikasi.</p> @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
