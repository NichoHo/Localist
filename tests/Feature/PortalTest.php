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

function fakeImage(string $name, int $width = 10): UploadedFile
{
    return UploadedFile::fake()->image($name, $width, 10);
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
        $category = Category::create(['name' => 'Cafes & Coffee Shops', 'slug' => 'cafes-coffee']);
        $city = City::create(['name' => 'Jakarta', 'slug' => 'jakarta', 'region' => 'DKI Jakarta']);
        $this->owner = User::factory()->create();
        $this->business = Business::create([
            'name' => 'Sunrise Coffee', 'slug' => 'sunrise-coffee', 'description' => 'Fresh coffee, brewed fast.',
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
            ->set('description', 'Now with weekend brunch hours.')
            ->set('phone', '0812-3456-7890')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('saved');

        $this->assertEquals('Now with weekend brunch hours.', $this->business->fresh()->description);
    }

    public function test_slug_change_creates_working_301(): void
    {
        $this->business->update(['user_id' => $this->owner->id]);

        Livewire::actingAs($this->owner)->test(EditListing::class)
            ->set('name', 'Sunrise Coffee & Roasters')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('sunrise-coffee-roasters', $this->business->fresh()->slug);
        $this->assertDatabaseHas('redirects', ['from_path' => '/business/sunrise-coffee']);

        // The spec's one correctness check: old path → 301 → new path, never 404.
        $this->get('/business/sunrise-coffee')
            ->assertStatus(301)
            ->assertRedirect('/business/sunrise-coffee-roasters');
        $this->get('/business/sunrise-coffee-roasters')->assertOk();
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

    public function test_photo_upload_is_resized_and_stored_as_webp(): void
    {
        Storage::fake('public');
        $this->business->update(['user_id' => $this->owner->id]);

        Livewire::actingAs($this->owner)->test(Photos::class)
            ->set('upload', fakeImage('wide.jpg', 3000))
            ->assertHasNoErrors();

        $path = $this->business->media()->value('path');
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);

        [$width] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertEquals(1600, $width);
    }

    public function test_photo_upload_rejects_undecodable_image(): void
    {
        Storage::fake('public');
        $this->business->update(['user_id' => $this->owner->id]);

        // Valid PNG signature, garbage body: passes MIME sniffing, fails GD decoding.
        $bytes = "\x89PNG\r\n\x1a\n".str_repeat('x', 64);
        Livewire::actingAs($this->owner)->test(Photos::class)
            ->set('upload', UploadedFile::fake()->createWithContent('bad.png', $bytes))
            ->assertHasErrors('upload');

        $this->assertEquals(0, $this->business->media()->count());
    }

    public function test_photo_upload_rejects_svg(): void
    {
        Storage::fake('public');
        $this->business->update(['user_id' => $this->owner->id]);

        Livewire::actingAs($this->owner)->test(Photos::class)
            ->set('upload', UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'))
            ->assertHasErrors('upload');

        $this->assertEquals(0, $this->business->media()->count());
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
