<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_check_is_user_is_admin()
    {
        $user = User::factory()->create([
            'email' => 'admin@mail.com'
        ]);

        $user2 = User::factory()->create([
            'email' => 'ana@mail.com'
        ]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user2->isAdmin());
    }
}
