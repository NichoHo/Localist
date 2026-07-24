<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-12"
    x-data="{ dirty: false, saved() { this.dirty = false; } }"
    x-on:input="dirty = true"
    x-on:change="dirty = true"
    x-on:saved.window="saved()"
    x-on:beforeunload.window="if (dirty) $event.preventDefault()">

    <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Edit listing</h1>

    @if (! $business)
        <div class="surface-card mt-8 border-dashed p-10 text-center">
            <p class="font-display text-lg font-bold text-ink">No listing to edit</p>
            <a href="{{ route('home') }}" class="mt-3 inline-block font-medium text-brand hover:underline">Claim your listing first</a>
        </div>
    @else
        <p class="mt-1.5 text-sm text-ink-muted">
            Public URL: <span class="font-mono text-ink">/business/{{ $this->slugPreview }}</span>
            @if ($this->slugPreview !== $business->slug)
                <span class="ml-1 rounded-full bg-accent-soft px-2 py-0.5 text-xs font-semibold text-accent-text ring-1 ring-inset ring-accent-line">URL will change, a 301 redirect will be created</span>
            @endif
        </p>

        <form wire:submit="save" class="mt-8 space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-ink">Business name</label>
                <input id="name" wire:model.live.debounce.300ms="name" class="field mt-1.5">
                @error('name') <p class="mt-1.5 text-sm text-danger-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-ink">Description</label>
                <textarea id="description" wire:model="description" rows="5" class="field mt-1.5"></textarea>
                @error('description') <p class="mt-1.5 text-sm text-danger-text">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="category" class="block text-sm font-medium text-ink">Category</label>
                    <select id="category" wire:model="category_id" class="field mt-1.5">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1.5 text-sm text-danger-text">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-ink">City</label>
                    <select id="city" wire:model="city_id" class="field mt-1.5">
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                    @error('city_id') <p class="mt-1.5 text-sm text-danger-text">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-ink">Address</label>
                <input id="address" wire:model="address" class="field mt-1.5">
                @error('address') <p class="mt-1.5 text-sm text-danger-text">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="phone" class="block text-sm font-medium text-ink">Phone</label>
                    <input id="phone" wire:model="phone" class="field mt-1.5">
                    @error('phone') <p class="mt-1.5 text-sm text-danger-text">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-ink">Email</label>
                    <input id="email" wire:model="email" class="field mt-1.5">
                    @error('email') <p class="mt-1.5 text-sm text-danger-text">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="website" class="block text-sm font-medium text-ink">Website</label>
                    <input id="website" wire:model="website" placeholder="https://…" class="field mt-1.5">
                    @error('website') <p class="mt-1.5 text-sm text-danger-text">{{ $message }}</p> @enderror
                </div>
            </div>

            <fieldset>
                <legend class="text-sm font-medium text-ink">Opening hours</legend>
                <div class="mt-3 space-y-2">
                    @foreach (\App\Livewire\Portal\EditListing::DAYS as $day)
                        <div class="flex items-center gap-3" x-data="{ closed: $wire.entangle('hours.{{ $day }}.closed') }">
                            <span class="w-10 text-sm font-medium uppercase text-ink-subtle">{{ $day }}</span>
                            <input type="time" wire:model="hours.{{ $day }}.open" :disabled="closed" class="field w-auto py-2 disabled:bg-sunken disabled:text-ink-subtle">
                            <span class="text-ink-subtle">–</span>
                            <input type="time" wire:model="hours.{{ $day }}.close" :disabled="closed" class="field w-auto py-2 disabled:bg-sunken disabled:text-ink-subtle">
                            <label class="flex items-center gap-1.5 text-sm text-ink-muted">
                                <input type="checkbox" x-model="closed" class="rounded border-line text-brand focus:ring-brand/30"> Closed
                            </label>
                        </div>
                    @endforeach
                </div>
            </fieldset>

            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" class="btn btn-primary">
                    <span wire:loading.remove wire:target="save">Save changes</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
                <span x-show="dirty" x-cloak class="text-sm font-medium text-accent-text">Unsaved changes</span>
                <span x-data="{ show: false }" x-show="show" x-cloak x-transition.opacity
                    x-on:saved.window="show = true; setTimeout(() => show = false, 2500)"
                    class="rounded-lg bg-success-soft px-3 py-1.5 text-sm font-medium text-success-text">Saved</span>
            </div>
        </form>
    @endif
</div>
