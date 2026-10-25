@props(['data'])
@php $max = max(1, $data->max()); @endphp
<div class="flex h-40 items-end gap-2 sm:gap-4">
  @foreach ($data as $label => $val)
    <div class="flex h-full flex-1 flex-col items-center justify-end gap-1" title="Rp {{ number_format($val, 0, ',', '.') }}">
      <div class="w-full rounded-t-lg {{ $loop->last ? 'bg-brand' : 'bg-brand-100' }}" style="height: {{ max(4, $val / $max * 85) }}%"></div>
      <span class="text-[11px] text-gray-500">{{ $label }}</span>
    </div>
  @endforeach
</div>
