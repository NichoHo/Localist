<?php

namespace App\Livewire\Portal;

use App\Models\Business;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Photos extends Component
{
    use WithFileUploads;

    public ?Business $business = null;

    public $upload;

    public function mount(): void
    {
        $this->business = auth()->user()->business();
    }

    public function updatedUpload(): void
    {
        $this->validate([
            'upload' => ['image', 'max:2048'],
        ]);

        if ($this->business->media()->count() >= $this->business->plan->max_photos) {
            $this->addError('upload', "Your {$this->business->plan->name} plan allows up to {$this->business->plan->max_photos} photos. Upgrade for more.");

            return;
        }

        $path = $this->upload->store('photos/'.$this->business->id, 'public');

        $this->business->media()->create([
            'path' => $path,
            'alt' => $this->business->name,
            'sort_order' => ($this->business->media()->max('sort_order') ?? 0) + 1,
        ]);

        $this->upload = null;
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $this->business->media()->where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    public function delete(int $mediaId): void
    {
        $media = $this->business->media()->findOrFail($mediaId);
        Storage::disk('public')->delete($media->path);
        $media->delete();
    }

    public function render()
    {
        return view('livewire.portal.photos', [
            'photos' => $this->business?->media()->get() ?? collect(),
        ]);
    }
}
