<x-public-layout :title="$business->name.' | '.$business->category->name.' in '.$business->city->name.' | Localist'" :description="Str::limit($business->description ?: $business->category->name.' in '.$business->city->name.', Malaysia.', 155)">
    <x-json-ld :data="\App\Services\Seo::localBusiness($business)" />
    <x-json-ld :data="\App\Services\Seo::breadcrumbs([
        ['Home', route('home')],
        [$business->city->name, route('city', $business->city)],
        [$business->category->name, route('city.category', [$business->city, $business->category])],
        [$business->name, route('business', $business)],
    ])" />

    @php
        $open = $business->isOpenNow();
        $statusLabel = $business->openStatusLabel();
        $dayKeys = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $today = $dayKeys[now($business->city->timezone ?? 'Asia/Kuala_Lumpur')->dayOfWeekIso - 1];
        $todaySlot = $business->hours[$today] ?? null;
        $todayText = $todaySlot ? (($todaySlot['closed'] ?? false) ? 'Closed' : ($todaySlot['open'] ?? '').' – '.($todaySlot['close'] ?? '')) : null;
        $directions = ($business->lat && $business->lng)
            ? 'https://www.google.com/maps/dir/?api=1&destination='.$business->lat.','.$business->lng
            : ($business->address ? 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($business->name.' '.$business->address) : null);
        $categoryUrl = route('city.category', [$business->city, $business->category]);
    @endphp

    <div class="mx-auto max-w-[76rem] px-4 py-4 sm:px-6 lg:py-10">
        {{-- Desktop: breadcrumbs. Mobile: a single back link, like an app. --}}
        <x-breadcrumbs class="hidden lg:flex" :items="[
            ['Home', route('home')],
            [$business->city->name, route('city', $business->city)],
            [$business->category->name, $categoryUrl],
            [$business->name, null],
        ]" />
        <a href="{{ $categoryUrl }}" class="-ml-1 inline-flex items-center gap-1 rounded-full py-2 pr-3 text-sm font-medium text-ink-muted transition-colors hover:text-ink lg:hidden">
            <x-icon name="chevron-left" class="size-5" />{{ $business->category->name }} in {{ $business->city->name }}
        </a>

        <div class="mt-3 lg:mt-6">
            <span class="icon-tile mb-4 size-14 lg:hidden"><x-category-icon :slug="$business->category->slug" class="size-7" /></span>
            <h1 class="font-display text-[2rem] font-bold leading-[1.05] tracking-[-0.03em] text-ink sm:text-4xl lg:text-5xl">{{ $business->name }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-ink-muted">
                <span>{{ $business->category->name }} &middot; {{ $business->city->name }}, {{ $business->city->region }}</span>
                @if ($business->isFeatured())
                    <span class="featured-tag">Featured</span>
                @endif
                @unless ($business->user_id)
                    <a href="{{ route('claim', $business) }}" class="text-sm font-medium text-ink underline decoration-line-strong underline-offset-4 transition-colors hover:decoration-ink lg:hidden">Is this your business?</a>
                @endunless
            </div>
        </div>

        {{-- Status: a soft banner on mobile, a four-cell strip on desktop --}}
        @if ($statusLabel)
            <div @class(['mt-5 flex items-center gap-3 rounded-2xl px-4 py-3 lg:hidden', 'bg-success-soft' => $open, 'bg-sunken' => ! $open])>
                <span @class(['size-2.5 rounded-full', 'bg-success' => $open, 'bg-ink-subtle' => ! $open])></span>
                <div class="text-sm">
                    <p @class(['font-semibold', 'text-success-text' => $open, 'text-ink' => ! $open])>{{ $statusLabel }}</p>
                    @if ($todayText)<p class="text-ink-muted">Today {{ $todayText }}</p>@endif
                </div>
            </div>
            <dl class="mt-6 hidden grid-cols-4 divide-x divide-line overflow-hidden rounded-2xl border border-line bg-surface lg:grid">
                <div class="px-5 py-4"><dt class="text-xs text-ink-subtle">Status</dt><dd @class(['mt-0.5 font-semibold', 'text-success-text' => $open, 'text-ink' => ! $open])>{{ $statusLabel }}</dd></div>
                <div class="px-5 py-4"><dt class="text-xs text-ink-subtle">Category</dt><dd class="mt-0.5 font-medium text-ink">{{ $business->category->name }}</dd></div>
                <div class="px-5 py-4"><dt class="text-xs text-ink-subtle">City</dt><dd class="mt-0.5 font-medium text-ink">{{ $business->city->name }}</dd></div>
                <div class="px-5 py-4"><dt class="text-xs text-ink-subtle">Today</dt><dd class="mt-0.5 font-medium tabular-nums text-ink">{{ $todayText ?? 'Not listed' }}</dd></div>
            </dl>
        @endif

        <div class="mt-8 grid gap-8 lg:mt-10 lg:grid-cols-[1fr_22rem] lg:gap-12">
            <div class="min-w-0 space-y-8 lg:space-y-10">
                @if ($business->description)
                    <section>
                        <h2 class="font-display text-xl font-bold tracking-tight text-ink lg:text-2xl">About</h2>
                        <p class="mt-3 max-w-[45rem] text-[1.0625rem] leading-relaxed text-ink-muted">{{ $business->description }}</p>
                    </section>
                @endif

                @if ($business->hours)
                    <section>
                        <details open class="surface-card group overflow-hidden">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 lg:pointer-events-none lg:px-6 lg:py-5 [&::-webkit-details-marker]:hidden">
                                <span class="font-display text-xl font-bold tracking-tight text-ink lg:text-2xl">Opening hours</span>
                                <x-icon name="chevron-down" class="size-5 text-ink-subtle transition-transform group-open:rotate-180 lg:hidden" />
                            </summary>
                            <dl class="divide-y divide-line border-t border-line">
                                @foreach ($business->hours as $day => $slot)
                                    <div @class(['flex items-center justify-between gap-4 px-5 py-2.5 text-sm lg:px-6', 'bg-accent-soft' => $day === $today])>
                                        <dt @class(['capitalize', 'font-semibold text-ink' => $day === $today, 'text-ink-muted' => $day !== $today])>{{ ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'][$day] ?? $day }}@if ($day === $today) <span class="ml-1 text-xs font-medium text-ink-muted">Today</span>@endif</dt>
                                        <dd @class(['tabular-nums', 'text-ink-subtle' => ($slot['closed'] ?? false), 'font-medium text-ink' => ! ($slot['closed'] ?? false)])>{{ ($slot['closed'] ?? false) ? 'Closed' : ($slot['open'] ?? '').' – '.($slot['close'] ?? '') }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </details>
                    </section>
                @endif

                <section>
                    <h2 class="font-display text-xl font-bold tracking-tight text-ink lg:text-2xl">Find us</h2>
                    <dl class="surface-card mt-4 space-y-4 p-5 text-sm lg:p-6">
                        @if ($business->address)
                            <div class="flex gap-3">
                                <x-icon name="pin" class="mt-0.5 size-[18px] text-ink-subtle" />
                                <div><dt class="text-ink-subtle">Address</dt><dd class="mt-0.5 text-ink">{{ $business->address }}</dd></div>
                            </div>
                        @endif
                        @if ($business->phone)
                            <div class="flex gap-3">
                                <x-icon name="phone" class="mt-0.5 size-[18px] text-ink-subtle" />
                                <div><dt class="text-ink-subtle">Phone</dt><dd class="mt-0.5"><a href="tel:{{ $business->phone }}" class="font-medium tabular-nums text-ink hover:underline">{{ $business->phone }}</a></dd></div>
                            </div>
                        @endif
                        @if ($business->website && $business->plan->allows_website)
                            <div class="flex gap-3">
                                <x-icon name="globe" class="mt-0.5 size-[18px] text-ink-subtle" />
                                <div class="min-w-0"><dt class="text-ink-subtle">Website</dt><dd class="mt-0.5"><a href="{{ $business->website }}" rel="nofollow" class="break-all font-medium text-ink hover:underline">{{ $business->website }}</a></dd></div>
                            </div>
                        @endif
                        @if ($directions)
                            <a href="{{ $directions }}" target="_blank" rel="noopener" class="btn btn-ghost w-full lg:hidden"><x-icon name="navigation" class="size-4" />Get directions</a>
                        @endif
                    </dl>
                </section>

                @if ($business->media->isNotEmpty())
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($business->media as $photo)
                            <img src="{{ Storage::disk(config('filesystems.photo_disk'))->url($photo->path) }}" alt="{{ $photo->alt }}" loading="lazy"
                                class="aspect-square w-full rounded-2xl border border-line object-cover">
                        @endforeach
                    </div>
                @endif

                @if ($business->lat && $business->lng)
                    <div class="overflow-hidden rounded-2xl border border-line">
                        <iframe title="Map showing {{ $business->name }}" width="100%" height="320" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://maps.google.com/maps?q={{ $business->lat }},{{ $business->lng }}&z=15&output=embed"></iframe>
                    </div>
                @endif
            </div>

            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                {{-- Desktop only: the mobile call button lives in the sticky bottom bar --}}
                @if ($business->phone)
                    <div class="surface-card hidden p-6 lg:block">
                        <p class="text-sm text-ink-muted">Call the shop</p>
                        <p class="mt-1 font-display text-2xl font-bold tabular-nums tracking-tight text-ink">{{ $business->phone }}</p>
                        <div class="mt-5 grid grid-cols-2 gap-2">
                            <a href="tel:{{ $business->phone }}" class="btn btn-primary"><x-icon name="phone" class="size-4" />Call</a>
                            @if ($directions)
                                <a href="{{ $directions }}" target="_blank" rel="noopener" class="btn btn-ghost"><x-icon name="navigation" class="size-4" />Directions</a>
                            @else
                                <a href="#enquire" class="btn btn-ghost">Enquire</a>
                            @endif
                        </div>
                    </div>
                @endif

                <div id="enquire" class="surface-card scroll-mt-24 p-5 lg:p-6">
                    <h2 class="font-display text-lg font-bold text-ink">Send an enquiry</h2>
                    @if (session('enquiry_sent'))
                        <div class="mt-4 flex items-start gap-2.5 rounded-xl bg-success-soft p-4 text-sm">
                            <x-icon name="check" class="mt-0.5 size-[18px] shrink-0 text-success" />
                            <p class="font-medium text-success-text">Thanks! Your enquiry has been sent.</p>
                        </div>
                    @else
                        <form method="post" action="{{ route('business.enquire', $business) }}" class="mt-4 space-y-3">
                            @csrf
                            {{-- Honeypot: humans never see it, bots fill it. --}}
                            <input name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="hidden">
                            <div>
                                <label for="enq-name" class="sr-only">Your name</label>
                                <input id="enq-name" name="name" value="{{ old('name') }}" placeholder="Your name" required class="field">
                            </div>
                            <div>
                                <label for="enq-email" class="sr-only">Email</label>
                                <input id="enq-email" name="email" type="email" value="{{ old('email') }}" placeholder="Email" required class="field">
                            </div>
                            <div>
                                <label for="enq-phone" class="sr-only">Phone (optional)</label>
                                <input id="enq-phone" name="phone" value="{{ old('phone') }}" placeholder="Phone (optional)" class="field">
                            </div>
                            <div>
                                <label for="enq-message" class="sr-only">What do you need?</label>
                                <textarea id="enq-message" name="message" rows="4" placeholder="What do you need?" required class="field">{{ old('message') }}</textarea>
                            </div>
                            @if ($errors->any())
                                <p class="text-sm font-medium text-danger-text">{{ $errors->first() }}</p>
                            @endif
                            <button type="submit" class="btn btn-primary w-full">Send enquiry</button>
                        </form>
                    @endif
                </div>

                @unless ($business->user_id)
                    <div class="rounded-2xl border border-dashed border-line-strong bg-canvas-2 p-5 text-sm">
                        <p class="font-medium text-ink">Is this your business?</p>
                        <a href="{{ route('claim', $business) }}" class="mt-1 inline-flex items-center gap-1 font-semibold text-ink underline decoration-line-strong underline-offset-4 transition-colors hover:decoration-ink">
                            Claim this listing
                            <x-icon name="arrow-up-right" class="size-4" />
                        </a>
                    </div>
                @endunless
            </aside>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-14 lg:mt-20">
                <h2 class="font-display text-xl font-bold tracking-tight text-ink lg:text-2xl">More {{ strtolower($business->category->name) }} in {{ $business->city->name }}</h2>
                <div class="business-list mt-5">
                    @foreach ($related as $other)
                        <x-business-card :business="$other" :hide-category="true" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Mobile bottom action bar (replaces the tab bar on this page) --}}
    <x-slot:actions>
        <div class="fixed inset-x-0 bottom-0 z-40 flex gap-2 border-t border-line bg-surface/95 px-4 pt-3 backdrop-blur lg:hidden" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom))">
            @if ($business->phone)
                <a href="tel:{{ $business->phone }}" class="btn btn-primary btn-lg flex-1"><x-icon name="phone" class="size-5" />Call now</a>
            @endif
            @if ($directions)
                <a href="{{ $directions }}" target="_blank" rel="noopener" aria-label="Directions" class="row-action row-action-view size-12"><x-icon name="navigation" class="size-5" /></a>
            @endif
            <a href="#enquire" aria-label="Send an enquiry" class="row-action row-action-view size-12"><x-icon name="message" class="size-5" /></a>
        </div>
    </x-slot:actions>

    <script>navigator.sendBeacon('{{ route('business.view', $business) }}');</script>
</x-public-layout>
