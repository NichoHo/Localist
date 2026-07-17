<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate {--max=50000 : Max URLs per sitemap file} {--path= : Output directory (defaults to public/)}';

    protected $description = 'Write sitemap index + per-section sitemaps to public/, chunked at 50k URLs.';

    public function handle(): int
    {
        $sections = [
            'businesses' => Business::published()->pluck('slug')->map(fn ($slug) => route('business', $slug)),
            'categories' => Category::pluck('slug')->map(fn ($slug) => route('category', $slug)),
            'cities' => City::pluck('slug')->map(fn ($slug) => route('city', $slug))->concat(
                DB::table('businesses')
                    ->join('cities', 'cities.id', '=', 'businesses.city_id')
                    ->join('categories', 'categories.id', '=', 'businesses.category_id')
                    ->where('businesses.status', 'published')
                    ->distinct()->get(['cities.slug as city', 'categories.slug as category'])
                    ->map(fn ($row) => url($row->city.'/'.$row->category))
            ),
        ];

        $dir = rtrim($this->option('path') ?: public_path(), '/\\');
        $files = [];
        foreach ($sections as $name => $urls) {
            foreach ($urls->chunk((int) $this->option('max')) as $i => $chunk) {
                $file = 'sitemap-'.$name.($i > 0 ? '-'.($i + 1) : '').'.xml';
                file_put_contents($dir.DIRECTORY_SEPARATOR.$file, view('sitemap.urlset', ['urls' => $chunk])->render());
                $files[] = $file;
                $this->info("$file (".$chunk->count().' URLs)');
            }
        }

        file_put_contents($dir.DIRECTORY_SEPARATOR.'sitemap.xml', view('sitemap.index', ['files' => $files])->render());
        $this->info('sitemap.xml (index, '.count($files).' sitemaps)');

        return self::SUCCESS;
    }
}
