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
            'totalLeads' => $business?->leads()->count() ?? 0,
            'recentLeads' => $business ? $business->leads()->latest()->limit(4)->get() : collect(),
            'photoCount' => $business?->media()->count() ?? 0,
        ]);
    }
}
