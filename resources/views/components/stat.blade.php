@props(['label', 'value', 'sub' => null])
<div class="card"><p class="text-xs text-gray-500">{{ $label }}</p><p class="mt-1 text-2xl font-bold text-brand">{{ $value }}</p>@if($sub)<p class="mt-0.5 text-xs text-gray-400">{{ $sub }}</p>@endif</div>
