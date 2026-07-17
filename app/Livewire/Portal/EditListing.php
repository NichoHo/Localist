<?php

namespace App\Livewire\Portal;

use App\Jobs\PurgeCloudflare;
use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Redirect;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class EditListing extends Component
{
    public ?Business $business = null;

    public string $name = '';
    public string $description = '';
    public string $category_id = '';
    public string $city_id = '';
    public string $address = '';
    public string $phone = '';
    public string $website = '';
    public string $email = '';
    public array $hours = [];

    public const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function mount(): void
    {
        $this->business = auth()->user()->business();
        if (! $this->business) {
            return;
        }

        $this->fill(array_map(fn ($v) => $v ?? '',
            $this->business->only('name', 'description', 'address', 'phone', 'website', 'email')));
        $this->category_id = (string) $this->business->category_id;
        $this->city_id = (string) $this->business->city_id;

        foreach (self::DAYS as $day) {
            $this->hours[$day] = $this->business->hours[$day]
                ?? ['open' => '09:00', 'close' => '18:00', 'closed' => false];
        }
    }

    #[Computed]
    public function slugPreview(): string
    {
        return Str::slug($this->name);
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'exists:categories,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'hours' => ['array'],
            'hours.*.open' => ['nullable', 'date_format:H:i'],
            'hours.*.close' => ['nullable', 'date_format:H:i'],
            'hours.*.closed' => ['boolean'],
        ]);

        $newSlug = Str::slug($data['name']);
        if ($newSlug !== $this->business->slug && Business::where('slug', $newSlug)->exists()) {
            $this->addError('name', 'Another listing already uses this name/URL.');

            return;
        }

        if ($newSlug !== $this->business->slug) {
            Redirect::updateOrCreate(
                ['from_path' => '/business/'.$this->business->slug],
                ['to_path' => '/business/'.$newSlug, 'status_code' => 301]
            );
        }

        $oldUrl = route('business', $this->business->slug);
        $this->business->update([...$data, 'slug' => $newSlug]);

        dispatch(PurgeCloudflare::forBusiness($this->business->fresh(), $oldUrl));

        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.portal.edit-listing', [
            'categories' => Category::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
        ]);
    }
}
