<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GrantFounderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_existing_account_gains_the_founder_role_and_a_fresh_password(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create(['email' => 'linas@example.org', 'password' => Hash::make('old-password-123456')]);

        $this->artisan('portal:founder', ['email' => 'Linas@Example.org'])
            ->expectsOutputToContain('now has Founder access')
            ->assertSuccessful();

        $user->refresh();
        $this->assertTrue($user->hasRole('founder'));
        $this->assertFalse(Hash::check('old-password-123456', $user->password));
    }

    public function test_an_unknown_address_is_refused(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->artisan('portal:founder', ['email' => 'nobody@example.org'])->assertFailed();
    }
}
