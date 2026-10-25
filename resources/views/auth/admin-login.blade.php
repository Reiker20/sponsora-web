@extends('layouts.auth')
@section('title', 'Login Admin')
@section('content')
<h1 class="text-3xl font-bold">Login Admin</h1>
<p class="mt-1 text-xs text-gray-500">Akun khusus pengelola platform Sponsora.</p>
<form method="POST" action="{{ route('admin.login') }}" class="mt-6 space-y-4">@csrf
  <div><label class="label">E-Mail Admin</label><input type="email" name="email" value="{{ old('email') }}" required class="input"></div>
  <div><label class="label">Kata Sandi</label><input type="password" name="password" required class="input"></div>
  <button class="btn btn-primary w-full !py-3">Masuk sebagai Admin</button>
</form>
@endsection
