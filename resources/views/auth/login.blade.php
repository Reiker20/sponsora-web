@extends('layouts.auth')
@section('title', 'Masuk')
@section('content')
<div x-data="{role: '{{ old('role', request('role') === 'organizer' ? 'organizer' : 'sponsor') }}'}">
  <h1 class="text-3xl font-bold">Selamat Datang Kembali</h1>
  <p class="mt-1 text-xs text-gray-500">Masuk untuk melanjutkan aktivitas dan kemitraanmu.</p>
  <div class="mt-6 grid grid-cols-2 gap-1 rounded-2xl bg-brand-50 p-1.5 text-sm font-semibold">
    <button type="button" @click="role='sponsor'" :class="role==='sponsor' ? 'bg-white shadow text-brand' : 'text-brand/70'" class="rounded-xl py-3">Sponsor</button>
    <button type="button" @click="role='organizer'" :class="role==='organizer' ? 'bg-white shadow text-brand' : 'text-brand/70'" class="rounded-xl py-3">Organizer</button></div>
  <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">@csrf
    <input type="hidden" name="role" :value="role">
    <div><label class="label">E-Mail</label><input type="email" name="email" value="{{ old('email') }}" required class="input" placeholder="nama@organisasi.com"></div>
    <div><label class="label">Kata Sandi</label><input type="password" name="password" required class="input" placeholder="••••••••"></div>
    <label class="flex items-center gap-2 text-xs text-gray-600"><input type="checkbox" name="remember" class="rounded"> Ingat saya</label>
    <button class="btn btn-primary w-full !py-3" x-text="'Masuk Sebagai ' + (role==='sponsor' ? 'Sponsor' : 'Organizer')"></button>
  </form>
  <p class="mt-6 text-center text-sm text-gray-500">Belum Punya Akun? <a :href="'{{ route('register') }}?role=' + role" class="font-bold text-brand">Daftar Sekarang</a></p>
  <p class="mt-2 text-center text-xs text-gray-400"><a href="{{ route('admin.login') }}">Login Admin</a></p>
</div>
@endsection
