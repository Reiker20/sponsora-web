@props(['s'])
@php
$map = [
  'Draft' => 'bg-gray-100 text-gray-600', 'Menunggu Verifikasi' => 'bg-amber-100 text-amber-700',
  'Menunggu' => 'bg-amber-100 text-amber-700', 'Menunggu Tanggapan' => 'bg-amber-100 text-amber-700',
  'Terverifikasi' => 'bg-emerald-100 text-emerald-700', 'Disetujui' => 'bg-emerald-100 text-emerald-700',
  'Diterima' => 'bg-emerald-100 text-emerald-700', 'Perlu Revisi' => 'bg-orange-100 text-orange-700',
  'Ditolak' => 'bg-red-100 text-red-700', 'Negosiasi' => 'bg-sky-100 text-sky-700', 'Dikirim' => 'bg-sky-100 text-sky-700',
  'Selesai' => 'bg-brand-100 text-brand', 'Dilaporkan' => 'bg-lime/50 text-brand', 'Terlambat' => 'bg-red-100 text-red-700',
];
@endphp
<span {{ $attributes->merge(['class' => 'inline-block whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold '.($map[$s] ?? 'bg-gray-100 text-gray-600')]) }}>{{ $s }}</span>
