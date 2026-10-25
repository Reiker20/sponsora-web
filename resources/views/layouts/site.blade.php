<!DOCTYPE html>
<html lang="id"><head>@include('partials.head')</head>
<body class="font-sans text-gray-800 antialiased">
<header class="sticky top-0 z-40 border-b border-brand/5 bg-cream/90 backdrop-blur" x-data="{m:false}">
  <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
    <a href="{{ route('home') }}">@include('partials.logo')</a>
    <nav class="hidden items-center gap-8 text-sm font-medium lg:flex">
      <a href="{{ route('home') }}" class="hover:text-brand">Beranda</a>
      <a href="{{ route('events.index') }}" class="hover:text-brand">Jelajahi Event</a>
      <a href="{{ route('home') }}#cara-kerja" class="hover:text-brand">Cara Kerja</a>
      <a href="{{ route('home') }}#fitur" class="hover:text-brand">Fitur</a>
      <a href="{{ route('home') }}#tentang" class="hover:text-brand">Tentang Kami</a>
    </nav>
    <div class="flex items-center gap-2">
      @auth
        @include('partials.bell') @include('partials.usermenu')
      @else
        <a href="{{ route('login') }}" class="hidden px-3 text-sm font-medium sm:block">Masuk</a>
        <a href="{{ route('register') }}" class="btn btn-primary btn-sm !rounded-full !px-4">Daftar Sekarang</a>
      @endauth
      <button type="button" class="ml-1 grid h-10 w-10 place-items-center rounded-lg lg:hidden" @click="m=!m" aria-label="Menu">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
    </div>
  </div>
  <nav x-show="m" x-cloak class="space-y-1 border-t border-gray-100 bg-cream px-4 py-3 text-sm font-medium lg:hidden">
    <a class="block rounded-lg px-3 py-2 hover:bg-brand-50" href="{{ route('home') }}">Beranda</a>
    <a class="block rounded-lg px-3 py-2 hover:bg-brand-50" href="{{ route('events.index') }}">Jelajahi Event</a>
    <a class="block rounded-lg px-3 py-2 hover:bg-brand-50" href="{{ route('home') }}#cara-kerja">Cara Kerja</a>
    <a class="block rounded-lg px-3 py-2 hover:bg-brand-50" href="{{ route('home') }}#fitur">Fitur</a>
    @guest<a class="block rounded-lg px-3 py-2 hover:bg-brand-50" href="{{ route('login') }}">Masuk</a>@endguest
  </nav>
</header>
<div class="px-4 sm:px-6">@include('partials.flash')</div>
<main>@yield('content')</main>
<footer class="mt-20 bg-cream" id="tentang">
  <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3">
    <div><div>@include('partials.logo')</div>
      <p class="mt-3 max-w-xs text-sm text-gray-600">Sponsora mempertemukan penyelenggara kegiatan sosial dengan perusahaan pemilik program CSR. Mendukung SDG 17: Kemitraan untuk Mencapai Tujuan.</p></div>
    <div class="grid grid-cols-2 gap-6 text-sm md:col-span-2 md:grid-cols-3">
      <div><p class="font-semibold">Platform</p><ul class="mt-3 space-y-2 text-gray-600"><li><a href="{{ route('events.index') }}">Jelajahi Event</a></li><li><a href="{{ route('home') }}#cara-kerja">Cara Kerja</a></li><li><a href="{{ route('home') }}#fitur">Fitur</a></li></ul></div>
      <div><p class="font-semibold">Perusahaan</p><ul class="mt-3 space-y-2 text-gray-600"><li>Tentang Kami</li><li>Pusat Bantuan</li><li>Hubungi Kami</li></ul></div>
      <div><p class="font-semibold">Legal</p><ul class="mt-3 space-y-2 text-gray-600"><li>Kebijakan Privasi</li><li>Syarat &amp; Ketentuan</li><li><a href="{{ route('admin.login') }}">Login Admin</a></li></ul></div>
    </div>
  </div>
  <div class="bg-brand py-3 text-center text-xs text-white/80">© {{ date('Y') }} Sponsora. Kolaborasi untuk kebaikan.</div>
</footer>
</body></html>
