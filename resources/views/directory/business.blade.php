<x-public-layout :title="$business->name.' - '.$business->category->name.' in '.$business->city->name.' | Localist'" :description="Str::limit($business->description, 155)">
    <x-json-ld :data="\App\Services\Seo::localBusiness($business)" />
    <x-json-ld :data="\App\Services\Seo::breadcrumbs([
        ['Home', route('home')],
        [$business->city->name, route('city', $business->city)],
        [$business->category->name, route('city.category', [$business->city, $business->category])],
        [$business->name, route('business', $business)],
    ])" />
    <div class="mx-auto max-w-[75rem] px-4 py-10">
        <nav class="text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('home') }}" class="hover:text-teal-700 dark:hover:text-teal-400">Home</a> /
            <a href="{{ route('city', $business->city) }}" class="hover:text-teal-700 dark:hover:text-teal-400">{{ $business->city->name }}</a> /
            <a href="{{ route('city.category', [$business->city, $business->category]) }}" class="hover:text-teal-700 dark:hover:text-teal-400">{{ $business->category->name }}</a> /
            {{ $business->name }}
        </nav>

        <div class="mt-6 grid gap-10 lg:grid-cols-[1fr_20rem]">
            <div class="max-w-[45rem]">
                <div class="flex items-start gap-3">
                    <h1 class="text-3xl font-bold">{{ $business->name }}</h1>
                    @if ($business->isFeatured())
                        <span class="mt-2 shrink-0 rounded-full bg-amber-100 dark:bg-amber-400/10 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300">Featured</span>
                    @endif
                </div>
                <p class="mt-2 text-gray-500 dark:text-gray-400">{{ $business->category->name }} · {{ $business->city->name }}, {{ $business->city->region }}</p>

                <div class="prose prose-gray mt-6 max-w-none">
                    <p>{{ $business->description }}</p>
                </div>

                @if ($business->media->isNotEmpty())
                    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($business->media as $photo)
                            <img src="{{ Storage::url($photo->path) }}" alt="{{ $photo->alt }}" loading="lazy"
                                class="aspect-square w-full rounded-lg border border-gray-200 dark:border-gray-800 object-cover">
                        @endforeach
                    </div>
                @endif

                @if ($business->lat && $business->lng)
                    <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                        <iframe title="Map showing {{ $business->name }}" width="100%" height="320" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://maps.google.com/maps?q={{ $business->lat }},{{ $business->lng }}&z=15&output=embed"></iframe>
                    </div>
                @endif

                @if ($related->isNotEmpty())
                    <h2 class="mt-12 text-xl font-semibold">More {{ strtolower($business->category->name) }} in {{ $business->city->name }}</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($related as $other)
                            <x-business-card :business="$other" />
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-5">
                    <h2 class="font-semibold">Contact</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        @if ($business->address)
                            <div><dt class="text-gray-500 dark:text-gray-400">Address</dt><dd>{{ $business->address }}</dd></div>
                        @endif
                        @if ($business->phone)
                            <div><dt class="text-gray-500 dark:text-gray-400">Phone</dt><dd><a href="tel:{{ $business->phone }}" class="text-teal-700 dark:text-teal-400 hover:underline">{{ $business->phone }}</a></dd></div>
                        @endif
                        @if ($business->website && $business->plan->allows_website)
                            <div><dt class="text-gray-500 dark:text-gray-400">Website</dt><dd><a href="{{ $business->website }}" rel="nofollow" class="break-all text-teal-700 dark:text-teal-400 hover:underline">{{ $business->website }}</a></dd></div>
                        @endif
                    </dl>
                </div>

                @if ($business->hours)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-5">
                        <h2 class="font-semibold">Opening hours</h2>
                        <dl class="mt-3 space-y-1 text-sm">
                            @foreach ($business->hours as $day => $slot)
                                <div class="flex justify-between">
                                    <dt class="uppercase text-gray-500 dark:text-gray-400">{{ $day }}</dt>
                                    <dd class="tabular-nums">{{ ($slot['closed'] ?? false) ? 'Closed' : ($slot['open'] ?? '').' – '.($slot['close'] ?? '') }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-5">
                    <h2 class="font-semibold">Send an enquiry</h2>
                    @if (session('enquiry_sent'))
                        <p class="mt-3 rounded-md bg-green-50 dark:bg-green-900/30 p-3 text-sm font-medium text-green-700 dark:text-green-300">Thanks! Your enquiry has been sent.</p>
                    @else
                        <form method="post" action="{{ route('business.enquire', $business) }}" class="mt-3 space-y-3">
                            @csrf
                            <input name="name" value="{{ old('name') }}" placeholder="Your name" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 text-sm focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                            <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 text-sm focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                            <input name="phone" value="{{ old('phone') }}" placeholder="Phone (optional)"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 text-sm focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                            <textarea name="message" rows="4" placeholder="What do you need?" required
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 text-sm focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">{{ old('message') }}</textarea>
                            @if ($errors->any())
                                <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
                            @endif
                            <button class="w-full rounded-md bg-teal-700 px-4 py-2.5 font-semibold text-white hover:bg-teal-800">Send enquiry</button>
                        </form>
                    @endif
                </div>

                @unless ($business->user_id)
                    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-5 text-sm text-gray-500 dark:text-gray-400">
                        Is this your business?
                        <a href="{{ route('claim', $business) }}" class="font-medium text-teal-700 dark:text-teal-400 hover:underline">Claim this listing</a>
                    </div>
                @endunless
            </aside>
        </div>
    </div>
</x-public-layout>
