<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBrowseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_lost_items_per_page_is_capped_to_prevent_oversized_queries(): void
    {
        $category = Category::create([
            'name' => 'Documents',
        ]);

        $user = User::factory()->create([
            'email' => 'owner@mlgcl.edu.ph',
        ]);

        foreach (range(1, 55) as $index) {
            LostItem::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'item_name' => "Document {$index}",
                'description' => 'Lost document',
                'color' => 'White',
                'last_seen_location' => 'Registrar',
                'date_lost' => now()->subDays($index)->toDateString(),
                'status' => 'pending',
            ]);
        }

        $response = $this->getJson('/api/public/lost-items?per_page=999');

        $response->assertOk()
            ->assertJsonPath('data.pagination.per_page', 50)
            ->assertJsonCount(50, 'data.items');
    }

    public function test_public_found_items_per_page_is_capped_to_prevent_oversized_queries(): void
    {
        $category = Category::create([
            'name' => 'Electronics',
        ]);

        $staff = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff@mlgcl.edu.ph',
        ]);

        foreach (range(1, 55) as $index) {
            \App\Models\FoundItem::create([
                'staff_id' => $staff->id,
                'category_id' => $category->id,
                'item_name' => "Phone {$index}",
                'description' => 'Black phone',
                'color' => 'Black',
                'found_location' => 'Lobby',
                'date_found' => now()->subDays($index)->toDateString(),
                'image_path' => null,
                'qr_code_path' => null,
                'reference_code' => "REFCODE{$index}",
                'storage_location' => 'Cabinet A',
                'status' => 'available',
            ]);
        }

        $response = $this->getJson('/api/public/found-items?per_page=999');

        $response->assertOk()
            ->assertJsonPath('data.pagination.per_page', 50)
            ->assertJsonCount(50, 'data.items');
    }

    public function test_public_categories_endpoint_returns_categories(): void
    {
        Category::create([
            'name' => 'Documents',
        ]);
        Category::create([
            'name' => 'Electronics',
        ]);

        $response = $this->getJson('/api/public/categories');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_lookup_categories(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'email' => 'user@mlgcl.edu.ph',
        ]);

        Category::create([
            'name' => 'Documents',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/lookup/categories');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Documents']);
    }
}
