<x-public-layout :title="$business->name.' | '.$business->category->name.' in '.$business->city->name.' | Localist'" :description="Str::limit($business->description, 155)">
    <x-json-ld :data="\App\Services\Seo::localBusiness($business)" />
    <x-json-ld :data="\App\Services\Seo::breadcrumbs([
        ['Home', route('home')],
        [$business->city->name, route('city', $business->city)],
        [$business->category->name, route('city.category', [$business->city, $business->category])],
        [$business->name, route('business', $business)],
    ])" />

    <div class="mx-auto max-w-[76rem] px-4 py-10 sm:px-6 sm:py-12">
        <x-breadcrumbs :items="[
            ['Home', route('home')],
            [$business->city->name, route('city', $business->city)],
            [$business->category->name, route('city.category', [$business->city, $business->category])],
            [$business->name, null],
        ]" />

        <div class="mt-6 grid gap-10 lg:grid-cols-[1fr_22rem]">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-display text-3xl font-bold text-ink sm:text-4xl">{{ $business->name }}</h1>
                    @if ($business->isFeatured())
                        <span class="inline-flex items-center gap-1 rounded-full bg-accent-soft px-3 py-1 text-xs font-semibold text-accent-text ring-1 ring-inset ring-accent-line">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="size-3.5" aria-hidden="true"><path d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.02 4.87 5.26.42c.5.04.7.66.32.99l-4.01 3.43 1.22 5.13a.56.56 0 0 1-.84.6L12 16.9l-4.5 2.75a.56.56 0 0 1-.84-.6l1.22-5.13-4.01-3.43a.56.56 0 0 1 .32-.99l5.26-.42 2.02-4.87Z" /></svg>
                            Featured
                        </span>
                    @endif
                </div>
                <p class="mt-2 flex items-center gap-1.5 text-ink-muted">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="size-4 shrink-0 text-ink-subtle" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    {{ $business->category->name }} · {{ $business->city->name }}, {{ $business->city->region }}
                </p>

                <div class="mt-6 max-w-[45rem]">
                    <p class="text-[1.0625rem] leading-relaxed text-ink-muted">{{ $business->description }}</p>
                </div>

                @if ($business->media->isNotEmpty())
                    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($business->media as $photo)
                            <img src="{{ Storage::url($photo->path) }}" alt="{{ $photo->alt }}" loading="lazy"
                                class="aspect-square w-full rounded-xl border border-line object-cover">
                        @endforeach
                    </div>
                @endif

                @if ($business->lat && $business->lng)
                    <div class="mt-8 overflow-hidden rounded-2xl border border-line">
                        <iframe title="Map showing {{ $business->name }}" width="100%" height="320" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://maps.google.com/maps?q={{ $business->lat }},{{ $business->lng }}&z=15&output=embed"></iframe>
                    </div>
                @endif

                @if ($related->isNotEmpty())
                    <div class="mt-14">
                        <h2 class="font-display text-xl font-bold text-ink">More {{ strtolower($business->category->name) }} in {{ $business->city->name }}</h2>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            @foreach ($related as $other)
                                <x-business-card :business="$other" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-5">
                <div class="surface-card p-6">
                    <h2 class="font-display text-base font-bold text-ink">Contact</h2>
                    <dl class="mt-4 space-y-4 text-sm">
                        @if ($business->address)
                            <div class="flex gap-3">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="mt-0.5 size-4.5 shrink-0 text-ink-subtle" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <div><dt class="text-ink-subtle">Address</dt><dd class="mt-0.5 text-ink">{{ $business->address }}</dd></div>
                            </div>
                        @endif
                        @if ($business->phone)
                            <div class="flex gap-3">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="mt-0.5 size-4.5 shrink-0 text-ink-subtle" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.28 6.72 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.37c0-.52-.35-.97-.85-1.09l-4.42-1.11c-.44-.11-.9.06-1.17.42l-.97 1.29c-.28.38-.77.54-1.21.38a12.04 12.04 0 0 1-7.14-7.14c-.16-.44 0-.93.38-1.21l1.29-.97c.36-.27.53-.73.42-1.17L6.96 3.1a1.13 1.13 0 0 0-1.09-.85H4.5A2.25 2.25 0 0 0 2.25 4.5z" />
                                </svg>
                                <div><dt class="text-ink-subtle">Phone</dt><dd class="mt-0.5"><a href="tel:{{ $business->phone }}" class="font-medium text-brand hover:underline">{{ $business->phone }}</a></dd></div>
                            </div>
                        @endif
                        @if ($business->website && $business->plan->allows_website)
                            <div class="flex gap-3">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="mt-0.5 size-4.5 shrink-0 text-ink-subtle" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 9h17M3.5 15h17M12 3a13 13 0 0 1 0 18 13 13 0 0 1 0-18Z" />
                                </svg>
                                <div class="min-w-0"><dt class="text-ink-subtle">Website</dt><dd class="mt-0.5"><a href="{{ $business->website }}" rel="nofollow" class="break-all font-medium text-brand hover:underline">{{ $business->website }}</a></dd></div>
                            </div>
                        @endif
                    </dl>
                </div>

                @if ($business->hours)
                    <div class="surface-card p-6">
                        <h2 class="font-display text-base font-bold text-ink">Opening hours</h2>
                        <dl class="mt-4 space-y-2.5 text-sm">
                            @foreach ($business->hours as $day => $slot)
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-ink-subtle">{{ $day }}</dt>
                                    <dd @class(['tabular-nums', 'text-ink-subtle' => ($slot['closed'] ?? false), 'font-medium text-ink' => ! ($slot['closed'] ?? false)])>{{ ($slot['closed'] ?? false) ? 'Closed' : ($slot['open'] ?? '').' – '.($slot['close'] ?? '') }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                <div class="surface-card p-6">
                    <h2 class="font-display text-base font-bold text-ink">Send an enquiry</h2>
                    @if (session('enquiry_sent'))
                        <div class="mt-4 flex items-start gap-2.5 rounded-xl bg-success-soft p-4 text-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 size-4.5 shrink-0 text-success" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            <p class="font-medium text-success-text">Thanks! Your enquiry has been sent.</p>
                        </div>
                    @else
                        <form method="post" action="{{ route('business.enquire', $business) }}" class="mt-4 space-y-3">
                            @csrf
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
                    <div class="rounded-xl border border-dashed border-line-strong bg-canvas-2 p-5 text-sm">
                        <p class="font-medium text-ink">Is this your business?</p>
                        <a href="{{ route('claim', $business) }}" class="mt-1 inline-flex items-center gap-1 font-semibold text-brand hover:underline">
                            Claim this listing
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    </div>
                @endunless
            </aside>
        </div>
    </div>
</x-public-layout>
