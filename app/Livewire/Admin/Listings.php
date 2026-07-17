<?php

namespace App\Livewire\Admin;

use App\Jobs\PurgeCloudflare;
use App\Models\Business;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Listings extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403);
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function approve(int $businessId): void
    {
        $this->setBusinessStatus($businessId, 'published');
    }

    public function unpublish(int $businessId): void
    {
        $this->setBusinessStatus($businessId, 'pending');
    }

    private function setBusinessStatus(int $businessId, string $status): void
    {
        $business = Business::findOrFail($businessId);
        $business->update(['status' => $status]);
        dispatch(PurgeCloudflare::forBusiness($business));
    }

    public function render()
    {
        return view('livewire.admin.listings', [
            'businesses' => Business::with(['category', 'city', 'plan', 'user'])
                ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->latest('updated_at')
                ->paginate(20),
            'counts' => Business::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
