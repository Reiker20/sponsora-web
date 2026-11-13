@extends('layouts.panel')
@section('title', 'Profil Perusahaan')
@section('heading', 'Profil Perusahaan')
@section('sub', 'Informasi ini terlihat oleh Organizer.')
@section('content')
<form method="POST" action="{{ route('sponsor.profil.update') }}" class="card grid max-w-3xl gap-4 sm:grid-cols-2">@csrf @method('PUT')
  <div><label class="label">Nama perusahaan *</label><input name="nama_perusahaan" value="{{ old('nama_perusahaan', $p->nama_perusahaan) }}" required class="input"></div>
  <div><label class="label">NIB</label><input value="{{ $p->nib }}" disabled class="input bg-gray-50"></div>
  <div><label class="label">Nama penanggung jawab *</label><input name="name" value="{{ old('name', $user->name) }}" required class="input"></div>
  <div><label class="label">Nomor telepon *</label><input name="kontak" value="{{ old('kontak', $p->kontak) }}" required class="input"></div>
  <div class="sm:col-span-2"><label class="label">Alamat</label><input name="alamat" value="{{ old('alamat', $p->alamat) }}" class="input"></div>
  @php $sel = array_map('trim', explode(',', $p->fokus_csr ?? '')); @endphp
  <div class="sm:col-span-2"><label class="label">Fokus CSR *</label><div class="flex flex-wrap gap-2">
    @foreach(['Lingkungan','Pendidikan','Kesehatan','Sosial','Olahraga','Kebudayaan'] as $c)<label class="cursor-pointer"><input type="checkbox" name="fokus_csr[]" value="{{ $c }}" class="peer sr-only" @checked(in_array($c, old('fokus_csr', $sel)))>
      <span class="inline-block rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium peer-checked:border-brand peer-checked:bg-brand peer-checked:text-white">{{ $c }}</span></label>@endforeach</div></div>
  <div class="text-xs text-gray-500 sm:col-span-2">Email: {{ $user->email }} · Status akun: <x-badge :s="$user->status_verifikasi"/></div>
  <div class="sm:col-span-2"><button class="btn btn-primary">Simpan Perubahan</button></div>
</form>
@endsection