@extends('layouts.site')
@section('title', 'Jelajahi Event')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
  <h1 class="text-3xl font-bold text-brand">Jelajahi Event</h1>
  <p class="mt-1 text-sm text-gray-500">Kegiatan terverifikasi yang sedang membutuhkan dukungan.</p>
  <form class="mt-6 grid gap-2 rounded-2xl border border-gray-200 bg-white p-3 md:grid-cols-[1fr_200px_200px_auto]">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama kegiatan atau organisasi..." class="input">
    <select name="kategori" class="input"><option value="">Semua kategori</option>
      @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('kategori') == $c->id)>{{ $c->nama_kategori }}</option>@endforeach</select>
    <select name="dana" class="input"><option value="">Semua kebutuhan dana</option>
      <option value="kecil" @selected(request('dana') == 'kecil')>&lt; Rp 10 jt</option>
      <option value="sedang" @selected(request('dana') == 'sedang')>Rp 10–50 jt</option>
      <option value="besar" @selected(request('dana') == 'besar')>&gt; Rp 50 jt</option></select>
    <button class="btn btn-primary">Terapkan</button>
  </form>
  <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($events as $e)<x-event-card :event="$e"/>@empty
      <p class="col-span-full rounded-2xl bg-white p-10 text-center text-gray-500">Tidak ada event yang cocok dengan pencarian Anda.</p>@endforelse
  </div>
  <div class="mt-8">{{ $events->links() }}</div>
</div>
@endsection