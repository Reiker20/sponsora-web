@extends('layouts.panel')
@section('title', 'Kelola Laporan - ' . $event->judul)
@section('crumb', 'Laporan Pertanggungjawaban')
@section('heading', $event->judul)
@section('sub', 'Laporan Pertanggungjawaban Pasca-Kegiatan untuk Sponsor')

@section('actions')
  <a href="{{ route('organizer.laporan.index') }}" class="btn btn-outline btn-sm">← Kembali ke Daftar Laporan</a>
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
  {{-- Header Info Event --}}
  <div class="card mb-6 grid gap-4 bg-white sm:grid-cols-2 lg:grid-cols-4">
    <div>
      <span class="text-xs text-gray-500">Kategori Event</span>
      <p class="font-bold text-brand">{{ $event->category->nama_kategori }}</p>
    </div>
    <div>
      <span class="text-xs text-gray-500">Tanggal Selesai</span>
      <p class="font-bold text-gray-800">{{ $event->tanggal_pelaksanaan->format('d M Y') }}</p>
    </div>
    <div>
      <span class="text-xs text-gray-500">Batas Waktu Pelaporan</span>
      <p class="font-bold text-amber-700">{{ $event->batas_laporan ? $event->batas_laporan->format('d M Y') : '14 Hari Pasca-Event' }}</p>
    </div>
    <div>
      <span class="text-xs text-gray-500">Total Dana Sponsorship</span>
      <p class="text-lg font-bold text-brand">@rp($event->dana_terkumpul)</p>
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
    {{-- Catatan Revisi dari Sponsor (Jika Ada) --}}
    @if($keuangan && $keuangan->status === 'Perlu Revisi')
      <div class="rounded-2xl border border-orange-200 bg-orange-50 p-4 text-xs text-orange-800 space-y-2">
        <p class="font-bold text-sm">Catatan Perbaikan dari Sponsor:</p>
        @foreach($keuangan->reviews->where('status', 'Perlu Revisi') as $rev)
          <div class="rounded-xl border border-orange-200 bg-white p-3">
            <span class="font-bold text-gray-800">{{ $rev->sponsor->nama_perusahaan }}:</span>
            <p class="mt-1 text-gray-700">{{ $rev->catatan }}</p>
          </div>
        @endforeach
      </div>
    @endif

    @php
      $isKeuanganEditable = ! $keuangan || $keuangan->isEditable();
      $danaTerkumpul = (int) $event->dana_terkumpul;

      $oldItems = old('items');
      if (is_array($oldItems) && count($oldItems) > 0) {
          $initialRows = array_map(function ($item) {
              return [
                  'uraian' => $item['uraian'] ?? '',
                  'nominal' => $item['nominal'] ?? '',
                  'bukti_lama' => $item['bukti_lama'] ?? null,
                  'id' => $item['id'] ?? null,
              ];
          }, array_values($oldItems));
      } elseif ($rows->isNotEmpty()) {
          $initialRows = $rows->toArray();
      } else {
          $initialRows = [['uraian' => '', 'nominal' => '', 'bukti_lama' => null, 'id' => null]];
      }
    @endphp

    {{-- Form Laporan Keuangan dengan Alpine.js untuk Kalkulasi Otomatis --}}
    <div x-data="{
      danaMasuk: {{ $danaTerkumpul }},
      rows: {{ json_encode($initialRows) }},
      tambahBaris() {
        this.rows.push({ uraian: '', nominal: '', bukti_lama: null, id: null });
      },
      hapusBaris(idx) {
        if (this.rows.length > 1) {
          this.rows.splice(idx, 1);
        }
      },
      totalPengeluaran() {
        return this.rows.reduce((acc, row) => acc + (parseInt(row.nominal) || 0), 0);
      },
      sisaDana() {
        return this.danaMasuk - this.totalPengeluaran();
      },
      formatRupiah(num) {
        return 'Rp ' + (num || 0).toLocaleString('id-ID');
      }
    }">
      {{-- 3 Kartu Ringkasan Realokasi Dana --}}
      <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <div class="card bg-brand-50/50 border border-brand/10">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Dana Masuk</span>
          <p class="text-2xl font-bold text-brand mt-1" x-text="formatRupiah(danaMasuk)"></p>
          <span class="text-[11px] text-gray-500 mt-1 block">Akumulasi komitmen sponsor diterima</span>
        </div>

        <div class="card bg-white border border-gray-100">
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pengeluaran Aktual</span>
          <p class="text-2xl font-bold text-gray-800 mt-1" x-text="formatRupiah(totalPengeluaran())"></p>
          <span class="text-[11px] text-gray-500 mt-1 block">Penjumlahan seluruh pos nota di bawah</span>
        </div>

        <div class="card border" :class="sisaDana() >= 0 ? 'bg-emerald-50/50 border-emerald-200' : 'bg-red-50/50 border-red-200'">
          <span class="text-xs font-semibold uppercase tracking-wider" :class="sisaDana() >= 0 ? 'text-emerald-700' : 'text-red-700'">
            <span x-text="sisaDana() >= 0 ? 'Sisa Dana (Surplus)' : 'Defisit Dana (Kurang)'"></span>
          </span>
          <p class="text-2xl font-bold mt-1" :class="sisaDana() >= 0 ? 'text-emerald-700' : 'text-red-700'"
             x-text="formatRupiah(Math.abs(sisaDana()))"></p>
          <span class="text-[11px] mt-1 block" :class="sisaDana() >= 0 ? 'text-emerald-600' : 'text-red-600'">
            <template x-if="sisaDana() > 0"><span>Sisa dana aman / surplus</span></template>
            <template x-if="sisaDana() < 0"><span>Defisit ditanggung kas panitia</span></template>
            <template x-if="sisaDana() === 0"><span>Pengeluaran tepat seimbang</span></template>
          </span>
        </div>
      </div>

      {{-- Formulir / Tabel Baris Pengeluaran --}}
      @if($isKeuanganEditable)
        <form method="POST" action="{{ route('organizer.laporan.keuangan', $event) }}" enctype="multipart/form-data" class="space-y-6">
          @csrf
          <div class="card overflow-hidden !p-0">
            <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3 flex items-center justify-between">
              <div>
                <h3 class="font-bold text-gray-800 text-sm">Rincian Pos Pengeluaran &amp; Bukti Nota</h3>
                <p class="text-xs text-gray-500">Wajib sertakan uraian dana keluar, nominal, dan foto bukti nota/kwitansi (JPG/PNG/PDF maks 5 MB).</p>
              </div>
              <button type="button" @click="tambahBaris()" class="btn btn-outline btn-sm">
                + Tambah Pos Pengeluaran
              </button>
            </div>

            <div class="p-4 space-y-3">
              <template x-for="(row, idx) in rows" :key="idx">
                <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm space-y-3">
                  <div class="grid gap-3 sm:grid-cols-12 items-start">
                    {{-- Pos Pengeluaran --}}
                    <div class="sm:col-span-5">
                      <label class="label">Pos / Uraian Pengeluaran *</label>
                      <input type="text" :name="'items[' + idx + '][uraian]'" x-model="row.uraian" required
                             placeholder="Contoh: Konsumsi 50 Relawan & Panitia" class="input">
                      <input type="hidden" :name="'items[' + idx + '][bukti_lama]'" :value="row.bukti_lama">
                      <input type="hidden" :name="'items[' + idx + '][id]'" :value="row.id">
                    </div>

                    {{-- Nominal --}}
                    <div class="sm:col-span-3">
                      <label class="label">Nominal Aktual (Rp) *</label>
                      <input type="number" :name="'items[' + idx + '][nominal]'" x-model="row.nominal" required min="0"
                             placeholder="Contoh: 1500000" class="input">
                    </div>

                    {{-- Unggah Bukti Nota / Foto --}}
                    <div class="sm:col-span-3">
                      <label class="label">Bukti Foto / Nota *</label>
                      <input type="file" :name="'items[' + idx + '][bukti]'" accept="image/jpeg,image/png,application/pdf"
                             class="input !p-1.5 text-xs">
                      <template x-if="row.bukti_lama && row.id">
                        <div class="mt-1 flex items-center gap-1.5">
                          <button type="button" @click="openPreview('/berkas/bukti/' + row.id, row.uraian)"
                                  class="text-[11px] font-semibold text-brand underline">
                            📄 Lihat Bukti Tersimpan
                          </button>
                        </div>
                      </template>
                    </div>

                    {{-- Tombol Hapus Baris --}}
                    <div class="sm:col-span-1 pt-6 text-right">
                      <button type="button" @click="hapusBaris(idx)" :disabled="rows.length <= 1"
                              class="text-red-500 hover:text-red-700 disabled:opacity-30 p-2 text-sm" title="Hapus Pos">
                        ✕
                      </button>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            {{-- Footer Tabel --}}
            <div class="border-t border-gray-100 bg-gray-50/50 p-4 flex flex-wrap items-center justify-between gap-3">
              <button type="button" @click="tambahBaris()" class="btn btn-outline btn-sm">
                + Tambah Pos Pengeluaran
              </button>

              <div class="flex items-center gap-2">
                <button type="submit" name="aksi" value="draft" class="btn btn-outline btn-sm">
                  Simpan Draft
                </button>
                <button type="submit" name="aksi" value="kirim" class="btn btn-primary btn-sm"
                        onclick="return confirm('Kirim laporan keuangan ke seluruh Sponsor pendana? Data akan dikunci untuk peninjauan.')">
                  Kirim ke Sponsor →
                </button>
              </div>
            </div>
          </div>
        </form>
      @else
        {{-- Tampilan Read-Only jika Laporan Sudah Dikirim / Diterima --}}
        <div class="card overflow-hidden !p-0">
          <div class="border-b border-gray-100 bg-gray-50 px-4 py-3 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm">Daftar Pengeluaran Terkirim</h3>
            <x-badge :s="$keuangan->status"/>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
              <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
                <tr>
                  <th class="th">No</th>
                  <th class="th">Pos / Uraian Pengeluaran</th>
                  <th class="th text-right">Nominal</th>
                  <th class="th text-center">Bukti Nota / Kwitansi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @foreach($keuangan->items as $idx => $item)
                  <tr>
                    <td class="td font-medium text-gray-400">{{ $idx + 1 }}</td>
                    <td class="td font-semibold text-gray-900">{{ $item->uraian }}</td>
                    <td class="td text-right font-bold text-brand">@rp($item->nominal)</td>
                    <td class="td text-center">
                      @if($item->bukti_berkas)
                        <button type="button" @click="openPreview('{{ $item->buktiUrl() }}', '{{ addslashes($item->uraian) }}')"
                                class="btn btn-outline btn-sm !py-1 text-xs">
                          🔎 Lihat Nota
                        </button>
                      @else
                        <span class="text-xs text-gray-400">Tidak ada berkas</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
    </div>
  </div>

  {{-- ==================== TAB 2: LAPORAN HASIL PROGRAM ==================== --}}
  <div x-show="tab === 'hasil'" class="space-y-6">
    @php
      $isHasilEditable = ! $hasil || $hasil->isEditable();
    @endphp

    @if($isHasilEditable)
      <form method="POST" action="{{ route('organizer.laporan.hasil', $event) }}" enctype="multipart/form-data" class="card space-y-4">
        @csrf
        <div>
          <label class="label">Ringkasan Pelaksanaan Program *</label>
          <textarea name="ringkasan" rows="4" class="input" placeholder="Jelaskan secara garis besar bagaimana kegiatan berlangsung...">{{ old('ringkasan', $hasil?->ringkasan) }}</textarea>
        </div>

        <div>
          <label class="label">Capaian Hasil &amp; Dampak Positif *</label>
          <textarea name="capaian_hasil" rows="4" class="input" placeholder="Uraikan jumlah penerima manfaat, target output yang tercapai...">{{ old('capaian_hasil', $hasil?->capaian_hasil) }}</textarea>
        </div>

        <div>
          <label class="label">Evaluasi &amp; Kendala Lapangan (Opsional)</label>
          <textarea name="kendala" rows="3" class="input" placeholder="Catatan kendala yang dialami serta solusinya...">{{ old('kendala', $hasil?->kendala) }}</textarea>
        </div>

        <div>
          <label class="label">Unggah Berkas Dokumentasi Program (PDF/JPG/PNG maks 5 MB)</label>
          @if($hasil?->berkas_dokumentasi)
            <div class="mb-2">
              <button type="button" @click="openPreview('{{ route('files.show', ['jenis' => 'dokumentasi', 'id' => $hasil->id]) }}', 'Dokumentasi Program')"
                      class="text-xs font-semibold text-brand underline">
                📄 Lihat Berkas Dokumentasi Tersimpan
              </button>
            </div>
          @endif
          <input type="file" name="dokumentasi" accept="image/jpeg,image/png,application/pdf" class="input !p-2 text-xs">
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
          <button type="submit" name="aksi" value="draft" class="btn btn-outline btn-sm">Simpan Draft</button>
          <button type="submit" name="aksi" value="kirim" class="btn btn-primary btn-sm"
                  onclick="return confirm('Kirim laporan hasil program ke Sponsor?')">
            Kirim ke Sponsor →
          </button>
        </div>
      </form>
    @else
      <div class="card space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="font-bold text-gray-800">Laporan Hasil Program</h3>
          <x-badge :s="$hasil->status"/>
        </div>
        <div>
          <span class="text-xs font-semibold text-gray-500">Ringkasan Pelaksanaan:</span>
          <p class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $hasil->ringkasan }}</p>
        </div>
        <div>
          <span class="text-xs font-semibold text-gray-500">Capaian Hasil:</span>
          <p class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $hasil->capaian_hasil }}</p>
        </div>
        @if($hasil->kendala)
          <div>
            <span class="text-xs font-semibold text-gray-500">Kendala Lapangan:</span>
            <p class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $hasil->kendala }}</p>
          </div>
        @endif
        @if($hasil->berkas_dokumentasi)
          <div class="pt-2">
            <button type="button" @click="openPreview('{{ route('files.show', ['jenis' => 'dokumentasi', 'id' => $hasil->id]) }}', 'Dokumentasi Program')"
                    class="btn btn-outline btn-sm">
              📄 Buka Berkas Dokumentasi
            </button>
          </div>
        @endif
      </div>
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
