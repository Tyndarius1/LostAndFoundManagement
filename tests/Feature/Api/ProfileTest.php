<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_update_their_profile_name_and_email(): void
    {
        $user = User::factory()->create([
            'email' => 'old@mlgcl.edu.ph'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', [
                'name' => 'New Name',
                'email' => 'new@mlgcl.edu.ph'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@mlgcl.edu.ph'
        ]);
    }

    public function test_user_cannot_update_email_to_invalid_domain(): void
    {
        $user = User::factory()->create([
            'email' => 'user@mlgcl.edu.ph'
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', [
                'email' => 'user@gmail.com'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('old-password')
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password'
            ]);

        $response->assertStatus(200);
        $this->assertTrue(\Hash::check('new-password', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('old-password')
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_user_can_upload_profile_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', [
                'profile_image' => $file
            ]);

        $response->assertStatus(200);
        
        $user->fresh();
        $this->assertNotNull($user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }
}
