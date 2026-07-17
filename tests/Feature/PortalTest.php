<?php

namespace Tests\Feature;

use App\Livewire\Portal\EditListing;
use App\Livewire\Portal\Leads;
use App\Livewire\Portal\Photos;
use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

// 1x1 transparent PNG so image validation passes without the GD extension.
const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

function fakeImage(string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, base64_decode(TINY_PNG));
}

class PortalTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create(['name' => 'Free', 'priority_rank' => 0, 'max_photos' => 3]);
        $category = Category::create(['name' => 'Plumbers', 'slug' => 'plumbers']);
        $city = City::create(['name' => 'Kuala Lumpur', 'slug' => 'kuala-lumpur', 'region' => 'Federal Territory']);
        $this->owner = User::factory()->create();
        $this->business = Business::create([
            'name' => 'Rapid Plumbing', 'slug' => 'rapid-plumbing', 'description' => 'Pipes fixed fast.',
            'category_id' => $category->id, 'city_id' => $city->id, 'plan_id' => $plan->id, 'status' => 'published',
        ]);
    }

    public function test_owner_can_claim_an_unclaimed_listing(): void
    {
        $this->actingAs($this->owner)
            ->post("/claim/{$this->business->slug}")
            ->assertRedirect(route('dashboard'));

        $this->assertEquals($this->owner->id, $this->business->fresh()->user_id);
    }

    public function test_claimed_listing_cannot_be_claimed_again(): void
    {
        $this->business->update(['user_id' => $this->owner->id]);
        $other = User::factory()->create();

        $this->actingAs($other)->post("/claim/{$this->business->slug}")->assertForbidden();
        $this->assertEquals($this->owner->id, $this->business->fresh()->user_id);
    }

    public function test_owner_can_edit_listing(): void
    {
        $this->business->update(['user_id' => $this->owner->id]);

        Livewire::actingAs($this->owner)->test(EditListing::class)
            ->set('description', 'Now with 24/7 emergency callouts.')
            ->set('phone', '012-345 6789')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('saved');

        $this->assertEquals('Now with 24/7 emergency callouts.', $this->business->fresh()->description);
    }

    public function test_slug_change_creates_working_301(): void
    {
        $this->business->update(['user_id' => $this->owner->id]);

        Livewire::actingAs($this->owner)->test(EditListing::class)
            ->set('name', 'Rapid Plumbing & Sons')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('rapid-plumbing-sons', $this->business->fresh()->slug);
        $this->assertDatabaseHas('redirects', ['from_path' => '/business/rapid-plumbing']);

        // The spec's one correctness check: old path → 301 → new path, never 404.
        $this->get('/business/rapid-plumbing')
            ->assertStatus(301)
            ->assertRedirect('/business/rapid-plumbing-sons');
        $this->get('/business/rapid-plumbing-sons')->assertOk();
    }

    public function test_photo_upload_respects_plan_limit(): void
    {
        Storage::fake('public');
        $this->business->update(['user_id' => $this->owner->id]);
        foreach (range(1, 3) as $i) {
            $this->business->media()->create(['path' => "photos/fake-$i.jpg", 'sort_order' => $i]);
        }

        Livewire::actingAs($this->owner)->test(Photos::class)
            ->set('upload', fakeImage('extra.png'))
            ->assertHasErrors('upload');

        $this->assertEquals(3, $this->business->media()->count());
    }

    public function test_photo_upload_and_reorder(): void
    {
        Storage::fake('public');
        $this->business->update(['user_id' => $this->owner->id]);

        $component = Livewire::actingAs($this->owner)->test(Photos::class)
            ->set('upload', fakeImage('one.png'))
            ->assertHasNoErrors()
            ->set('upload', fakeImage('two.png'))
            ->assertHasNoErrors();

        [$first, $second] = $this->business->media()->pluck('id');
        $component->call('reorder', ["$second", "$first"]);

        $this->assertEquals([$second, $first], $this->business->media()->pluck('id')->all());
    }

    public function test_leads_can_be_toggled_read(): void
    {
        $this->business->update(['user_id' => $this->owner->id]);
        $lead = $this->business->leads()->create([
            'name' => 'Visitor', 'email' => 'v@example.com', 'message' => 'Quote please',
        ]);

        Livewire::actingAs($this->owner)->test(Leads::class)
            ->assertSee('1 unread')
            ->call('toggleRead', $lead->id)
            ->assertDontSee('1 unread');

        $this->assertNotNull($lead->fresh()->read_at);
    }

    public function test_portal_requires_auth(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/listing/edit')->assertRedirect('/login');
    }
}
