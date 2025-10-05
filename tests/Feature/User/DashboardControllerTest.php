<?php

namespace Tests\Feature\User;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'is_active' => true,
        ]);
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('user.dashboard');
    }

    /** @test */
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get(route('user.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function dashboard_displays_user_licenses()
    {
        // Create licenses for the user
        License::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('licenses');
        $response->assertViewHas('activeCount', 3);
    }

    /** @test */
    public function dashboard_displays_available_products()
    {
        // Create active products
        Product::factory()->count(5)->create(['is_active' => true]);
        Product::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($this->user)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('products');

        $products = $response->viewData('products');
        $this->assertCount(5, $products);
    }
}
