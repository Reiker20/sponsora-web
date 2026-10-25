@extends('layouts.site')
@section('title', 'Satu Ruang untuk Dampak Nyata')
@section('content')
@php $u = auth()->user(); @endphp
{{-- HERO --}}
<section class="relative overflow-hidden">
  <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:py-20">
    <div>
      <span class="inline-block rounded-full bg-lime/40 px-3 py-1 text-xs font-semibold text-brand">Menghubungkan kebaikan dengan dukungan nyata</span>
      <h1 class="mt-5 text-4xl font-bold leading-tight text-brand sm:text-5xl">Satu Ruang Untuk <span class="underline decoration-lime decoration-8 underline-offset-[-2px]">Dampak Nyata</span></h1>
      <p class="mt-5 max-w-lg text-gray-600">Sponsora mempertemukan kegiatan sosial terverifikasi dengan perusahaan yang ingin menyalurkan program CSR secara mudah, aman, dan transparan.</p>
      <div class="mt-7 flex flex-wrap gap-3">
        @if(!$u || $u->isSponsor() || $u->isAdmin())<a href="{{ route('events.index') }}" class="btn btn-primary">{{ $u ? 'Temukan Event' : 'Temukan event' }} →</a>@endif
        @if(!$u)<a href="{{ route('register', ['role' => 'organizer']) }}" class="btn btn-outline">Ajukan proposal</a>@endif
        @if($u?->isOrganizer())<a href="{{ route('organizer.proposals.create') }}" class="btn btn-primary">Ajukan proposal →</a><a href="{{ route('events.index') }}" class="btn btn-outline">Lihat Katalog</a>@endif
      </div>
      <div class="mt-5 flex flex-wrap gap-5 text-xs text-gray-500"><span>✓ Proposal terverifikasi</span><span>✓ Terpantau transparan</span></div>
    </div>
    @if($featured)
    <a href="{{ route('events.show', $featured) }}" class="relative mx-auto block w-full max-w-md rotate-2 rounded-3xl bg-white p-2 shadow-2xl transition hover:rotate-0">
      <x-cover :event="$featured" class="h-72 rounded-2xl"/>
      <div class="absolute inset-x-2 bottom-2 rounded-b-2xl bg-gradient-to-t from-black/70 to-transparent p-5 pt-16 text-white">
        <span class="rounded-full bg-lime px-2 py-0.5 text-[10px] font-bold text-brand">{{ $featured->category->nama_kategori }}</span>
        <h3 class="mt-1 text-xl font-semibold">{{ $featured->judul }}</h3>
      </div>
      <div class="p-4"><p class="text-xs text-gray-500">Dana Terkumpul</p><p class="text-xl font-bold">@rp($featured->dana_terkumpul)</p>
        <div class="mt-2 h-1.5 rounded-full bg-gray-100"><div class="h-1.5 rounded-full bg-lime" style="width: {{ $featured->progress }}%"></div></div></div>
    </a>
    @endif
  </div>
</section>

{{-- STATISTIK --}}
<section class="border-y border-gray-100 bg-white">
  <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 py-8 text-center sm:px-6 md:grid-cols-4">
    <div><p class="text-2xl font-bold text-brand">{{ $stats['event'] }}+</p><p class="text-xs text-gray-500">Event terverifikasi</p></div>
    <div><p class="text-2xl font-bold text-brand">{{ $stats['mitra'] }}</p><p class="text-xs text-gray-500">Mitra perusahaan</p></div>
    <div><p class="text-2xl font-bold text-brand">@rp($stats['dana'])</p><p class="text-xs text-gray-500">Komitmen tercatat</p></div>
    <div><p class="text-2xl font-bold text-brand">{{ $stats['kategori'] }}</p><p class="text-xs text-gray-500">Kategori dampak</p></div>
  </div>
</section>

{{-- KATALOG --}}
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
  <p class="text-sm font-semibold uppercase tracking-wide text-brand/70">Pilihan untukmu</p>
  <div class="flex flex-wrap items-end justify-between gap-3"><h2 class="mt-1 text-3xl font-bold text-brand sm:text-4xl">Aksi baik yang<br>menanti dukungan</h2>
    <a href="{{ route('events.index') }}" class="text-sm font-semibold text-brand hover:underline">Lihat Semua Event →</a></div>
  <p class="mt-2 max-w-md text-sm text-gray-500">Jelajahi kegiatan terverifikasi dan temukan yang sejalan dengan fokus CSR perusahaanmu.</p>
  <form action="{{ route('events.index') }}" class="mt-6 flex flex-col gap-2 rounded-2xl border border-gray-200 bg-white p-2 sm:flex-row">
    <input name="q" placeholder="Cari nama kegiatan atau organisasi..." class="min-w-0 flex-1 rounded-xl border-0 px-4 py-2.5 text-sm focus:outline-none focus:ring-0">
    <select name="kategori" class="rounded-xl border border-gray-200 px-3 py-2 text-sm"><option value="">Semua kategori</option>
      @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->nama_kategori }}</option>@endforeach</select>
    <button class="btn btn-primary">Cari</button>
  </form>
  <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($events as $e)<x-event-card :event="$e"/>@empty<p class="text-gray-500">Belum ada event terverifikasi.</p>@endforelse
  </div>
</section>

