<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    public function home()
    {
        return view('directory.home', [
            'categories' => Category::withCount('businesses')->orderBy('name')->get(),
            'featured' => Business::published()->ranked()
                ->whereHas('plan', fn ($q) => $q->where('priority_rank', '>', 0))
                ->with(['category', 'city', 'plan'])->inRandomOrder()->limit(6)->get(),
            'cities' => City::withCount('businesses')->orderByDesc('businesses_count')->limit(12)->get(),
            'cityCount' => City::count(),
        ]);
    }

    public function category(Category $category, Request $request)
    {
        $businesses = $category->businesses()->published()->ranked()
            ->when($request->query('city'), fn ($q, $slug) => $q->whereHas('city', fn ($c) => $c->where('slug', $slug)))
            ->with(['city', 'plan'])->paginate(24)->withQueryString();

        return view('directory.category', [
            'category' => $category,
            'businesses' => $businesses,
            'cities' => City::whereHas('businesses', fn ($q) => $q->where('category_id', $category->id))->orderBy('name')->get(),
        ]);
    }

    public function city(City $city)
    {
        return view('directory.city', [
            'city' => $city,
            'categories' => Category::whereHas('businesses', fn ($q) => $q->where('city_id', $city->id))
                ->withCount(['businesses' => fn ($q) => $q->where('city_id', $city->id)])
                ->orderBy('name')->get(),
            'featured' => $city->businesses()->published()->ranked()
                ->whereHas('plan', fn ($q) => $q->where('priority_rank', '>', 0))
                ->with(['category', 'plan'])->limit(6)->get(),
        ]);
    }

    public function cityCategory(City $city, Category $category)
    {
        $businesses = Business::published()->ranked()
            ->where('businesses.city_id', $city->id)
            ->where('businesses.category_id', $category->id)
            ->with(['category', 'city', 'plan'])
            ->paginate(24);

        abort_if($businesses->isEmpty() && $businesses->currentPage() === 1, 404);

        return view('directory.city-category', [
            'city' => $city,
            'category' => $category,
            'businesses' => $businesses,
            'nearbyCities' => City::where('id', '!=', $city->id)->where('region', $city->region)
                ->whereHas('businesses', fn ($q) => $q->where('category_id', $category->id))->orderBy('name')->limit(8)->get(),
            'relatedCategories' => Category::where('id', '!=', $category->id)
                ->whereHas('businesses', fn ($q) => $q->where('city_id', $city->id))->orderBy('name')->limit(8)->get(),
        ]);
    }

    public function show(Business $business)
    {
        abort_unless($business->status === 'published', 404);
        $business->increment('views_count');
        $business->load(['category', 'city', 'plan', 'media']);

        return view('directory.business', [
            'business' => $business,
            'related' => Business::published()->ranked()
                ->where('businesses.city_id', $business->city_id)
                ->where('businesses.category_id', $business->category_id)
                ->where('businesses.id', '!=', $business->id)
                ->with(['category', 'city', 'plan'])->limit(4)->get(),
        ]);
    }

    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));

        $businesses = Business::published()->ranked()
            ->when($q, fn ($query) => $query->where(fn ($w) => $w
                ->where('businesses.name', 'like', "%$q%")
                ->orWhere('businesses.description', 'like', "%$q%")))
            ->with(['category', 'city', 'plan'])
            ->paginate(24)->withQueryString();

        return view('directory.search', ['q' => $q, 'businesses' => $businesses]);
    }

    public function enquire(Business $business, Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $business->leads()->create($data);

        return back()->with('enquiry_sent', true);
    }
}
