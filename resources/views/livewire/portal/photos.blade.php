<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-12">
    <h1 class="font-display text-2xl font-bold text-ink sm:text-3xl">Photos</h1>

    @if (! $business)
        <div class="surface-card mt-8 border-dashed p-10 text-center">
            <p class="font-display text-lg font-bold text-ink">No listing yet</p>
            <a href="{{ route('home') }}" class="mt-3 inline-block font-medium text-brand hover:underline">Claim your listing first</a>
        </div>
    @else
        <p class="mt-1.5 text-sm text-ink-muted">
            {{ $photos->count() }} of {{ $business->plan->max_photos }} photos ({{ $business->plan->name }} plan) · drag to reorder
        </p>

        <div class="mt-6">
            <label class="btn btn-primary cursor-pointer">
                <span wire:loading.remove wire:target="upload">Upload photo</span>
                <span wire:loading wire:target="upload">Uploading…</span>
                <input type="file" wire:model="upload" accept="image/*" class="hidden">
            </label>
            @error('upload') <p class="mt-2 text-sm text-danger-text">{{ $message }}</p> @enderror
        </div>

        @if ($photos->isEmpty())
            <div class="surface-card mt-8 border-dashed p-10 text-center text-ink-muted">
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
                        class="group relative cursor-move overflow-hidden rounded-xl border border-line">
                        <img src="{{ Storage::url($photo->path) }}" alt="{{ $photo->alt }}" class="aspect-square w-full object-cover">
                        <button wire:click="delete({{ $photo->id }})" wire:confirm="Delete this photo?"
                            class="absolute right-2 top-2 hidden rounded-lg bg-danger px-2 py-1 text-xs font-semibold text-white shadow-card group-hover:block">
                            Delete
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
