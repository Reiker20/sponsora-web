@if($c->status === 'Diterima')
<div class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-3 text-sm" x-data="{ open: false }">
  <div class="flex flex-wrap items-center justify-between gap-2">
    <div>
      <p class="font-semibold text-gray-700">Pembayaran Dana</p>
      <p class="mt-0.5 text-xs text-gray-500">
        @if($c->status_pembayaran === 'Belum Dibayar')
          Silakan transfer dana sesuai komitmen, lalu unggah bukti pembayarannya.
        @elseif($c->status_pembayaran === 'Menunggu Verifikasi')
          Bukti terkirim {{ $c->tanggal_bukti?->format('d M Y H:i') }}. Menunggu verifikasi Organizer.
        @elseif($c->status_pembayaran === 'Terverifikasi')
          Pembayaran sudah diverifikasi.
        @else
          Bukti pembayaran perlu diunggah ulang.
        @endif
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <x-badge :s="$c->status_pembayaran"/>
      @if($c->bukti_pembayaran)
        <a href="{{ route('files.show', ['pembayaran', $c->id]) }}" target="_blank" class="text-xs font-semibold text-brand underline">Lihat bukti</a>
      @endif
      @if($c->status_pembayaran !== 'Terverifikasi')
        <button type="button" @click="open = !open" class="btn btn-primary btn-sm">{{ $c->bukti_pembayaran ? 'Ganti Bukti' : 'Unggah Bukti Pembayaran' }}</button>
      @endif
    </div>
  </div>

  @if($c->status_pembayaran !== 'Terverifikasi')
    <form x-show="open" x-cloak method="POST" action="{{ route('sponsor.komitmen.bukti', $c) }}" enctype="multipart/form-data" class="mt-3 grid gap-3 sm:grid-cols-3">
      @csrf
      <div class="sm:col-span-2">
        <label class="label">Bukti pembayaran * <span class="font-normal text-gray-400">(PDF/JPG/PNG, maks 5 MB)</span></label>
        <input type="file" name="bukti" accept=".pdf,.jpg,.jpeg,.png" required class="input !p-2 text-xs">
      </div>
      <div>
        <label class="label">Catatan (opsional)</label>
        <input name="catatan_pembayaran" maxlength="300" class="input">
      </div>
      <div class="flex justify-end gap-2 sm:col-span-3">
        <button type="button" @click="open = false" class="btn btn-outline btn-sm">Batal</button>
        <button class="btn btn-primary btn-sm">Kirim Bukti</button>
      </div>
    </form>
  @endif
</div>
@endif