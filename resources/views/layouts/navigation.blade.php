@php
    $user = auth()->user();
    $unread = $user->business()?->leads()->whereNull('read_at')->count() ?? 0;
    $items = [
        ['Dashboard', route('dashboard'), 'dashboard', request()->routeIs('dashboard'), 0],
        ['Edit listing', route('portal.edit'), 'pencil', request()->routeIs('portal.edit', 'portal.photos'), 0],
        ['Leads', route('portal.leads'), 'inbox', request()->routeIs('portal.leads'), $unread],
        ['Billing', route('portal.billing'), 'card', request()->routeIs('portal.billing'), 0],
    ];
    if ($user->role === 'admin') {
        $items[] = ['Admin', route('admin'), 'shield', request()->routeIs('admin'), 0];
    }
    $initials = collect(explode(' ', $user->name))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
@endphp

{{-- Desktop: top bar with pill links. Mobile: a slim top bar plus a bottom tab bar. --}}
<header class="sticky top-0 z-40 border-b border-line bg-canvas/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-[76rem] items-center gap-6 px-4 sm:px-6">
        <a href="{{ route('dashboard') }}" class="font-display text-xl font-bold tracking-tight text-ink lg:text-2xl">Localist</a>

        <nav class="hidden items-center gap-1 lg:ml-4 lg:flex" aria-label="Portal">
            @foreach ($items as [$label, $href, $icon, $active, $badge])
                <x-nav-link :href="$href" :active="$active">
                    {{ __($label) }}
                    @if ($badge > 0)
                        <span class="ml-2 rounded-full bg-accent px-1.5 text-xs font-semibold tabular-nums text-on-accent">{{ $badge }}</span>
                    @endif
                </x-nav-link>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('home') }}" class="hidden rounded-full px-3 py-2 text-sm font-medium text-ink-muted transition-colors hover:text-ink sm:inline-flex">View directory</a>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex items-center gap-2 rounded-full border border-line bg-surface py-1 pl-1 pr-3 text-sm font-medium text-ink transition-colors hover:border-line-strong focus:outline-none" aria-label="Account menu">
                        <span class="grid size-8 place-items-center rounded-full bg-accent text-xs font-bold uppercase text-on-accent">{{ $initials }}</span>
                        <span class="hidden sm:inline">{{ $user->name }}</span>
                        <x-icon name="chevron-down" class="size-3.5 text-ink-subtle" />
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                    <x-dropdown-link :href="route('home')">{{ __('View directory') }}</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>

<nav class="tabbar" style="grid-template-columns: repeat({{ count($items) }}, minmax(0, 1fr))" aria-label="Portal">
    @foreach ($items as [$label, $href, $icon, $active, $badge])
        <a href="{{ $href }}" class="tabbar-item" @if ($active) aria-current="page" @endif>
            <span class="tabbar-icon">
                <x-icon :name="$icon" class="size-5" />
                @if ($badge > 0)
                    <span class="absolute right-[22%] top-0 grid min-w-4 place-items-center rounded-full bg-ink px-1 text-[10px] font-bold leading-4 tabular-nums text-canvas">{{ $badge }}</span>
                @endif
            </span>
            {{ __($label) }}
        </a>
    @endforeach
</nav>