{{-- CARA KERJA --}}
<section id="cara-kerja" class="bg-brand py-16 text-white" x-data="{tab:'sponsor'}">
  <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
    <div>
      <p class="font-semibold text-lime">Cara kerja Sponsora</p>
      <h2 class="mt-2 text-4xl font-semibold leading-tight">Kolaborasi lebih dekat, proses lebih sederhana</h2>
      <p class="mt-3 max-w-md text-white/60">Dari publikasi proposal hingga komitmen dukungan, semua tercatat rapi dalam satu ruang kerja bersama.</p>
      <div class="mt-6 inline-flex rounded-full bg-white/10 p-1 text-sm font-semibold">
        <button type="button" @click="tab='sponsor'" :class="tab==='sponsor' ? 'bg-lime text-brand' : 'text-white/80'" class="rounded-full px-4 py-1.5">Untuk Sponsor</button>
        <button type="button" @click="tab='organizer'" :class="tab==='organizer' ? 'bg-lime text-brand' : 'text-white/80'" class="rounded-full px-4 py-1.5">Untuk Organizer</button></div>
    </div>
    <div class="space-y-6">
      @php $steps = ['sponsor' => [['Temukan Event yang Relevan', 'Cari dan saring kegiatan terverifikasi berdasarkan kategori dan kebutuhan dana.'], ['Pelajari dan Ajukan Komitmen', 'Lihat proposal, benefit, dan progres event sebelum mengajukan nominal dukungan.'], ['Pantau Dampak secara Transparan', 'Pantau progres dana dan terima laporan pertanggungjawaban dari penyelenggara.']],
        'organizer' => [['Daftar dan Buat Proposal', 'Susun proposal kegiatan lengkap dengan kebutuhan dana dan benefit sponsor.'], ['Lolos Verifikasi Admin', 'Admin memeriksa akun dan proposal sebelum tayang di katalog.'], ['Kelola Komitmen dan Laporkan', 'Terima, tolak, atau negosiasikan komitmen, lalu kirim laporan setelah event selesai.']]]; @endphp
      @foreach($steps as $k => $list)
        <div x-show="tab==='{{ $k }}'" x-cloak class="space-y-6">
          @foreach($list as $i => [$t, $d])
            <div class="flex gap-4"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-lime text-sm font-bold text-lime">0{{ $i + 1 }}</span>
              <div><h3 class="font-semibold">{{ $t }}</h3><p class="text-sm text-white/60">{{ $d }}</p></div></div>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- FITUR --}}
<section id="fitur" class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6">
  <p class="text-sm font-semibold text-brand/70">Semua dalam satu platform</p>
  <h2 class="mt-1 text-3xl font-bold text-brand sm:text-4xl">Dirancang untuk Kemitraan<br>yang tepercaya</h2>
  <p class="mx-auto mt-3 max-w-lg text-sm text-gray-500">Tak lagi tercecer di email, WhatsApp, dan spreadsheet. Sponsora membuat setiap langkah lebih jelas.</p>
  <div class="mx-auto mt-10 grid max-w-4xl gap-5 text-left sm:grid-cols-2">
    @foreach([['Kegiatan Terverifikasi', 'Admin memeriksa akun dan proposal sebelum kegiatan dipublikasikan ke Sponsor.'], ['Status Transparan', 'Perkembangan dana dan status komitmen dapat dilihat secara real-time.'], ['Proposal Terpusat', 'Semua proposal tersimpan rapi dan mudah ditemukan dalam satu katalog.'], ['Dokumen Terstruktur', 'Laporan keuangan dan hasil program dikirim, ditinjau, dan diarsipkan di sistem.']] as [$t, $d])
      <div class="card p-6"><span class="mb-3 grid h-10 w-10 place-items-center rounded-xl bg-lime/40 text-brand">✓</span><h3 class="font-semibold text-brand">{{ $t }}</h3><p class="mt-1 text-sm text-gray-500">{{ $d }}</p></div>
    @endforeach
  </div>
</section>

{{-- CSR --}}
<section class="mx-auto max-w-7xl px-4 sm:px-6">
  <div class="grid items-center overflow-hidden rounded-3xl bg-lime/60 lg:grid-cols-2">
    <div class="p-8 sm:p-12"><p class="text-sm font-semibold text-brand/70">Sesuai tujuan, tepat sasaran</p>
      <h2 class="mt-2 text-3xl font-bold text-brand">Dari program CSR menjadi cerita perubahan.</h2>
      <p class="mt-3 text-sm text-brand/70">Perusahaan dapat memilih kegiatan yang selaras dengan fokus CSR, sementara penyelenggara memperoleh dukungan yang terdokumentasi dan bisa dipertanggungjawabkan.</p></div>
    <div class="h-56 bg-gradient-to-br from-brand to-emerald-600 lg:h-full"></div>
  </div>
</section>

{{-- CTA --}}
@guest
<section class="mx-auto mt-16 max-w-7xl px-4 sm:px-6">
  <div class="rounded-3xl bg-lime p-10 text-center sm:p-14">
    <p class="text-sm font-semibold text-brand/70">Mulai berkolaborasi</p>
    <h2 class="mt-2 text-3xl font-bold text-brand sm:text-4xl">Dampak besar selalu dimulai dari<br>satu koneksi.</h2>
    <div class="mt-7 flex flex-wrap justify-center gap-3">
      <a href="{{ route('register', ['role' => 'organizer']) }}" class="btn btn-primary">Daftar sebagai Organizer →</a>
      <a href="{{ route('register', ['role' => 'sponsor']) }}" class="btn btn-outline">Daftar sebagai Sponsor</a></div>
  </div>
</section>
@endguest
@endsection
