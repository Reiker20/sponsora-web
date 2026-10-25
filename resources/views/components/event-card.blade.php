@props(['event'])
<a href="{{ route('events.show', $event) }}" class="group block overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
  <div class="relative">
    <x-cover :event="$event" class="h-44"/>
    <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-0.5 text-xs font-semibold text-brand">{{ $event->category->nama_kategori }}</span>
  </div>
  <div class="p-4">
    <p class="truncate text-xs text-gray-500">{{ $event->organizer->nama_organisasi }}</p>
    <h3 class="mt-1 line-clamp-1 text-lg font-semibold text-gray-900">{{ $event->judul }}</h3>
    <div class="mt-3 flex justify-between text-xs text-gray-500">
      <span>Terkumpul <b class="text-brand">@rp($event->dana_terkumpul)</b></span><span>dari @rp($event->target_dana)</span></div>
    <div class="mt-1.5 h-1.5 rounded-full bg-gray-100"><div class="h-1.5 rounded-full bg-lime" style="width: {{ $event->progress }}%"></div></div>
    <div class="mt-3 flex justify-between text-xs text-gray-500"><span>{{ $event->sisa_hari }} hari lagi</span><span class="font-semibold text-brand group-hover:underline">Lihat detail →</span></div>
  </div>
</a>
