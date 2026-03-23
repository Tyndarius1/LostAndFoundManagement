<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\FoundItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FoundItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_replacing_a_found_item_image_removes_the_old_file(): void
    {
        Storage::fake('public');

        $staff = User::factory()->create([
            'role' => 'staff',
            'email' => 'staff@mlgcl.edu.ph',
        ]);

        $category = Category::create([
            'name' => 'Electronics',
        ]);

        Storage::disk('public')->put('found-items/old-image.jpg', 'old-image');

        $foundItem = FoundItem::create([
            'staff_id' => $staff->id,
            'category_id' => $category->id,
            'item_name' => 'Phone',
            'description' => 'Black phone',
            'color' => 'Black',
            'found_location' => 'Lobby',
            'date_found' => now()->toDateString(),
            'image_path' => 'found-items/old-image.jpg',
            'reference_code' => 'REFCODE1234',
            'storage_location' => 'Cabinet A',
            'status' => 'available',
        ]);

        $response = $this->actingAs($staff, 'sanctum')->patch(
            "/api/found-items/{$foundItem->id}",
            [
                'item_name' => 'Updated Phone',
                'image' => UploadedFile::fake()->image('new-image.jpg'),
            ],
            [
                'Accept' => 'application/json',
            ]
        );

        $response->assertOk()
            ->assertJsonPath('data.item_name', 'Updated Phone');

        Storage::disk('public')->assertMissing('found-items/old-image.jpg');

        $foundItem->refresh();
        $this->assertNotSame('found-items/old-image.jpg', $foundItem->image_path);
        Storage::disk('public')->assertExists($foundItem->image_path);
    }
}
