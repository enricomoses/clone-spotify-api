<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Modern Laravel 11+ migrations create 'users' table, but the User model expects 'User'.
        // To run tests successfully on SQLite memory, we can create the 'User' table structure manually
        // if needed, or check if the tables are mapped. Let's run a raw schema build if necessary,
        // or rename the table dynamically for testing if needed.
        // Let's create the 'User' table in SQLite memory database directly.
        \Illuminate\Support\Facades\Schema::create('User', function ($table) {
            $table->string('id_user')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->date('birthday')->nullable();
            $table->string('gender')->nullable();
            $table->string('country')->nullable();
            $table->string('profile_picture')->nullable();
        });
    }

    public function test_user_can_update_profile_details(): void
    {
        $user = User::create([
            'id_user' => (string) Str::uuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'birthday' => '1995-05-15',
            'gender' => 'Male',
            'country' => 'Indonesia',
            'profile_picture' => 'http://example.com/old.jpg',
        ]);

        $response = $this->putJson("/api/users/{$user->id_user}", [
            'name' => 'John Updated',
            'birthday' => '1996-06-16',
            'gender' => 'Other',
            'country' => 'Singapore',
            'profile_picture' => 'http://example.com/new.jpg',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'John Updated')
                 ->assertJsonPath('data.gender', 'Other')
                 ->assertJsonPath('data.country', 'Singapore')
                 ->assertJsonPath('data.profile_picture', 'http://example.com/new.jpg');

        $this->assertDatabaseHas('User', [
            'id_user' => $user->id_user,
            'name' => 'John Updated',
            'birthday' => '1996-06-16',
            'gender' => 'Other',
            'country' => 'Singapore',
            'profile_picture' => 'http://example.com/new.jpg',
        ]);
    }

    public function test_user_cannot_update_email(): void
    {
        $user = User::create([
            'id_user' => (string) Str::uuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->putJson("/api/users/{$user->id_user}", [
            'email' => 'john_new@example.com',
            'name' => 'John Updated',
        ]);

        $response->assertStatus(200);

        // Ensure email remains unchanged
        $this->assertEquals('john@example.com', $user->fresh()->email);
        $this->assertDatabaseHas('User', [
            'id_user' => $user->id_user,
            'email' => 'john@example.com',
        ]);
        $this->assertDatabaseMissing('User', [
            'email' => 'john_new@example.com',
        ]);
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $user = User::create([
            'id_user' => (string) Str::uuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123', // Will be hashed automatically by cast
        ]);

        $response = $this->putJson("/api/users/{$user->id_user}", [
            'current_password' => 'password123',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::create([
            'id_user' => (string) Str::uuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->putJson("/api/users/{$user->id_user}", [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_to_the_same_password(): void
    {
        $user = User::create([
            'id_user' => (string) Str::uuid(),
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->putJson("/api/users/{$user->id_user}", [
            'current_password' => 'password123',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }
}
