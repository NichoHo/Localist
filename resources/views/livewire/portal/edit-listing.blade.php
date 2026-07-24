<div class="mx-auto max-w-3xl px-4 py-10"
    x-data="{ dirty: false, saved() { this.dirty = false; } }"
    x-on:input="dirty = true"
    x-on:change="dirty = true"
    x-on:saved.window="saved()"
    x-on:beforeunload.window="if (dirty) $event.preventDefault()">

    <h1 class="text-2xl font-bold">Edit listing</h1>

    @if (! $business)
        <div class="mt-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center">
            <p class="text-lg font-semibold">No listing to edit</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block font-medium text-teal-700 dark:text-teal-400 hover:underline">Claim your listing first</a>
        </div>
    @else
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Public URL: <span class="font-mono">/business/{{ $this->slugPreview }}</span>
            @if ($this->slugPreview !== $business->slug)
                <span class="ml-1 rounded-full bg-amber-100 dark:bg-amber-400/10 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300">URL will change, a 301 redirect will be created</span>
            @endif
        </p>

        <form wire:submit="save" class="mt-8 space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium">Business name</label>
                <input id="name" wire:model.live.debounce.300ms="name"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium">Description</label>
                <textarea id="description" wire:model="description" rows="5"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500"></textarea>
                @error('description') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="category" class="block text-sm font-medium">Category</label>
                    <select id="category" wire:model="category_id"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium">City</label>
                    <select id="city" wire:model="city_id"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                    @error('city_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium">Address</label>
                <input id="address" wire:model="address"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                @error('address') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="phone" class="block text-sm font-medium">Phone</label>
                    <input id="phone" wire:model="phone"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                    @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium">Email</label>
                    <input id="email" wire:model="email"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                    @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="website" class="block text-sm font-medium">Website</label>
                    <input id="website" wire:model="website" placeholder="https://…"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 focus:border-teal-600 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                    @error('website') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <fieldset>
                <legend class="text-sm font-medium">Opening hours</legend>
                <div class="mt-2 space-y-2">
                    @foreach (\App\Livewire\Portal\EditListing::DAYS as $day)
                        <div class="flex items-center gap-3" x-data="{ closed: $wire.entangle('hours.{{ $day }}.closed') }">
                            <span class="w-10 text-sm font-medium uppercase text-gray-500 dark:text-gray-400">{{ $day }}</span>
                            <input type="time" wire:model="hours.{{ $day }}.open" :disabled="closed"
                                class="rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600 disabled:bg-gray-100 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-600">
                            <span class="text-gray-400">–</span>
                            <input type="time" wire:model="hours.{{ $day }}.close" :disabled="closed"
                                class="rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600 disabled:bg-gray-100 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:disabled:bg-gray-800 dark:disabled:text-gray-600">
                            <label class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-300">
                                <input type="checkbox" x-model="closed"
                                    class="rounded border-gray-300 dark:border-gray-700 text-teal-700 dark:text-teal-400 focus:ring-teal-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500"> Closed
                            </label>
                        </div>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="rounded-md bg-teal-700 px-5 py-2.5 font-semibold text-white hover:bg-teal-800">
                    <span wire:loading.remove wire:target="save">Save changes</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
                <span x-show="dirty" x-cloak class="text-sm font-medium text-amber-600 dark:text-amber-400">Unsaved changes</span>
                <span x-data="{ show: false }" x-show="show" x-cloak x-transition.opacity
                    x-on:saved.window="show = true; setTimeout(() => show = false, 2500)"
                    class="rounded-md bg-green-50 dark:bg-green-900/30 px-3 py-1.5 text-sm font-medium text-green-700 dark:text-green-300">Saved ✓</span>
            </div>
        </form>
    @endif
</div>
