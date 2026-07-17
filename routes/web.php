<?php

use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\ProfileController;
use App\Models\Business;
use Illuminate\Support\Facades\Route;

// Owner portal (auth cookie → Cloudflare bypasses cache)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', \App\Livewire\Portal\Dashboard::class)->name('dashboard');
    Route::get('/listing/edit', \App\Livewire\Portal\EditListing::class)->name('portal.edit');
    Route::get('/listing/photos', \App\Livewire\Portal\Photos::class)->name('portal.photos');
    Route::get('/leads', \App\Livewire\Portal\Leads::class)->name('portal.leads');
    Route::get('/billing', \App\Livewire\Portal\Billing::class)->name('portal.billing');
    Route::get('/admin', \App\Livewire\Admin\Listings::class)->name('admin');

    Route::get('/claim/{business}', function (Business $business) {
        return $business->user_id
            ? redirect()->route('business', $business)
            : view('claim', ['business' => $business]);
    })->name('claim');

    Route::post('/claim/{business}', function (Business $business) {
        abort_if($business->user_id, 403, 'This listing has already been claimed.');
        $business->update(['user_id' => auth()->id()]);

        return redirect()->route('dashboard');
    })->name('claim.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Public directory (anonymous, cacheable). Keep separate from auth routes for the Cloudflare cache boundary.
// cache.headers only touches GET/HEAD, so the enquire POST is unaffected.
Route::middleware('cache.headers:public;max_age=600;etag')->group(function () {
    Route::get('/', [DirectoryController::class, 'home'])->name('home');
    Route::get('/search', [DirectoryController::class, 'search'])->name('search');
    Route::get('/category/{category}', [DirectoryController::class, 'category'])->name('category');
    Route::get('/business/{business}', [DirectoryController::class, 'show'])->name('business');
    Route::post('/business/{business}/enquire', [DirectoryController::class, 'enquire'])->name('business.enquire');

    // City catch-alls last so named routes above win.
    Route::get('/{city}', [DirectoryController::class, 'city'])->name('city');
    Route::get('/{city}/{category}', [DirectoryController::class, 'cityCategory'])->name('city.category');
});
