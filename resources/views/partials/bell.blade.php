@auth
@php $unread = auth()->user()->notifikasi()->where('status_baca', false)->count(); @endphp
<a href="{{ route('notifikasi') }}" class="relative grid h-10 w-10 place-items-center rounded-full bg-white shadow-sm hover:bg-brand-50" title="Notifikasi">
  <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8M10 20a2 2 0 0 0 4 0"/></svg>
  @if ($unread)<span class="absolute -right-0.5 -top-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $unread }}</span>@endif
</a>
@endauth
