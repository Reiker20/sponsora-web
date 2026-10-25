<div class="mx-auto max-w-7xl empty:hidden">
  @if (session('success'))
    <div x-data="{s:true}" x-show="s" class="mt-4 flex items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      <span>{{ session('success') }}</span><button type="button" @click="s=false" class="font-bold">×</button></div>
  @endif
  @if (session('error'))
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
  @endif
  @if ($errors->any())
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
</div>
