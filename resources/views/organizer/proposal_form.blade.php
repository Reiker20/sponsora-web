@extends('layouts.panel')
@section('title', $event->exists ? 'Ubah Proposal' : 'Buat Proposal')
@section('heading', $event->exists ? 'Ubah Proposal' : 'Buat Proposal')
@section('sub', 'Lengkapi data kegiatan. Proposal disimpan sebagai Draft dan dapat diajukan untuk verifikasi.')
@section('content')
<form method="POST" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3"
      action="{{ $event->exists ? route('organizer.proposals.update', $event) : route('organizer.proposals.store') }}">
  @csrf @if($event->exists) @method('PUT') @endif
  <div class="card space-y-4 lg:col-span-2">
    <div><label class="label">Nama kegiatan *</label><input name="judul" value="{{ old('judul', $event->judul) }}" required class="input"></div>
    <div class="grid gap-4 sm:grid-cols-2">
      <div><label class="label">Kategori *</label><select name="category_id" required class="input"><option value="">Pilih kategori</option>
        @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $event->category_id) == $c->id)>{{ $c->nama_kategori }}</option>@endforeach</select></div>
      <div><label class="label">Tanggal pelaksanaan *</label><input type="date" name="tanggal_pelaksanaan" value="{{ old('tanggal_pelaksanaan', $event->tanggal_pelaksanaan?->format('Y-m-d')) }}" required class="input"></div>
      <div><label class="label">Lokasi *</label><input name="lokasi" value="{{ old('lokasi', $event->lokasi) }}" required class="input"></div>
      <div><label class="label">Penerima manfaat</label><input name="penerima_manfaat" value="{{ old('penerima_manfaat', $event->penerima_manfaat) }}" class="input" placeholder="mis. 1.200+ warga"></div>
    </div>
    <div><label class="label">Deskripsi kegiatan *</label><textarea name="deskripsi" rows="6" required class="input">{{ old('deskripsi', $event->deskripsi) }}</textarea></div>
    <div><label class="label">Benefit untuk sponsor *</label><textarea name="benefit_sponsor" rows="4" required class="input" placeholder="Satu benefit per baris">{{ old('benefit_sponsor', $event->benefit_sponsor) }}</textarea></div>
  </div>
  <div class="space-y-6">
    <div class="card space-y-4"><div><label class="label">Kebutuhan dana (Rp) *</label><input type="number" min="100000" step="1000" name="target_dana" value="{{ old('target_dana', $event->target_dana) }}" required class="input"></div>
      <div><label class="label">Gambar sampul (JPG/PNG, maks 2 MB)</label>
        @if($event->cover_url)<img src="{{ $event->cover_url }}" class="mb-2 h-28 w-full rounded-xl object-cover">@endif
        <input type="file" name="cover" accept="image/*" class="input !p-2 text-xs"></div></div>
    <div class="card bg-brand-50 text-xs text-brand">Proposal hanya dapat diubah atau dihapus selama belum diverifikasi Admin.</div>
    <div class="flex gap-2"><button class="btn btn-primary flex-1">Simpan Draft</button><a href="{{ route('organizer.proposals') }}" class="btn btn-outline">Batal</a></div>
  </div>
</form>
@endsection