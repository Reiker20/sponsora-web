<!DOCTYPE html>
<html lang="id"><head>@include('partials.head')</head>
@php
$u = auth()->user();
$menus = [
 'organizer' => [['Dasbor', 'organizer.dashboard', 'organizer.dashboard'], ['Proposal Saya', 'organizer.proposals', 'organizer.proposals'],
   ['Buat Proposal', 'organizer.proposals.create', 'organizer.proposals.create'], ['Komitmen Masuk', 'organizer.komitmen', 'organizer.komitmen*'],
   ['Laporan Pertanggungjawaban', 'organizer.laporan.index', 'organizer.laporan*'], ['Riwayat Kemitraan', 'organizer.riwayat', 'organizer.riwayat'],
   ['Profil', 'organizer.profil', 'organizer.profil*']],
 'sponsor' => [['Dasbor & Riwayat', 'sponsor.dashboard', 'sponsor.dashboard'], ['Jelajahi Event', 'events.index', 'events.*'],
   ['Laporan Program', 'sponsor.laporan.index', 'sponsor.laporan*'], ['Profil Perusahaan', 'sponsor.profil', 'sponsor.profil*']],
 'admin' => [['Dasbor', 'admin.dashboard', 'admin.dashboard'], ['Verifikasi Akun', 'admin.akun', 'admin.akun*'],
   ['Verifikasi Proposal', 'admin.proposal', 'admin.proposal*'], ['Manajemen Kegiatan', 'admin.kegiatan', 'admin.kegiatan*'],
   ['Pemantauan Komitmen', 'admin.komitmen', 'admin.komitmen*'], ['Kepatuhan Pelaporan', 'admin.pelaporan', 'admin.pelaporan*']],
][$u->role];
@endphp
<body class="bg-[#f3f6f2] font-sans text-gray-800 antialiased" x-data="{side:false}">
<div class="flex min-h-screen">
  <div x-show="side" x-cloak @click="side=false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>
  <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto border-r border-gray-100 bg-white p-4 transition lg:translate-x-0" :class="side && '!translate-x-0'">
    <a href="{{ route('home') }}" class="mb-5 block px-2">@include('partials.logo')</a>
    <div class="mb-4 rounded-xl bg-brand-50 p-3 text-xs"><p class="font-semibold text-brand">{{ $u->displayName() }}</p>
      <p class="text-gray-500">{{ ucfirst($u->role) }}</p>@unless($u->isAdmin())<div class="mt-1"><x-badge :s="$u->status_verifikasi"/></div>@endunless</div>
    <nav class="space-y-1 text-sm">
      @foreach ($menus as [$label, $route, $pattern])
        <a href="{{ route($route) }}" class="block rounded-xl px-3 py-2.5 font-medium {{ request()->routeIs($pattern) ? 'bg-brand text-white' : 'text-gray-600 hover:bg-brand-50' }}">{{ $label }}</a>
      @endforeach
      <a href="{{ route('home') }}" class="mt-4 block rounded-xl px-3 py-2.5 text-gray-500 hover:bg-brand-50">← Ke Beranda</a>
    </nav>
  </aside>
  <div class="min-w-0 flex-1 lg:pl-64">
    <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-100 bg-white/90 px-4 backdrop-blur sm:px-6">
      <button type="button" class="grid h-10 w-10 place-items-center rounded-lg lg:hidden" @click="side=true"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <p class="hidden text-sm text-gray-500 lg:block">@yield('crumb', 'Sponsora')</p>
      <div class="flex items-center gap-2">@include('partials.bell') @include('partials.usermenu')</div>
    </header>
    <div class="px-4 pb-10 sm:px-6">
      @include('partials.flash')
      <div class="mt-6 flex flex-wrap items-end justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-brand">@yield('heading')</h1><p class="text-sm text-gray-500">@yield('sub')</p></div>
        <div>@yield('actions')</div>
      </div>
      <div class="mt-6">@yield('content')</div>
    </div>
  </div>
</div>
</body></html>
