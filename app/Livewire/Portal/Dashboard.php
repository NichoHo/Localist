<?php

namespace App\Livewire\Portal;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $business = auth()->user()->business()?->load(['plan', 'category', 'city']);

        return view('livewire.portal.dashboard', [
            'business' => $business,
            'unreadLeads' => $business?->leads()->whereNull('read_at')->count() ?? 0,
        ]);
    }
}
