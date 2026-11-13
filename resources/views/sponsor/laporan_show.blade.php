@extends('layouts.panel')
@section('title', 'Tinjau Laporan - ' . $event->judul)
@section('crumb', 'Laporan Program')
@section('heading', 'Peninjauan Laporan: ' . $event->judul)
@section('sub', 'Verifikasi realisasi keuangan dan capaian program dari penyelenggara kegiatan.')

@section('actions')
  <a href="{{ route('sponsor.laporan.index') }}" class="btn btn-outline btn-sm">← Kembali ke Daftar Laporan</a>
@endsection

@section('content')
<div x-data="{
  tab: 'keuangan',
  previewUrl: null,
  previewTitle: '',
  modalOpen: false,
  openPreview(url, title) {
    this.previewUrl = url;
    this.previewTitle = title;
    this.modalOpen = true;
  }
}">
  {{-- Header Ringkasan --}}
  <div class="card mb-6 grid gap-4 bg-white sm:grid-cols-2 lg:grid-cols-4">
    <div>
      <span class="text-xs text-gray-500">Penyelenggara</span>
      <p class="font-bold text-gray-800">{{ $event->organizer->nama_organisasi }}</p>
    </div>
    <div>
      <span class="text-xs text-gray-500">Kategori Event</span>
      <p class="font-bold text-brand">{{ $event->category->nama_kategori }}</p>
    </div>
    <div>
      <span class="text-xs text-gray-500">Komitmen Dana Anda</span>
      <p class="text-lg font-bold text-brand">@rp($myCommitment?->nominal ?? 0)</p>
    </div>
    <div>
      <span class="text-xs text-gray-500">Total Sponsorship Event</span>
      <p class="text-lg font-bold text-gray-800">@rp($event->dana_terkumpul)</p>
    </div>
  </div>

  {{-- Tab Navigasi Laporan --}}
  <div class="mb-6 flex gap-2 border-b border-gray-200 text-sm font-semibold">
    <button type="button" @click="tab = 'keuangan'"
            :class="tab === 'keuangan' ? 'border-brand text-brand border-b-2' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center gap-2 pb-3 px-3 transition">
      <span>1. Laporan Keuangan Transparan</span>
      @if($keuangan) <x-badge :s="$keuangan->status"/> @endif
    </button>
    <button type="button" @click="tab = 'hasil'"
            :class="tab === 'hasil' ? 'border-brand text-brand border-b-2' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center gap-2 pb-3 px-3 transition">
      <span>2. Laporan Hasil Program</span>
      @if($hasil) <x-badge :s="$hasil->status"/> @endif
    </button>
  </div>

  {{-- ==================== TAB 1: LAPORAN KEUANGAN ==================== --}}
  <div x-show="tab === 'keuangan'" class="space-y-6">
    @if(! $keuangan)
      <div class="card py-12 text-center text-gray-500">
        <p class="font-medium text-sm">Laporan keuangan belum dikirim oleh pihak Organizer.</p>
        <p class="mt-1 text-xs text-gray-400">Penyelenggara sedang menyusun rincian bukti nota pengeluaran.</p>
      </div>
    @else
      @php
        $myReviewKeuangan = $reviews->get($keuangan->id);
        $totalPengeluaran = $keuangan->totalPengeluaran();
        $sisaDana = $keuangan->sisaDana();
      @endphp

      {{-- 4 Kartu KPI Keuangan --}}
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card bg-brand-50/50 border border-brand/10">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Dana Masuk</span>
          <p class="text-2xl font-bold text-brand mt-1">@rp($event->dana_terkumpul)</p>
          <span class="text-[11px] text-gray-500 mt-1 block">Dari seluruh sponsor yang disetujui</span>
        </div>

        <div class="card bg-white border border-gray-100">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pengeluaran Panitia</span>
          <p class="text-2xl font-bold text-gray-800 mt-1">@rp($totalPengeluaran)</p>
          <span class="text-[11px] text-gray-500 mt-1 block">{{ $keuangan->items->count() }} pos rincian terlampir</span>
        </div>

        <div class="card border {{ $sisaDana >= 0 ? 'bg-emerald-50/50 border-emerald-200' : 'bg-red-50/50 border-red-200' }}">
          <span class="text-xs font-semibold uppercase tracking-wider {{ $sisaDana >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
            {{ $sisaDana >= 0 ? 'Sisa Dana (Surplus)' : 'Defisit Dana (Kurang)' }}
          </span>
          <p class="text-2xl font-bold mt-1 {{ $sisaDana >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
            {{ $sisaDana >= 0 ? '+' : '-' }}@rp(abs($sisaDana))
          </p>
          <span class="text-[11px] mt-1 block {{ $sisaDana >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
            @if($sisaDana > 0)
              Sisa dana lebih (surplus aman)
            @elseif($sisaDana < 0)
              Defisit ditutup oleh kas panitia
            @else
              Pengeluaran tepat seimbang
            @endif
          </span>
        </div>

        <div class="card bg-white border border-gray-100">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Laporan</span>
          <div class="mt-2"><x-badge :s="$keuangan->status"/></div>
          <span class="text-[11px] text-gray-500 mt-2 block">
            @if($myReviewKeuangan)
              Keputusan Anda: <b>{{ $myReviewKeuangan->status }}</b>
            @else
              Menunggu peninjauan Anda
            @endif
          </span>
        </div>
      </div>

      {{-- Form Peninjauan & Verifikasi per Item Pengeluaran --}}
      <form method="POST" action="{{ route('sponsor.laporan.tanggapi', $keuangan) }}" class="space-y-6">
        @csrf
        <div class="card overflow-hidden !p-0">
          <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3 flex items-center justify-between">
            <div>
              <h3 class="font-bold text-gray-800 text-sm">Verifikasi Rincian Pengeluaran per Item</h3>
              <p class="text-xs text-gray-500">Periksa kesesuaian pos pengeluaran dan klik "Lihat Nota" untuk memeriksa bukti transaksi asli.</p>
            </div>
            <span class="text-xs font-semibold text-brand bg-brand-50 rounded-lg px-2.5 py-1">
              {{ $keuangan->items->count() }} Pos Pengeluaran
            </span>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
              <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                <tr>
                  <th class="th w-12">No</th>
                  <th class="th">Pos / Uraian Pengeluaran</th>
                  <th class="th text-right">Nominal</th>
                  <th class="th text-center">Bukti Nota</th>
                  <th class="th text-center w-48">Verifikasi per Item</th>
                  <th class="th">Catatan Item (Opsional)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @foreach($keuangan->items as $idx => $item)
                  @php
                    $itemRev = $item->reviews->first();
                    $currentStatus = $itemRev?->status ?? 'Valid';
                  @endphp
                  <tr class="hover:bg-gray-50/50">
                    <td class="td font-medium text-gray-400">{{ $idx + 1 }}</td>
                    <td class="td font-semibold text-gray-900">
                      {{ $item->uraian }}
                    </td>
                    <td class="td text-right font-bold text-brand">
                      @rp($item->nominal)
                    </td>
                    <td class="td text-center">
                      @if($item->bukti_berkas)
                        <button type="button" @click="openPreview('{{ $item->buktiUrl() }}', 'Nota: {{ addslashes($item->uraian) }}')"
                                class="btn btn-outline btn-sm !py-1 text-xs">
                          🔎 Lihat Nota
                        </button>
                      @else
                        <span class="text-xs text-red-500 italic">Tidak ada bukti</span>
                      @endif
                    </td>
                    <td class="td text-center">
                      <select name="items_review[{{ $item->id }}][status]"
                              class="rounded-lg border border-gray-200 text-xs font-semibold px-2 py-1.5 focus:border-brand focus:ring-1 focus:ring-brand">
                        <option value="Valid" {{ $currentStatus === 'Valid' ? 'selected' : '' }}>✅ Valid</option>
                        <option value="Kurang Bukti" {{ $currentStatus === 'Kurang Bukti' ? 'selected' : '' }}>⚠️ Kurang Bukti</option>
                        <option value="Perlu Revisi" {{ $currentStatus === 'Perlu Revisi' ? 'selected' : '' }}>❌ Perlu Revisi</option>
                      </select>
                    </td>
                    <td class="td">
                      <input type="text" name="items_review[{{ $item->id }}][catatan]"
                             value="{{ $itemRev?->catatan }}"
                             placeholder="Catatan untuk item ini..."
                             class="input !py-1 !text-xs">
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        {{-- Panel Keputusan Akhir Sponsor --}}
        <div class="card bg-white border border-gray-100 space-y-4">
          <h3 class="font-bold text-gray-800 text-sm">Keputusan Keseluruhan Laporan Keuangan</h3>
          
          <div class="flex flex-wrap gap-4 text-sm font-semibold">
            <label class="flex items-center gap-2 cursor-pointer rounded-xl border border-emerald-200 bg-emerald-50/50 px-4 py-2.5 hover:bg-emerald-50">
              <input type="radio" name="status" value="Diterima"
                     {{ old('status', $myReviewKeuangan?->status) === 'Diterima' ? 'checked' : '' }}
                     class="text-brand focus:ring-brand">
              <span class="text-emerald-800">✅ Setujui &amp; Terima Laporan Keuangan</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer rounded-xl border border-orange-200 bg-orange-50/50 px-4 py-2.5 hover:bg-orange-50">
              <input type="radio" name="status" value="Perlu Revisi"
                     {{ old('status', $myReviewKeuangan?->status) === 'Perlu Revisi' ? 'checked' : '' }}
                     class="text-brand focus:ring-brand">
              <span class="text-orange-800">⚠️ Minta Revisi Laporan</span>
            </label>
          </div>

          <div>
            <label class="label">Catatan Umum / Rekomendasi (Wajib diisi jika Minta Revisi)</label>
            <textarea name="catatan" rows="3" class="input"
                      placeholder="Tuliskan catatan apresiasi atau rincian hal-hal yang perlu diperbaiki panitia...">{{ old('catatan', $myReviewKeuangan?->catatan) }}</textarea>
            @error('catatan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          <div class="flex justify-end pt-2">
            <button type="submit" class="btn btn-primary btn-sm">
              Kirim Tanggapan &amp; Verifikasi →
            </button>
          </div>
        </div>
      </form>
    @endif
  </div>

  {{-- ==================== TAB 2: LAPORAN HASIL PROGRAM ==================== --}}
  <div x-show="tab === 'hasil'" class="space-y-6">
    @if(! $hasil)
      <div class="card py-12 text-center text-gray-500">
        <p class="font-medium text-sm">Laporan hasil program belum dikirim oleh pihak Organizer.</p>
      </div>
    @else
      @php $myReviewHasil = $reviews->get($hasil->id); @endphp
      <div class="card space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="font-bold text-gray-800 text-sm">Laporan Hasil Program</h3>
          <x-badge :s="$hasil->status"/>
        </div>
        <div>
          <span class="text-xs font-semibold text-gray-500">Ringkasan Pelaksanaan:</span>
          <p class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $hasil->ringkasan }}</p>
        </div>
        <div>
          <span class="text-xs font-semibold text-gray-500">Capaian Hasil &amp; Dampak:</span>
          <p class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $hasil->capaian_hasil }}</p>
        </div>
        @if($hasil->kendala)
          <div>
            <span class="text-xs font-semibold text-gray-500">Evaluasi &amp; Kendala:</span>
            <p class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $hasil->kendala }}</p>
          </div>
        @endif
        @if($hasil->berkas_dokumentasi)
          <div class="pt-2">
            <button type="button" @click="openPreview('{{ route('files.show', ['jenis' => 'dokumentasi', 'id' => $hasil->id]) }}', 'Dokumentasi Program')"
                    class="btn btn-outline btn-sm">
              📄 Lihat Berkas Dokumentasi Program
            </button>
          </div>
        @endif
      </div>

      {{-- Form Tanggapan Hasil Program --}}
      <form method="POST" action="{{ route('sponsor.laporan.tanggapi', $hasil) }}" class="card space-y-4">
        @csrf
        <h3 class="font-bold text-gray-800 text-sm">Tanggapan untuk Laporan Hasil Program</h3>
        <div class="flex flex-wrap gap-4 text-sm font-semibold">
          <label class="flex items-center gap-2 cursor-pointer rounded-xl border border-emerald-200 bg-emerald-50/50 px-4 py-2 hover:bg-emerald-50">
            <input type="radio" name="status" value="Diterima"
                   {{ old('status', $myReviewHasil?->status) === 'Diterima' ? 'checked' : '' }} class="text-brand">
            <span class="text-emerald-800">✅ Terima Laporan</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer rounded-xl border border-orange-200 bg-orange-50/50 px-4 py-2 hover:bg-orange-50">
            <input type="radio" name="status" value="Perlu Revisi"
                   {{ old('status', $myReviewHasil?->status) === 'Perlu Revisi' ? 'checked' : '' }} class="text-brand">
            <span class="text-orange-800">⚠️ Minta Revisi</span>
          </label>
        </div>
        <div>
          <label class="label">Catatan Evaluasi</label>
          <textarea name="catatan" rows="3" class="input" placeholder="Tuliskan catatan evaluasi...">{{ old('catatan', $myReviewHasil?->catatan) }}</textarea>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="btn btn-primary btn-sm">Kirim Tanggapan Hasil Program</button>
        </div>
      </form>
    @endif
  </div>

  {{-- Modal Pratinjau Bukti Foto / Nota --}}
  <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
    <div @click.outside="modalOpen = false" class="relative max-h-[90vh] max-w-3xl w-full rounded-2xl bg-white p-4 shadow-2xl overflow-hidden flex flex-col">
      <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
        <h4 class="font-bold text-sm text-gray-800" x-text="previewTitle"></h4>
        <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
      </div>
      <div class="flex-1 overflow-auto flex items-center justify-center bg-gray-50 rounded-xl p-2 min-h-[300px]">
        <template x-if="previewUrl">
          <iframe :src="previewUrl" class="w-full h-[65vh] rounded-lg border-0"></iframe>
        </template>
      </div>
      <div class="mt-3 flex justify-end">
        <a :href="previewUrl" target="_blank" class="btn btn-outline btn-sm">Buka di Tab Baru ↗</a>
      </div>
    </div>
  </div>
</div>
@endsection
