<?php

namespace App\Livewire\Portal;

use App\Models\Business;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Leads extends Component
{
    public ?Business $business = null;

    public function mount(): void
    {
        $this->business = auth()->user()->business();
    }

    public function toggleRead(int $leadId): void
    {
        $lead = $this->business->leads()->findOrFail($leadId);
        $lead->update(['read_at' => $lead->read_at ? null : now()]);
    }

    public function render()
    {
        return view('livewire.portal.leads', [
            'leads' => $this->business?->leads()->latest()->get() ?? collect(),
            'unread' => $this->business?->leads()->whereNull('read_at')->count() ?? 0,
        ]);
    }
}
