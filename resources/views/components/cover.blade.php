@props(['event'])
@php
$grad = ['Lingkungan' => 'from-emerald-700 to-lime', 'Pendidikan' => 'from-amber-600 to-yellow-300', 'Kesehatan' => 'from-rose-600 to-orange-300',
         'Sosial' => 'from-sky-700 to-cyan-300', 'Olahraga' => 'from-indigo-700 to-sky-300', 'Kebudayaan' => 'from-fuchsia-700 to-pink-300'];
$g = $grad[$event->category->nama_kategori ?? ''] ?? 'from-brand to-lime';
@endphp
@if ($event->cover_url)
  <img src="{{ $event->cover_url }}" alt="{{ $event->judul }}" {{ $attributes->merge(['class' => 'w-full object-cover']) }}>
@else
  <div {{ $attributes->merge(['class' => "w-full bg-gradient-to-br $g grid place-items-center"]) }}>
    <span class="px-4 text-center text-xl font-bold text-white/90 drop-shadow">{{ $event->category->nama_kategori ?? '' }}</span>
  </div>
@endif
