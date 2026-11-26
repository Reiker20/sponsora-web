@extends('layouts.site')
@section('title', $event->judul)
@section('content')
@php $u = auth()->user(); $sukses = session('komitmen_sukses'); @endphp
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
  <a href="{{ route('events.index') }}" class="text-sm text-gray-500 hover:text-brand">← Kembali ke katalog</a>
  <div class="mt-6 grid gap-8 lg:grid-cols-3">
    <div class="lg:col-span-2">
      <x-cover :event="$event" class="h-64 rounded-2xl shadow sm:h-96"/>
      <div class="mt-5 flex flex-wrap items-center gap-3 text-sm font-medium text-gray-600">
        <span class="inline-flex items-center gap-1.5 text-brand">🛡 Organizer Terverifikasi</span><span>•</span><span>{{ $event->organizer->nama_organisasi }}</span>
        <x-badge :s="$event->status"/></div>
      <h1 class="mt-4 text-3xl font-bold text-gray-900 sm:text-4xl">{{ $event->judul }}</h1>
      <p class="mt-4 text-lg text-gray-500">{{ \Illuminate\Support\Str::limit($event->deskripsi, 170) }}</p>
      <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="card"><p class="text-xs text-gray-500">Pelaksanaan</p><p class="mt-1 font-bold text-brand">{{ $event->tanggal_pelaksanaan->translatedFormat('d F Y') }}</p></div>
        <div class="card"><p class="text-xs text-gray-500">Penerima manfaat</p><p class="mt-1 font-bold text-brand">{{ $event->penerima_manfaat ?: '-' }}</p></div>
        <div class="card"><p class="text-xs text-gray-500">Lokasi</p><p class="mt-1 font-bold text-brand">{{ $event->lokasi }}</p></div>
      </div>
      <h2 class="mt-8 text-xl font-semibold">Tentang Kegiatan</h2>
      <p class="mt-3 whitespace-pre-line leading-relaxed text-gray-600">{{ $event->deskripsi }}</p>
      <h2 class="mt-8 text-xl font-semibold">Benefit untuk Sponsor</h2>
      <p class="mt-3 whitespace-pre-line leading-relaxed text-gray-600">{{ $event->benefit_sponsor }}</p>
    </div>

    <aside class="self-start lg:sticky lg:top-24">
      <div class="card p-6 shadow-lg">
        <p class="text-sm text-gray-500">Dana Terkumpul</p>
        <p class="text-3xl font-bold">@rp($event->dana_terkumpul)</p>
        <p class="text-xs text-gray-500">dari target @rp($event->target_dana)</p>
        <p class="mt-3 text-right text-xs font-semibold text-emerald-700">{{ $event->progress }}%</p>
        <div class="h-2 rounded-full bg-gray-100"><div class="h-2 rounded-full bg-lime" style="width: {{ $event->progress }}%"></div></div>
        <div class="mt-4 flex justify-between border-t border-gray-100 pt-4">
          <div><p class="text-lg font-bold text-brand">{{ $event->mitra_count }}</p><p class="text-xs text-gray-500">Mitra Sponsor</p></div>
          <div class="text-right"><p class="text-lg font-bold text-brand">{{ $event->sisa_hari }} Hari</p><p class="text-xs text-gray-500">Tersisa</p></div></div>

        <div class="mt-5 border-t border-gray-100 pt-5">
        {{-- ===== SPONSOR: tombol Danai Event ===== --}}
        @if($u?->isSponsor())
          @if($myCommitment && in_array($myCommitment->status, ['Menunggu Tanggapan','Diterima']))
            <div class="rounded-xl bg-brand-50 p-4 text-center text-sm space-y-2">
              <p class="font-semibold text-brand">Donasi / Komitmen Anda</p>
              <p class="text-lg font-bold">@rp($myCommitment->nominal)</p>
              <div class="flex flex-wrap justify-center gap-1.5">
                <x-badge :s="$myCommitment->status"/>
                @if($myCommitment->status_pembayaran)
                  <x-badge :s="$myCommitment->status_pembayaran"/>
                @endif
              </div>

              @if($myCommitment->hasInvoice())
                <p class="text-xs text-emerald-700 font-medium">Invoice {{ $myCommitment->nomor_invoice }} telah terbit.</p>
                <div class="flex flex-col gap-2 pt-1">
                  <a href="{{ route('sponsor.inbox.show', $myCommitment) }}" class="btn btn-primary btn-sm">Lihat &amp; Download Invoice</a>
                </div>
              @elseif($myCommitment->bukti_pembayaran)
                <p class="text-xs text-amber-800">Bukti transfer terkirim. Menunggu validasi nominal dari Organizer.</p>
                <a href="{{ route('files.show', ['pembayaran', $myCommitment->id]) }}" target="_blank" class="inline-block text-xs font-semibold text-brand underline">Lihat bukti transfer</a>
              @elseif($myCommitment->status === 'Diterima')
                <a href="{{ route('sponsor.pembayaran', $myCommitment) }}" class="btn btn-primary btn-sm mt-2">Transfer &amp; Unggah Bukti</a>
              @endif
              <a href="{{ route('sponsor.dashboard') }}" class="mt-2 block text-xs font-semibold text-brand underline">Lihat di dashboard</a>
            </div>

          @elseif(! $event->acceptsCommitments())
            <div class="rounded-xl bg-gray-100 p-4 text-center text-sm text-gray-600">Event ini tidak lagi menerima komitmen.</div>
          @elseif(! $u->isVerified())
            <div class="rounded-xl bg-amber-50 p-4 text-center text-sm text-amber-700">Akun Anda berstatus <b>{{ $u->status_verifikasi }}</b>. Donasi dapat dilakukan setelah Admin menyetujui akun.</div>
          @else
            <div x-data="{open: {{ ($sukses || $errors->any()) ? 'true' : 'false' }}, step: {{ $sukses ? 3 : 1 }}, nominal: '{{ old('nominal', '') }}', bentuk: '{{ old('bentuk_dukungan', 'Dana Tunai') }}', setuju: false}">
              @if($event->hasPaymentMethod())
                <div class="mb-4 rounded-xl border border-brand/15 bg-brand-50/60 p-3 text-xs space-y-2">
                  <p class="font-bold text-brand">Tujuan Transfer Langsung Organizer</p>
                  @if($event->hasBankAccount())
                    <div class="rounded-lg bg-white p-2.5 border border-gray-100">
                      <p class="font-semibold text-gray-800">{{ $event->nama_bank }}</p>
                      <p class="text-sm font-bold tracking-wide text-brand">{{ $event->nomor_rekening }}</p>
                      <p class="text-[11px] text-gray-500">a.n. {{ $event->atas_nama_rekening }}</p>
                    </div>
                  @endif
                  @if($event->qris_url)
                    <div class="rounded-lg bg-white p-2.5 border border-gray-100 text-center">
                      <p class="mb-1.5 text-[11px] font-semibold text-gray-600">Scan QRIS Organizer</p>
                      <img src="{{ $event->qris_url }}" alt="QRIS {{ $event->judul }}" class="mx-auto h-36 w-36 rounded-lg border border-gray-100 object-contain">
                    </div>
                  @endif
                </div>
              @endif

              <button type="button" @click="open=true; step=1" class="btn btn-primary w-full !py-3.5 !text-base">Danai Event Ini →</button>
              <p class="mt-3 text-center text-[11px] leading-5 text-gray-500">Transfer ke Rekening atau QRIS Organizer di atas, lalu input nominal &amp; unggah bukti transfernya.</p>

              <div x-show="open" x-cloak class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/50 p-4" @keydown.escape.window="open=false">
                <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl" @click.outside="open=false">
                  <div class="flex items-start justify-between"><div><p class="text-xs font-semibold uppercase text-brand/60">Donasi &amp; Bukti Transfer</p><h3 class="text-xl font-bold text-brand">Berikan Dukunganmu</h3></div>
                    <button type="button" @click="open=false" class="text-2xl leading-none text-gray-400">×</button></div>
                  <div class="mt-4 grid grid-cols-3 gap-2"><template x-for="i in 3"><div class="h-1.5 rounded-full" :class="i<=step ? 'bg-lime' : 'bg-gray-200'"></div></template></div>

                  @if($errors->any())
                    <div class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                      <ul class="list-disc pl-4 space-y-0.5">
                        @foreach($errors->all() as $err)
                          <li>{{ $err }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <form method="POST" action="{{ route('sponsor.komitmen.store', $event) }}" enctype="multipart/form-data" class="mt-5">@csrf
                    <div x-show="step===1" class="space-y-4">
                      <div class="rounded-xl bg-brand-50 p-3 text-xs space-y-2">
                        <p class="font-semibold text-brand">Transfer ke Tujuan Berikut ({{ $event->organizer->nama_organisasi }}):</p>
                        @if($event->hasBankAccount())
                          <div class="rounded-lg bg-white p-2.5 flex items-center justify-between gap-2">
                            <div>
                              <p class="font-bold text-gray-800">{{ $event->nama_bank }} · {{ $event->nomor_rekening }}</p>
                              <p class="text-[11px] text-gray-500">a.n. {{ $event->atas_nama_rekening }}</p>
                            </div>
                            <button type="button" onclick="navigator.clipboard?.writeText('{{ $event->nomor_rekening }}')" class="btn btn-outline btn-sm !py-1 !px-2 text-[11px]">Salin</button>
                          </div>
                        @endif
                        @if($event->qris_url)
                          <div class="rounded-lg bg-white p-2.5 text-center">
                            <p class="text-[11px] font-semibold text-gray-600 mb-1">Atau Scan QRIS:</p>
                            <img src="{{ $event->qris_url }}" alt="QRIS {{ $event->judul }}" class="mx-auto h-36 w-36 rounded object-contain">
                          </div>
                        @endif
                      </div>

                      <div><label class="label">Nominal donasi yang ditransfer (Rp) *</label><input type="number" name="nominal" x-model="nominal" min="10000" step="1000" required class="input" placeholder="mis. 5000000"></div>
                      <div><label class="label">Unggah bukti transfer (JPG/PNG/PDF, maks 5 MB) *</label><input type="file" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.pdf" required class="input !p-2 text-xs"></div>
                      <div><label class="label">Bentuk dukungan</label><select name="bentuk_dukungan" x-model="bentuk" class="input"><option>Dana Tunai</option><option>Barang</option><option>Jasa / Layanan</option><option>Lainnya</option></select></div>
                      <div><label class="label">Catatan untuk organizer (opsional)</label><textarea name="catatan" rows="2" maxlength="500" class="input" placeholder="mis. Transfer atas nama PT ... pukul 14:15">{{ old('catatan') }}</textarea></div>
                      <div class="flex justify-end"><button type="button" class="btn btn-primary" :disabled="!nominal || nominal < 10000" @click="step=2">Lanjut →</button></div>
                    </div>
                    <div x-show="step===2" x-cloak class="space-y-4">
                      <div class="rounded-xl border border-gray-100 p-4 text-sm"><p class="text-xs text-gray-500">Ringkasan klaim donasi</p>
                        <p class="mt-1 text-2xl font-bold text-brand" x-text="'Rp ' + Number(nominal||0).toLocaleString('id-ID')"></p><p class="text-gray-600" x-text="bentuk"></p></div>
                      <div class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Organizer akan memeriksa bukti transfer dan memvalidasi nominal aktual yang diterima. Setelah divalidasi, Invoice resmi akan dikirim ke Inbox Anda.</div>
                      <label class="flex items-start gap-2 text-sm"><input type="checkbox" x-model="setuju" class="mt-1 rounded"> Saya menyatakan bukti transfer dan nominal yang saya inputkan benar.</label>
                      <div class="flex justify-between"><button type="button" class="btn btn-outline" @click="step=1">Kembali</button><button class="btn btn-primary" :disabled="!setuju">Kirim Donasi &amp; Bukti</button></div>
                    </div>
                  </form>

                  <div x-show="step===3" x-cloak class="py-6 text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-lime text-2xl text-brand">✓</div>
                    <h4 class="mt-4 text-lg font-bold text-brand">Donasi &amp; Bukti Transfer Terkirim</h4>
                    <p class="mt-1 text-sm text-gray-500">Klaim sebesar @rp((int) $sukses) sedang menunggu pemeriksaan &amp; validasi nominal oleh Organizer.</p>
                    <div class="mt-5 flex justify-center gap-2"><a href="{{ route('sponsor.dashboard') }}" class="btn btn-primary btn-sm">Lihat Dashboard</a><button type="button" @click="open=false" class="btn btn-outline btn-sm">Tutup</button></div>
                  </div>
                </div>
              </div>
            </div>
          @endif

        {{-- ===== ORGANIZER ===== --}}
        @elseif($u?->isOrganizer())
          @if($u->organizerProfile?->id === $event->organizer_id)
            <div class="rounded-2xl bg-brand-50 p-5 space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-brand">Status Proposal Anda</span>
                <x-badge :s="$event->status"/>
              </div>

              @if($event->status === 'Perlu Revisi')
                @if($event->verifications->first()?->catatan)
                  <div class="rounded-xl border border-orange-200 bg-orange-50 p-3 text-xs text-orange-800">
                    <p class="font-bold">Catatan Perbaikan dari Admin:</p>
                    <p class="mt-1">{{ $event->verifications->first()->catatan }}</p>
                  </div>
                @endif
              @elseif($event->status === 'Menunggu Verifikasi')
                <p class="text-xs text-amber-800 italic">Proposal ini sedang dalam antrean verifikasi Admin sebelum tampil di katalog publik.</p>
              @elseif($event->status === 'Draft')
                <p class="text-xs text-gray-600">Proposal ini masih berstatus Draft dan belum dipublikasikan ke katalog.</p>
              @elseif($event->status === 'Terverifikasi')
                <p class="text-xs text-emerald-800 font-medium">✓ Proposal aktif dan dapat menerima komitmen dari Sponsor.</p>
              @endif

              <div class="flex flex-col gap-2 pt-2">
                @if($event->isEditable())
                  <a href="{{ route('organizer.proposals.edit', $event) }}" class="btn btn-outline w-full text-center">Ubah Proposal</a>
                  <form method="POST" action="{{ route('organizer.proposals.ajukan', $event) }}" onsubmit="return confirm('Ajukan proposal \'{{ addslashes($event->judul) }}\' untuk diverifikasi Admin? Setelah diajukan, proposal akan ditinjau Admin sebelum tayang di katalog.')">
                    @csrf
                    <button class="btn btn-primary w-full" @disabled(! $u->isVerified())>
                      Ajukan Verifikasi Admin
                    </button>
                  </form>
                  @unless($u->isVerified())
                    <p class="text-[11px] text-amber-700 text-center">Akun Anda masih berstatus <b>{{ $u->status_verifikasi }}</b>. Proposal baru dapat diajukan setelah akun disetujui Admin.</p>
                  @endunless
                @elseif($event->status === 'Terverifikasi')
                  <form method="POST" action="{{ route('organizer.proposals.selesai', $event) }}" onsubmit="return confirm('Tandai event selesai? Event tidak lagi menerima komitmen dan kewajiban pelaporan dibuka.')">
                    @csrf
                    <button class="btn btn-lime w-full">Tandai Event Selesai</button>
                  </form>
                @elseif(in_array($event->status, ['Selesai', 'Dilaporkan']))
                  <a href="{{ route('organizer.laporan.show', $event) }}" class="btn btn-lime w-full text-center">Buka Laporan Pertanggungjawaban</a>
                @endif
              </div>
            </div>
          @else
            <div class="rounded-2xl bg-gray-100 p-5 text-center"><p class="text-sm font-bold text-brand">Tampilan Organizer</p>
              <p class="mt-1 text-xs text-gray-500">Akun Organizer dapat melihat progres pendanaan tetapi tidak dapat mendanai event ini.</p></div>
          @endif
        @elseif($u?->isAdmin())
          <div class="rounded-2xl bg-gray-100 p-5 text-center"><p class="text-sm font-bold text-brand">Tampilan Admin</p>
            <a href="{{ route('admin.proposal.show', $event) }}" class="mt-2 inline-block text-xs font-semibold text-brand underline">Buka di panel verifikasi</a></div>
        @else
          <a href="{{ route('login', ['role' => 'sponsor']) }}" class="btn btn-primary w-full !py-3.5">Masuk sebagai Sponsor untuk Mendanai</a>
          <p class="mt-3 text-center text-[11px] text-gray-500">Belum punya akun? <a href="{{ route('register', ['role' => 'sponsor']) }}" class="font-semibold text-brand underline">Daftar</a></p>
        @endif
        </div>
      </div>
    </aside>
  </div>
</div>
@endsection