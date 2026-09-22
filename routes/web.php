<?php

use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Admin\Listings;
use App\Livewire\Portal\Billing;
use App\Livewire\Portal\Dashboard;
use App\Livewire\Portal\EditListing;
use App\Livewire\Portal\Leads;
use App\Livewire\Portal\Photos;
use App\Models\Business;
use Illuminate\Support\Facades\Route;

// Owner portal (auth cookie → Cloudflare bypasses cache)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/listing/edit', EditListing::class)->name('portal.edit');
    Route::get('/listing/photos', Photos::class)->name('portal.photos');
    Route::get('/leads', Leads::class)->name('portal.leads');
    Route::get('/billing', Billing::class)->name('portal.billing');
    Route::get('/admin', Listings::class)->name('admin');

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
    Route::post('/business/{business}/enquire', [DirectoryController::class, 'enquire'])->middleware('throttle:5,1')->name('business.enquire');
    // View beacon: cached pages never reach origin, so the page's JS posts here instead.
    Route::post('/business/{business}/view', [DirectoryController::class, 'view'])->middleware('throttle:60,1')->name('business.view');

    // City catch-alls last so named routes above win.
    Route::get('/{city}', [DirectoryController::class, 'city'])->name('city');
    Route::get('/{city}/{category}', [DirectoryController::class, 'cityCategory'])->name('city.category');
});
