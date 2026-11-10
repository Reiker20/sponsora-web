<svg {{ $attributes }} viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="20" stroke-linecap="round" aria-hidden="true">
  @for($i = 0; $i < 12; $i++)<line x1="100" y1="100" x2="100" y2="10" transform="rotate({{ $i * 30 }} 100 100)"/>@endfor
</svg>