<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold">Photos</h1>

    @if (! $business)
        <div class="mt-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center">
            <p class="text-lg font-semibold">No listing yet</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block font-medium text-teal-700 dark:text-teal-400 hover:underline">Claim your listing first</a>
        </div>
    @else
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $photos->count() }} of {{ $business->plan->max_photos }} photos ({{ $business->plan->name }} plan) · drag to reorder
        </p>

        <div class="mt-6">
            <label class="inline-block cursor-pointer rounded-md bg-teal-700 px-4 py-2 font-semibold text-white hover:bg-teal-800">
                <span wire:loading.remove wire:target="upload">Upload photo</span>
                <span wire:loading wire:target="upload">Uploading…</span>
                <input type="file" wire:model="upload" accept="image/*" class="hidden">
            </label>
            @error('upload') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        @if ($photos->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center text-gray-500 dark:text-gray-400">
                No photos yet. Listings with photos get more enquiries.
            </div>
        @else
            <ul class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3"
                x-data="{
                    dragging: null,
                    drop(target) {
                        if (this.dragging === null || this.dragging === target) return;
                        const ids = [...$el.querySelectorAll('[data-id]')].map(li => li.dataset.id);
                        ids.splice(ids.indexOf(String(target)), 0, ...ids.splice(ids.indexOf(String(this.dragging)), 1));
                        $wire.reorder(ids);
                        this.dragging = null;
                    }
                }">
                @foreach ($photos as $photo)
                    <li wire:key="photo-{{ $photo->id }}" data-id="{{ $photo->id }}" draggable="true"
                        x-on:dragstart="dragging = {{ $photo->id }}"
                        x-on:dragover.prevent
                        x-on:drop.prevent="drop({{ $photo->id }})"
                        class="group relative cursor-move overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                        <img src="{{ Storage::url($photo->path) }}" alt="{{ $photo->alt }}" class="aspect-square w-full object-cover">
                        <button wire:click="delete({{ $photo->id }})" wire:confirm="Delete this photo?"
                            class="absolute right-2 top-2 hidden rounded-md bg-red-600 px-2 py-1 text-xs font-semibold text-white group-hover:block">
                            Delete
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
