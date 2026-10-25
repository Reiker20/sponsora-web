@extends('layouts.auth')
@section('title', 'Daftar')
@section('content')
@php $csr = ['Lingkungan', 'Pendidikan', 'Kesehatan', 'Sosial', 'Olahraga', 'Kebudayaan']; @endphp
<div x-data="{role: '{{ old('role', request('role') === 'organizer' ? 'organizer' : 'sponsor') }}', file: ''}">
  <h1 class="text-3xl font-bold">Mulai Bersama Sponsora</h1>
  <p class="mt-1 text-xs text-gray-500">Pilih peran dan lengkapi informasi akunmu</p>
  <div class="mt-5 grid grid-cols-2 gap-1 rounded-2xl bg-brand-50 p-1.5 text-sm font-semibold">
    <button type="button" @click="role='sponsor'" :class="role==='sponsor' ? 'bg-white shadow text-brand' : 'text-brand/70'" class="rounded-xl py-3">🏢 Sponsor</button>
    <button type="button" @click="role='organizer'" :class="role==='organizer' ? 'bg-white shadow text-brand' : 'text-brand/70'" class="rounded-xl py-3">👥 Organizer</button></div>

  <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="mt-6 grid gap-4 sm:grid-cols-2">@csrf
    <input type="hidden" name="role" :value="role">

    <fieldset :disabled="role!=='sponsor'" x-show="role==='sponsor'" class="contents">
      <div><label class="label">Nama perusahaan *</label><input name="nama_perusahaan" value="{{ old('nama_perusahaan') }}" class="input" placeholder="PT Nusa Karya"></div>
      <div><label class="label">NIB perusahaan *</label><input name="nib" value="{{ old('nib') }}" class="input" placeholder="9120401234567"></div>
    </fieldset>
    <fieldset :disabled="role!=='organizer'" x-show="role==='organizer'" x-cloak class="contents">
      <div><label class="label">Nama organisasi *</label><input name="nama_organisasi" value="{{ old('nama_organisasi') }}" class="input" placeholder="Komunitas Akar Bumi"></div>
      <div><label class="label">Jenis organisasi *</label><select name="jenis" class="input">@foreach(['HIMA/UKM','NGO','NPO','Komunitas'] as $j)<option @selected(old('jenis')==$j)>{{ $j }}</option>@endforeach</select></div>
    </fieldset>

    <div><label class="label">Nama penanggung jawab *</label><input name="name" value="{{ old('name') }}" required class="input"></div>
    <div><label class="label">Nomor telepon *</label><input name="kontak" value="{{ old('kontak') }}" required class="input" placeholder="0812 3456 7890"></div>

    <fieldset :disabled="role!=='organizer'" x-show="role==='organizer'" x-cloak class="contents">
      <div class="sm:col-span-2"><label class="label">Alamat organisasi *</label><input name="alamat" value="{{ old('alamat') }}" class="input"></div>
    </fieldset>

    <div><label class="label">E-Mail *</label><input type="email" name="email" value="{{ old('email') }}" required class="input"></div>
    <div><label class="label">Kata sandi *</label><input type="password" name="password" required class="input"><p class="mt-1 text-[11px] text-gray-400">Minimal 8 karakter, huruf dan angka.</p></div>

    <fieldset :disabled="role!=='sponsor'" x-show="role==='sponsor'" class="contents">
      <div class="sm:col-span-2"><label class="label">Fokus CSR * (pilih satu atau lebih)</label>
        <div class="flex flex-wrap gap-2">@foreach($csr as $c)<label class="cursor-pointer"><input type="checkbox" name="fokus_csr[]" value="{{ $c }}" class="peer sr-only" @checked(in_array($c, old('fokus_csr', [])))>
          <span class="inline-block rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white">{{ $c }}</span></label>@endforeach</div></div>
    </fieldset>

    <div class="sm:col-span-2"><label class="label" x-text="role==='sponsor' ? 'Dokumen legalitas perusahaan *' : 'Dokumen legalitas / SK organisasi *'"></label>
      <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-gray-200 bg-white p-4 hover:border-brand">
        <span class="text-xl">⬆</span><span class="min-w-0 flex-1 text-sm"><b class="block truncate" x-text="file || 'Pilih berkas'"></b><span class="text-xs text-gray-400">PDF, JPG, PNG · Maksimal 5 MB per berkas</span></span>
        <input type="file" name="dokumen" required accept=".pdf,.jpg,.jpeg,.png" class="sr-only" @change="file = $event.target.files[0]?.name"></label></div>

    <div class="rounded-xl bg-brand-50 p-4 text-xs text-brand sm:col-span-2"><b x-text="role==='sponsor' ? 'Verifikasi sebelum berkomitmen' : 'Akun menunggu verifikasi'"></b>
      <p class="mt-1 text-brand/80" x-text="role==='sponsor' ? 'Admin memeriksa identitas dan dokumen Anda. Anda dapat mengajukan komitmen setelah akun terverifikasi.' : 'Setelah mendaftar, Admin memeriksa identitas organisasi. Proposal dapat diajukan untuk verifikasi hanya setelah akun disetujui.'"></p></div>
    <label class="flex items-center gap-2 text-xs text-gray-600 sm:col-span-2"><input type="checkbox" name="setuju" value="1" required class="rounded"> Saya menyetujui Ketentuan Penggunaan dan Kebijakan Privasi SPONSORA.</label>
    <button class="btn btn-primary w-full !py-3 sm:col-span-2" x-text="role==='sponsor' ? 'Daftar Sponsor' : 'Daftar Penyelenggara'"></button>
  </form>
  <p class="mt-5 text-center text-sm text-gray-500">Sudah memiliki akun? <a href="{{ route('login') }}" class="font-bold text-brand">Masuk</a></p>
</div>
@endsection
