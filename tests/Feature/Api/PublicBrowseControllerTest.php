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
}
