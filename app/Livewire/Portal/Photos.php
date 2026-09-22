<?php

namespace App\Livewire\Portal;

use App\Models\Business;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        // ponytail: 5 MB cap keeps GD under the default 128M memory_limit; raise both together
        $this->validate([
            'upload' => ['image', 'max:5120'],
        ]);

        if ($this->business->media()->count() >= $this->business->plan->max_photos) {
            $this->addError('upload', "Your {$this->business->plan->name} plan allows up to {$this->business->plan->max_photos} photos. Upgrade for more.");

            return;
        }

        if (! $webp = $this->toWebp($this->upload->getRealPath())) {
            $this->addError('upload', 'That image could not be read. Try a JPG or PNG.');

            return;
        }

        $path = 'photos/'.$this->business->id.'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $webp);

        $this->business->media()->create([
            'path' => $path,
            'alt' => $this->business->name,
            'sort_order' => ($this->business->media()->max('sort_order') ?? 0) + 1,
        ]);

        $this->upload = null;
    }

    // Phone photos are 3-6 MB and often sideways (EXIF orientation); this makes them ~150 KB, upright, WebP.
    private function toWebp(string $file): ?string
    {
        if (! $image = @imagecreatefromstring(file_get_contents($file))) {
            return null;
        }

        $orientation = function_exists('exif_read_data') ? (@exif_read_data($file)['Orientation'] ?? 1) : 1;
        if ($angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0) {
            $image = imagerotate($image, $angle, 0);
        }

        if (imagesx($image) > 1600) {
            $image = imagescale($image, 1600);
        }

        ob_start();
        imagewebp($image, null, 82);

        return ob_get_clean();
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
