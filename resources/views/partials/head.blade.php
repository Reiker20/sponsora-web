<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Sponsora') — Sponsora</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  fontFamily: { sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
  colors: { brand: { DEFAULT: '#17402F', 700: '#1f5a43', 50: '#edf3ee', 100: '#dce8df' },
            lime: { DEFAULT: '#c6e04a', 300: '#d6ea78' }, cream: '#fafbf6' } } } }
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<style type="text/tailwindcss">
  @layer components {
    .btn { @apply inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed; }
    .btn-primary { @apply bg-brand text-white hover:bg-brand-700; }
    .btn-lime { @apply bg-lime text-brand hover:bg-lime-300; }
    .btn-outline { @apply border border-brand/20 bg-white text-brand hover:bg-brand-50; }
    .btn-danger { @apply bg-red-600 text-white hover:bg-red-700; }
    .btn-sm { @apply px-3 py-1.5 text-xs; }
    .input { @apply w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20; }
    .label { @apply mb-1.5 block text-xs font-semibold text-gray-700; }
    .card { @apply rounded-2xl border border-gray-100 bg-white p-5 shadow-sm; }
    .th { @apply px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500; }
    .td { @apply px-4 py-3 text-sm; }
  }
  [x-cloak] { display: none !important; }
  body { background: #fafbf6; }
</style>
