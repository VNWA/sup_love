<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('username', 'testuser')->first();
        $this->assertNotNull($user);
        $this->assertSame(0, $user->point);
    }

    public function test_registration_accepts_password_shorter_than_eight_characters(): void
    {
        $response = $this->post(route('register.store'), [
            'username' => 'shortpwuser',
            'password' => 'ab',
            'password_confirmation' => 'ab',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertNotNull(User::query()->where('username', 'shortpwuser')->first());
    }
}
