@extends('layouts.panel')
@section('title', 'Profil Organizer')
@section('crumb', 'Profil Organizer')
@section('heading', 'Profil Penyelenggara')
@section('sub', 'Kelola informasi profil organisasi Anda agar data yang ditampilkan kepada calon sponsor selalu akurat dan kredibel.')

@section('content')
<div class="space-y-6">
  {{-- Card Status Verifikasi Akun --}}
  <div class="card flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center gap-3">
      <div class="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-xl font-bold text-brand">
        {{ strtoupper(substr($p->nama_organisasi ?? $user->name, 0, 1)) }}
      </div>
      <div>
        <h2 class="text-base font-bold text-brand">{{ $p->nama_organisasi ?? $user->name }}</h2>
        <p class="text-xs text-gray-500">{{ $user->email }} · Terdaftar sejak {{ $user->created_at->format('d M Y') }}</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs text-gray-500">Status Akun:</span>
      <x-badge :s="$user->status_verifikasi"/>
    </div>
  </div>

  @if($user->status_verifikasi === 'Menunggu')
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
      <p class="font-semibold">Akun sedang dalam antrean verifikasi Admin.</p>
      <p class="mt-0.5">Anda tetap dapat menyusun draft proposal kegiatan. Pengajuan verifikasi proposal ke katalog sponsor dapat dilakukan setelah akun disetujui.</p>
    </div>
  @elseif($user->status_verifikasi === 'Ditolak')
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs text-red-700">
      <p class="font-semibold">Pendaftaran akun belum disetujui oleh Admin.</p>
      @if($user->catatan_verifikasi)
        <p class="mt-1 bg-white/70 p-2 rounded-lg border border-red-200 font-mono text-xs">Catatan Admin: {{ $user->catatan_verifikasi }}</p>
      @endif
      <p class="mt-1">Silakan perbarui data atau dokumen legalitas Anda di bawah ini dan simpan perubahan.</p>
    </div>
  @elseif($user->status_verifikasi === 'Disetujui' || $user->status_verifikasi === 'Terverifikasi')
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs text-emerald-800 flex items-center justify-between">
      <div>
        <p class="font-semibold">Akun Terverifikasi</p>
        <p class="mt-0.5">Identitas organisasi Anda telah diverifikasi oleh Admin. Anda dapat mempublikasikan proposal dan menerima komitmen dari Sponsor.</p>
      </div>
      <svg class="h-6 w-6 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
  @endif

  {{-- Formulir Edit Profil --}}
  <form method="POST" action="{{ route('organizer.profil.update') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
    @csrf
    @method('PUT')

    {{-- Kolom Kiri: Data Utama Organisasi --}}
    <div class="card space-y-5 lg:col-span-2">
      <h3 class="text-sm font-bold uppercase tracking-wider text-brand">Informasi Organisasi & Narahubung</h3>

      <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="label">Nama Organisasi / Komunitas *</label>
          <input type="text" name="nama_organisasi" value="{{ old('nama_organisasi', $p->nama_organisasi) }}" required class="input" placeholder="mis. HIMA Teknik Informatika, BEM Universitas, Yayasan Aksi Sosial">
          @error('nama_organisasi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="label">Jenis Organisasi *</label>
          <select name="jenis" required class="input">
            @foreach(['HIMA/UKM', 'NGO', 'NPO', 'Komunitas'] as $j)
              <option value="{{ $j }}" @selected(old('jenis', $p->jenis) === $j)>{{ $j }}</option>
            @endforeach
          </select>
          @error('jenis') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="label">Nomor Kontak / WhatsApp *</label>
          <input type="text" name="kontak" value="{{ old('kontak', $p->kontak) }}" required class="input" placeholder="mis. 081234567890">
          @error('kontak') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
          <label class="label">Nama Narahubung / Penanggung Jawab (PIC) *</label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="input" placeholder="Nama lengkap ketua atau penanggung jawab">
          @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
          <label class="label">Alamat Kantor / Sekretariat</label>
          <input type="text" name="alamat" value="{{ old('alamat', $p->alamat) }}" class="input" placeholder="Jl. Kampus No. 1, Kota...">
          @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div>
        <label class="label">Deskripsi Organisasi</label>
        <textarea name="deskripsi" rows="5" class="input" placeholder="Ceritakan latar belakang, fokus bidang sosial, visi, dan rekam jejak kegiatan yang pernah diselenggarakan organisasi Anda. Informasi ini akan dilihat oleh calon Sponsor saat meninjau proposal.">{{ old('deskripsi', $p->deskripsi) }}</textarea>
        @error('deskripsi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-gray-500">Maksimal 1.000 karakter.</p>
      </div>
    </div>

    {{-- Kolom Kanan: Akun, Legalitas & Aksi Simpan --}}
    <div class="space-y-6">
      {{-- Card Akun --}}
      <div class="card space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-brand">Kredensial Akun</h3>
        <div>
          <label class="label">Email Terdaftar</label>
          <input type="email" value="{{ $user->email }}" disabled class="input bg-gray-50 text-gray-500 cursor-not-allowed">
          <p class="mt-1 text-[11px] text-gray-400">Email akun digunakan untuk login dan menerima notifikasi.</p>
        </div>
      </div>

      {{-- Card Berkas Legalitas --}}
      <div class="card space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-brand">Dokumen Legalitas / SK</h3>
        <p class="text-xs text-gray-500">Dokumen legalitas berupa SK Kepengurusan, Akta, atau Surat Keterangan Organisasi.</p>

        @if($p->dokumen_legalitas)
          <div class="rounded-xl border border-gray-200 bg-brand-50/50 p-3 flex items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="truncate text-xs font-semibold text-brand">Berkas Terunggah</p>
              <p class="text-[11px] text-gray-500">Tersimpan di server (privat)</p>
            </div>
            <a href="{{ route('files.show', ['jenis' => 'legalitas', 'id' => $user->id]) }}" target="_blank" class="btn btn-outline btn-sm !py-1 text-xs">
              Lihat Berkas
            </a>
          </div>
        @else
          <div class="rounded-xl border border-dashed border-gray-300 p-3 text-center text-xs text-gray-400">
            Belum ada berkas legalitas yang diunggah.
          </div>
        @endif

        <div>
          <label class="label">Perbarui Berkas Legalitas (Opsional)</label>
          <input type="file" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" class="input !p-2 text-xs">
          <p class="mt-1 text-[11px] text-gray-500">Format: PDF, JPG, PNG (maksimal 5 MB per NFR-ORG-04).</p>
          @error('dokumen') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Card Tindakan --}}
      <div class="card space-y-3">
        <button type="submit" class="btn btn-primary w-full">
          Simpan Perubahan
        </button>
        <a href="{{ route('organizer.dashboard') }}" class="btn btn-outline w-full text-center">
          Batal
        </a>
      </div>
    </div>
  </form>
</div>
@endsection
