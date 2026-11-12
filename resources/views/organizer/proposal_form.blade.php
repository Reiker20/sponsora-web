@extends('layouts.panel')
@section('title', $event->exists ? 'Ubah Proposal' : 'Buat Proposal')
@section('heading', $event->exists ? 'Ubah Proposal' : 'Buat Proposal')
@section('sub', 'Lengkapi data kegiatan. Proposal dapat disimpan sebagai Draft atau langsung diajukan untuk verifikasi Admin.')

@section('content')
@if($event->status === 'Perlu Revisi' && $event->verifications->first())
  <div class="mb-6 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-xs text-orange-800">
    <p class="font-bold text-sm">Catatan Revisi dari Admin:</p>
    <p class="mt-1 text-sm bg-white/80 p-3 rounded-xl border border-orange-200">{{ $event->verifications->first()->catatan }}</p>
    <p class="mt-2 text-xs text-orange-700">Silakan sesuaikan data proposal Anda di bawah ini, lalu klik <b>Simpan &amp; Ajukan Verifikasi</b>.</p>
  </div>
@endif

<form method="POST" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3"
      action="{{ $event->exists ? route('organizer.proposals.update', $event) : route('organizer.proposals.store') }}">
  @csrf
  @if($event->exists) @method('PUT') @endif

  <div class="card space-y-4 lg:col-span-2">
    <div>
      <label class="label">Nama kegiatan *</label>
      <input name="judul" value="{{ old('judul', $event->judul) }}" required class="input" placeholder="mis. Gerakan Pesisir Bersih 2026">
      @error('judul') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="label">Kategori *</label>
        <select name="category_id" required class="input">
          <option value="">Pilih kategori</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id', $event->category_id) == $c->id)>{{ $c->nama_kategori }}</option>
          @endforeach
        </select>
        @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="label">Tanggal pelaksanaan *</label>
        <input type="date" name="tanggal_pelaksanaan" value="{{ old('tanggal_pelaksanaan', $event->tanggal_pelaksanaan?->format('Y-m-d')) }}" required class="input">
        @error('tanggal_pelaksanaan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="label">Lokasi *</label>
        <input name="lokasi" value="{{ old('lokasi', $event->lokasi) }}" required class="input" placeholder="mis. Pantai Kenjeran, Surabaya">
        @error('lokasi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="label">Penerima manfaat</label>
        <input name="penerima_manfaat" value="{{ old('penerima_manfaat', $event->penerima_manfaat) }}" class="input" placeholder="mis. 1.200+ warga pesisir">
        @error('penerima_manfaat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
      </div>
    </div>

    <div>
      <label class="label">Deskripsi kegiatan *</label>
      <textarea name="deskripsi" rows="6" required class="input" placeholder="Jelaskan latar belakang, tujuan, dan alur kegiatan secara lengkap...">{{ old('deskripsi', $event->deskripsi) }}</textarea>
      @error('deskripsi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="label">Benefit untuk sponsor *</label>
      <textarea name="benefit_sponsor" rows="4" required class="input" placeholder="Satu benefit per baris (mis. Logo di banner, publikasi media, booth aktivasi...)">{{ old('benefit_sponsor', $event->benefit_sponsor) }}</textarea>
      @error('benefit_sponsor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
  </div>

  <div class="space-y-6">
    <div class="card space-y-4">
      <div>
        <label class="label">Kebutuhan dana (Rp) *</label>
        <input type="number" min="100000" step="1000" name="target_dana" value="{{ old('target_dana', $event->target_dana) }}" required class="input" placeholder="mis. 50000000">
        @error('target_dana') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="label">Gambar sampul (JPG/PNG, maks 2 MB)</label>
        @if($event->cover_url)
          <img src="{{ $event->cover_url }}" class="mb-2 h-28 w-full rounded-xl object-cover">
        @endif
        <input type="file" name="cover" accept="image/*" class="input !p-2 text-xs">
        @error('cover') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="card bg-brand-50 text-xs text-brand space-y-1">
      <p class="font-bold">Ketentuan Pengajuan:</p>
      <p>Proposal berstatus Draft atau Perlu Revisi dapat diubah kapan saja. Setelah diajukan, proposal akan ditinjau oleh Admin sebelum tampil di katalog publik.</p>
    </div>

    <div class="card space-y-3">
      <button type="submit" name="action" value="draft" class="btn btn-outline w-full">
        Simpan sebagai Draft
      </button>

      <button type="submit" name="action" value="ajukan" class="btn btn-primary w-full"
              @disabled(! auth()->user()->isVerified())
              onclick="return confirm('Simpan dan ajukan proposal ini untuk verifikasi Admin?')">
        Simpan &amp; Ajukan Verifikasi
      </button>

      @unless(auth()->user()->isVerified())
        <p class="text-[11px] text-amber-700 text-center">
          Akun Anda masih berstatus <b>{{ auth()->user()->status_verifikasi }}</b>. Anda dapat menyimpan sebagai Draft, namun tombol ajukan verifikasi aktif setelah akun disetujui Admin.
        </p>
      @endunless

      <a href="{{ route('organizer.proposals') }}" class="btn btn-outline w-full text-center !border-gray-200 !text-gray-600 hover:!bg-gray-50">
        Batal
      </a>
    </div>
  </div>
</form>
@endsection
