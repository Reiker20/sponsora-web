<!DOCTYPE html>
<html lang="id"><head>@include('partials.head')</head>
<body class="font-sans antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
  <aside class="hidden flex-col justify-between bg-brand p-10 text-white lg:flex">
    <a href="{{ route('home') }}" class="inline-block w-fit rounded-lg bg-white p-2">@include('partials.logo')</a>
    <div>
      <p class="font-semibold text-lime">Cara kerja Sponsora</p>
      <h2 class="mt-3 text-5xl font-semibold leading-tight">Kolaborasi lebih dekat, proses lebih sederhana</h2>
      <p class="mt-4 max-w-md text-white/60">Dari publikasi proposal hingga komitmen dukungan, semua tercatat rapi dalam satu ruang kerja bersama.</p>
      <div class="mt-8 flex gap-3 text-sm font-semibold"><span class="rounded-xl bg-white/10 px-5 py-3">Terverifikasi</span><span class="rounded-xl bg-white/10 px-5 py-3">Transparan</span><span class="rounded-xl bg-white/10 px-5 py-3">Terukur</span></div>
    </div>
    <p class="text-xs text-white/60">© {{ date('Y') }} Sponsora</p>
  </aside>
  <main class="flex items-center justify-center bg-cream px-5 py-10 sm:px-10">
    <div class="w-full max-w-xl">
      <a href="{{ route('home') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-brand">← Kembali ke Beranda</a>
      @include('partials.flash')
      @yield('content')
    </div>
  </main>
</div>
</body></html>
