@auth
@php $u = auth()->user(); $dash = $u->isAdmin() ? route('admin.dashboard') : route($u->role.'.dashboard'); @endphp
<div class="relative" x-data="{o:false}" @click.outside="o=false">
  <button type="button" @click="o=!o" class="flex items-center gap-2 rounded-full bg-white py-1 pl-1 pr-3 shadow-sm hover:bg-brand-50">
    <span class="grid h-8 w-8 place-items-center rounded-full bg-lime/60 text-sm font-bold text-brand">{{ strtoupper(substr($u->displayName(), 0, 1)) }}</span>
    <span class="hidden max-w-[8rem] truncate text-sm font-medium text-brand sm:block">{{ ucfirst($u->role) }}</span>
  </button>
  <div x-show="o" x-cloak x-transition class="absolute right-0 z-50 mt-2 w-56 rounded-2xl border border-gray-100 bg-white p-2 shadow-xl">
    <div class="border-b border-gray-100 px-3 py-2"><p class="truncate text-sm font-semibold">{{ $u->displayName() }}</p>
      <p class="truncate text-xs text-gray-500">{{ $u->email }}</p>
      @unless($u->isAdmin())<div class="mt-1"><x-badge :s="$u->status_verifikasi"/></div>@endunless</div>
    <a href="{{ $dash }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-brand-50">Dashboard</a>
    @unless($u->isAdmin())<a href="{{ route($u->role.'.profil') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-brand-50">Edit Profil</a>@endunless
    <form method="POST" action="{{ route('logout') }}">@csrf
      <button class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">Keluar</button></form>
  </div>
</div>
@endauth
