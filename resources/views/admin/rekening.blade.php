@extends('layouts.panel')
@section('title', 'Rekening Tujuan Transfer')
@section('crumb', 'Rekening Tujuan Transfer')
@section('heading', 'Rekening Tujuan Transfer')
@section('sub', 'Rekening platform yang ditampilkan kepada Sponsor. Dana ditransfer ke sini, bukan langsung ke Organizer.')
@section('content')
<div class="grid gap-6 lg:grid-cols-3">
  <div class="card lg:col-span-1 h-fit">
    <h2 class="font-semibold text-brand">Tambah Rekening</h2>
    <form method="POST" action="{{ route('admin.rekening.store') }}" class="mt-4 space-y-3">@csrf
      <div><label class="label">Nama bank</label><input name="nama_bank" value="{{ old('nama_bank') }}" required maxlength="50" class="input" placeholder="mis. BCA"></div>
      <div><label class="label">Nomor rekening</label><input name="nomor_rekening" value="{{ old('nomor_rekening') }}" required maxlength="30" class="input" placeholder="mis. 1234567890"></div>
      <div><label class="label">Atas nama</label><input name="atas_nama" value="{{ old('atas_nama') }}" required maxlength="100" class="input"></div>
      <div><label class="label">Catatan (opsional)</label><input name="catatan" value="{{ old('catatan') }}" maxlength="255" class="input"></div>
      <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked class="rounded"> Tampilkan ke Sponsor</label>
      <button class="btn btn-primary w-full">Simpan Rekening</button>
    </form>
  </div>

  <div class="space-y-3 lg:col-span-2">
    @forelse($accounts as $a)
      <div class="card" x-data="{edit:false}">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
          <div>
            <p class="font-semibold">{{ $a->nama_bank }} — {{ $a->nomor_rekening }}
              <span class="ml-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $a->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $a->is_active ? 'Aktif' : 'Nonaktif' }}</span></p>
            <p class="text-sm text-gray-600">a.n. {{ $a->atas_nama }}</p>
            @if($a->catatan)<p class="mt-1 text-xs text-gray-500">{{ $a->catatan }}</p>@endif
          </div>
          <div class="flex flex-wrap gap-2">
            <button type="button" @click="edit=!edit" class="btn btn-outline btn-sm">Ubah</button>
            <form method="POST" action="{{ route('admin.rekening.toggle', $a) }}">@csrf<button class="btn btn-outline btn-sm">{{ $a->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
            <form method="POST" action="{{ route('admin.rekening.destroy', $a) }}" onsubmit="return confirm('Hapus rekening ini?')">@csrf @method('DELETE')<button class="btn btn-outline btn-sm !text-red-600">Hapus</button></form>
          </div>
        </div>

        <form x-show="edit" x-cloak method="POST" action="{{ route('admin.rekening.update', $a) }}" class="mt-4 grid gap-3 border-t border-gray-100 pt-4 sm:grid-cols-2">@csrf @method('PUT')
          <div><label class="label">Nama bank</label><input name="nama_bank" value="{{ $a->nama_bank }}" required maxlength="50" class="input"></div>
          <div><label class="label">Nomor rekening</label><input name="nomor_rekening" value="{{ $a->nomor_rekening }}" required maxlength="30" class="input"></div>
          <div><label class="label">Atas nama</label><input name="atas_nama" value="{{ $a->atas_nama }}" required maxlength="100" class="input"></div>
          <div><label class="label">Catatan</label><input name="catatan" value="{{ $a->catatan }}" maxlength="255" class="input"></div>
          <div class="sm:col-span-2 flex justify-end"><button class="btn btn-primary btn-sm">Simpan Perubahan</button></div>
        </form>
      </div>
    @empty
      <p class="card text-center text-sm text-gray-500">Belum ada rekening tujuan. Sponsor belum bisa melihat instruksi transfer.</p>
    @endforelse
  </div>
</div>
@endsection