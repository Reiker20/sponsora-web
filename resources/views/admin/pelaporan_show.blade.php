@extends('layouts.panel')
@section('title', 'Inspeksi Laporan - ' . $event->judul)
@section('crumb', 'Kepatuhan Pelaporan')
@section('heading', 'Inspeksi Laporan: ' . $event->judul)
@section('sub', 'Inspeksi transparansi realisasi keuangan dan kepatuhan pelaporan hasil kegiatan.')

@section('actions')
  <a href="{{ route('admin.pelaporan') }}" class="btn btn-outline btn-sm">← Kembali ke Kepatuhan</a>
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
  {{-- Header Ringkasan Event & Kepatuhan --}}
  <div class="card mb-6 grid gap-4 bg-white sm:grid-cols-2 lg:grid-cols-4">
    <div>
      <span class="text-xs text-gray-500">Penyelenggara (Organizer)</span>
      <p class="font-bold text-gray-800">{{ $event->organizer->nama_organisasi }}</p>
      <p class="text-xs text-gray-500 mt-0.5">{{ $event->organizer->user?->name }} · {{ $event->organizer->kontak }}</p>
    </div>
    <div>
      <span class="text-xs text-gray-500">Kategori &amp; Pelaksanaan</span>
      <p class="font-bold text-brand">{{ $event->category->nama_kategori }}</p>
      <p class="text-xs text-gray-500 mt-0.5">{{ $event->tanggal_pelaksanaan->format('d M Y') }} · {{ $event->lokasi }}</p>
    </div>
    <div>
      <span class="text-xs text-gray-500">Batas Waktu Pelaporan</span>
      <p class="font-bold text-gray-800">{{ $event->batas_laporan?->format('d M Y') ?? '-' }}</p>
      <div class="mt-1">
        @if($keuangan?->isSent() && $hasil?->isSent())
          <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">✅ Lengkap</span>
        @elseif($isTerlambat)
          <x-badge s="Terlambat"/>
        @else
          <x-badge s="Menunggu"/>
        @endif
      </div>
    </div>
    <div>
      <span class="text-xs text-gray-500">Total Dana Sponsorship</span>
      <p class="text-lg font-bold text-brand">@rp($event->dana_terkumpul)</p>
      <span class="text-[11px] text-gray-500 block mt-0.5">{{ $event->commitments->count() }} Sponsor pendana</span>
    </div>
  </div>

  {{-- 3 Kartu Ringkasan Realokasi Keuangan --}}
  <div class="grid gap-4 sm:grid-cols-3 mb-6">
    <div class="card bg-brand-50/50 border border-brand/10">
      <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Dana Masuk</span>
      <p class="text-2xl font-bold text-brand mt-1">@rp($event->dana_terkumpul)</p>
      <span class="text-[11px] text-gray-500 mt-1 block">Akumulasi komitmen sponsor diterima</span>
    </div>

    <div class="card bg-white border border-gray-100">
      <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Realisasi Pengeluaran</span>
      <p class="text-2xl font-bold text-gray-800 mt-1">@rp($keuangan ? $keuangan->totalPengeluaran() : 0)</p>
      <span class="text-[11px] text-gray-500 mt-1 block">
        {{ $keuangan ? $keuangan->items->count() . ' pos pengeluaran tercatat' : 'Belum dilaporkan' }}
      </span>
    </div>

    @php
      $sisa = $keuangan ? $keuangan->sisaDana() : 0;
      $statusSisa = $keuangan ? $keuangan->statusSisaDana() : 'Belum Ada Data';
    @endphp
    <div class="card border {{ $sisa >= 0 ? 'bg-emerald-50/50 border-emerald-200' : 'bg-red-50/50 border-red-200' }}">
      <span class="text-xs font-semibold uppercase tracking-wider {{ $sisa >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
        {{ $sisa >= 0 ? 'Sisa Dana (Surplus)' : 'Defisit Dana (Kurang)' }}
      </span>
      <p class="text-2xl font-bold mt-1 {{ $sisa >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
        @rp(abs($sisa))
      </p>
      <span class="text-[11px] mt-1 block {{ $sisa >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
        Status Neraca: <strong>{{ $statusSisa }}</strong>
      </span>
    </div>
  </div>

  {{-- Tab Navigasi Laporan --}}
  <div class="mb-6 flex gap-2 border-b border-gray-200 text-sm font-semibold">
    <button type="button" @click="tab = 'keuangan'"
            :class="tab === 'keuangan' ? 'border-brand text-brand border-b-2' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center gap-2 pb-3 px-3 transition">
      <span>1. Laporan Keuangan Transparan</span>
      @if($keuangan) <x-badge :s="$keuangan->status"/> @else <span class="text-xs text-gray-400">(Belum Ada)</span> @endif
    </button>
    <button type="button" @click="tab = 'hasil'"
            :class="tab === 'hasil' ? 'border-brand text-brand border-b-2' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center gap-2 pb-3 px-3 transition">
      <span>2. Laporan Hasil Program</span>
      @if($hasil) <x-badge :s="$hasil->status"/> @else <span class="text-xs text-gray-400">(Belum Ada)</span> @endif
    </button>
    <button type="button" @click="tab = 'sponsor'"
            :class="tab === 'sponsor' ? 'border-brand text-brand border-b-2' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center gap-2 pb-3 px-3 transition">
      <span>3. Sponsor Pendana ({{ $event->commitments->count() }})</span>
    </button>
  </div>

  {{-- ==================== TAB 1: LAPORAN KEUANGAN ==================== --}}
  <div x-show="tab === 'keuangan'" class="space-y-6">
    @if($keuangan)
      {{-- Evaluasi Sponsor Umum --}}
      @if($keuangan->reviews->isNotEmpty())
        <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4 text-xs text-blue-900 space-y-2">
          <p class="font-bold text-sm">Evaluasi Keseluruhan dari Sponsor:</p>
          <div class="grid gap-2 sm:grid-cols-2">
            @foreach($keuangan->reviews as $rev)
              <div class="rounded-xl border border-blue-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                  <span class="font-bold text-gray-800">{{ $rev->sponsor?->nama_perusahaan }}:</span>
                  <x-badge :s="$rev->status"/>
                </div>
                @if($rev->catatan)
                  <p class="mt-1 text-gray-600 italic">"{{ $rev->catatan }}"</p>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Tabel Rincian Pengeluaran & Nota --}}
      <div class="card overflow-hidden !p-0">
        <div class="border-b border-gray-100 bg-gray-50 px-4 py-3 flex items-center justify-between">
          <h3 class="font-bold text-gray-800 text-sm">Rincian Pos Pengeluaran &amp; Verifikasi Nota</h3>
          <span class="text-xs text-gray-500">Tercatat: {{ $keuangan->items->count() }} pos</span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
              <tr>
                <th class="th">No</th>
                <th class="th">Pos / Uraian Pengeluaran</th>
                <th class="th text-right">Nominal</th>
                <th class="th text-center">Bukti Nota</th>
                <th class="th">Evaluasi Sponsor per Item</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($keuangan->items as $idx => $item)
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
                      <span class="text-xs text-gray-400 italic">Tidak ada berkas</span>
                    @endif
                  </td>
                  <td class="td">
                    @if($item->reviews->isNotEmpty())
                      <div class="space-y-1">
                        @foreach($item->reviews as $rev)
                          <div class="flex flex-wrap items-center gap-1.5 text-xs">
                            <span class="font-semibold text-gray-700">{{ $rev->sponsor?->nama_perusahaan }}:</span>
                            @if($rev->status === 'Valid')
                              <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">✅ Valid</span>
                            @elseif($rev->status === 'Kurang Bukti')
                              <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">⚠️ Kurang Bukti</span>
                            @else
                              <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800">❌ Perlu Revisi</span>
                            @endif
                            @if($rev->catatan)
                              <span class="text-[11px] text-gray-600 italic">"{{ $rev->catatan }}"</span>
                            @endif
                          </div>
                        @endforeach
                      </div>
                    @else
                      <span class="text-xs text-gray-400 italic">Belum dievaluasi sponsor</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="td py-8 text-center text-gray-500">Tidak ada rincian pos pengeluaran.</td>
                </tr>
              @endforelse
            </tbody>
            <tfoot class="bg-gray-50/80 font-bold text-gray-900 border-t border-gray-200">
              <tr>
                <td colspan="2" class="td text-right">Total Pengeluaran Aktual:</td>
                <td class="td text-right text-brand">@rp($keuangan->totalPengeluaran())</td>
                <td colspan="2" class="td"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    @else
      <div class="card py-12 text-center text-gray-500 bg-white">
        <p class="text-base font-semibold text-gray-700">Laporan Keuangan Belum Disusun</p>
        <p class="text-xs text-gray-400 mt-1">Pihak penyelenggara (Organizer) belum mengirimkan rincian laporan keuangan untuk kegiatan ini.</p>
      </div>
    @endif
  </div>

  {{-- ==================== TAB 2: LAPORAN HASIL PROGRAM ==================== --}}
  <div x-show="tab === 'hasil'" class="space-y-6">
    @if($hasil)
      {{-- Evaluasi Sponsor Umum --}}
      @if($hasil->reviews->isNotEmpty())
        <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4 text-xs text-blue-900 space-y-2">
          <p class="font-bold text-sm">Evaluasi Keseluruhan dari Sponsor:</p>
          <div class="grid gap-2 sm:grid-cols-2">
            @foreach($hasil->reviews as $rev)
              <div class="rounded-xl border border-blue-200 bg-white p-3 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                  <span class="font-bold text-gray-800">{{ $rev->sponsor?->nama_perusahaan }}:</span>
                  <x-badge :s="$rev->status"/>
                </div>
                @if($rev->catatan)
                  <p class="mt-1 text-gray-600 italic">"{{ $rev->catatan }}"</p>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="card space-y-5 bg-white">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
          <h3 class="font-bold text-gray-800 text-sm">Dokumentasi &amp; Hasil Capaian Acara</h3>
          <x-badge :s="$hasil->status"/>
        </div>

        <div>
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Ringkasan Pelaksanaan</span>
          <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $hasil->ringkasan ?: '-' }}</p>
        </div>

        <div>
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Capaian Hasil Program</span>
          <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $hasil->capaian_hasil ?: '-' }}</p>
        </div>

        <div>
          <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kendala &amp; Evaluasi Lapangan</span>
          <p class="mt-1 whitespace-pre-line text-sm text-gray-800">{{ $hasil->kendala ?: 'Tidak ada kendala yang dilaporkan.' }}</p>
        </div>

        @if($hasil->berkas_dokumentasi)
          <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
            <div>
              <span class="text-xs font-semibold text-gray-500 block">Berkas Dokumentasi Lampiran</span>
              <span class="text-xs text-gray-400">Foto / Berkas laporan komprehensif</span>
            </div>
            <button type="button" @click="openPreview('{{ route('files.show', ['jenis' => 'dokumentasi', 'id' => $hasil->id]) }}', 'Dokumentasi Program')"
                    class="btn btn-outline btn-sm">
              📄 Buka Berkas Dokumentasi
            </button>
          </div>
        @endif
      </div>
    @else
      <div class="card py-12 text-center text-gray-500 bg-white">
        <p class="text-base font-semibold text-gray-700">Laporan Hasil Program Belum Disusun</p>
        <p class="text-xs text-gray-400 mt-1">Pihak penyelenggara (Organizer) belum mengirimkan ringkasan capaian hasil program untuk kegiatan ini.</p>
      </div>
    @endif
  </div>

  {{-- ==================== TAB 3: SPONSOR PENDANA ==================== --}}
  <div x-show="tab === 'sponsor'" class="space-y-6">
    <div class="card overflow-hidden !p-0 bg-white">
      <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
        <h3 class="font-bold text-gray-800 text-sm">Daftar Sponsor yang Terlibat &amp; Menyetujui Pendanaan</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
          <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase">
            <tr>
              <th class="th">No</th>
              <th class="th">Perusahaan Sponsor</th>
              <th class="th">Bentuk Dukungan</th>
              <th class="th text-right">Nominal</th>
              <th class="th text-center">Tanggapan Keuangan</th>
              <th class="th text-center">Tanggapan Hasil</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($event->commitments as $idx => $c)
              @php
                $revK = $keuangan?->reviews->firstWhere('sponsor_id', $c->sponsor_id);
                $revH = $hasil?->reviews->firstWhere('sponsor_id', $c->sponsor_id);
              @endphp
              <tr>
                <td class="td font-medium text-gray-400">{{ $idx + 1 }}</td>
                <td class="td">
                  <p class="font-semibold text-gray-900">{{ $c->sponsor->nama_perusahaan }}</p>
                  <p class="text-xs text-gray-500">{{ $c->sponsor->user?->name }} · {{ $c->sponsor->kontak }}</p>
                </td>
                <td class="td">{{ $c->bentuk_dukungan }}</td>
                <td class="td text-right font-bold text-brand">@rp($c->nominal)</td>
                <td class="td text-center">
                  @if($revK)
                    <x-badge :s="$revK->status"/>
                  @else
                    <span class="text-xs text-gray-400 italic">Belum menanggapi</span>
                  @endif
                </td>
                <td class="td text-center">
                  @if($revH)
                    <x-badge :s="$revH->status"/>
                  @else
                    <span class="text-xs text-gray-400 italic">Belum menanggapi</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="td py-8 text-center text-gray-500">Tidak ada komitmen sponsor yang diterima.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Modal In-Page Preview Berkas / Nota --}}
  <div x-show="modalOpen" x-cloak
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div @click.outside="modalOpen = false" class="relative max-h-[90vh] max-w-4xl w-full rounded-2xl bg-white p-4 shadow-2xl overflow-hidden flex flex-col">
      <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
        <h4 class="font-bold text-sm text-gray-800" x-text="previewTitle"></h4>
        <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
      </div>
      <div class="flex-1 overflow-auto flex items-center justify-center bg-gray-50 rounded-xl p-2 min-h-[350px]">
        <template x-if="previewUrl">
          <iframe :src="previewUrl" class="w-full h-[68vh] rounded-lg border-0"></iframe>
        </template>
      </div>
      <div class="mt-3 flex justify-end gap-2">
        <button type="button" @click="modalOpen = false" class="btn btn-outline btn-sm">Tutup</button>
        <a :href="previewUrl" target="_blank" class="btn btn-primary btn-sm">Buka di Tab Baru ↗</a>
      </div>
    </div>
  </div>
</div>
@endsection
