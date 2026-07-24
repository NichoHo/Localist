<nav x-data="{ open: false }" class="glass-header">
    <div class="mx-auto max-w-[76rem] px-4 sm:px-6">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" class="font-display text-lg font-bold tracking-tight text-ink">Localist</a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>
                    <x-nav-link :href="route('portal.edit')" :active="request()->routeIs('portal.edit')">{{ __('Edit listing') }}</x-nav-link>
                    <x-nav-link :href="route('portal.photos')" :active="request()->routeIs('portal.photos')">{{ __('Photos') }}</x-nav-link>
                    <x-nav-link :href="route('portal.leads')" :active="request()->routeIs('portal.leads')">{{ __('Leads') }}</x-nav-link>
                    <x-nav-link :href="route('portal.billing')" :active="request()->routeIs('portal.billing')">{{ __('Billing') }}</x-nav-link>
                    @if (auth()->user()->role === 'admin')
                        <x-nav-link :href="route('admin')" :active="request()->routeIs('admin')">{{ __('Admin') }}</x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-ink-muted transition hover:bg-sunken hover:text-ink focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="size-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-lg p-2 text-ink-subtle transition hover:bg-sunken hover:text-ink focus:outline-none">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-line-subtle sm:hidden">
        <div class="space-y-1 py-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('portal.edit')" :active="request()->routeIs('portal.edit')">{{ __('Edit listing') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('portal.photos')" :active="request()->routeIs('portal.photos')">{{ __('Photos') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('portal.leads')" :active="request()->routeIs('portal.leads')">{{ __('Leads') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('portal.billing')" :active="request()->routeIs('portal.billing')">{{ __('Billing') }}</x-responsive-nav-link>
            @if (auth()->user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin')" :active="request()->routeIs('admin')">{{ __('Admin') }}</x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-line-subtle py-4">
            <div class="px-4">
                <div class="text-base font-medium text-ink">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-ink-muted">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
