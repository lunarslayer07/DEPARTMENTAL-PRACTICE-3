<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_sign_in_with_username_and_log_out(): void
    {
        $user = User::factory()->create(['username' => 'bookadmin']);

        $this->post(route('login.store'), [
            'login' => 'bookadmin',
            'password' => 'password',
        ])->assertRedirect(route('books.index'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->post(route('login.store'), [
            'login' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_show_a_clear_error(): void
    {
        User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'login' => 'missing@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_guests_cannot_open_inventory_or_reports(): void
    {
        $this->get(route('books.index'))->assertRedirect(route('login'));
        $this->get(route('reports.index'))->assertRedirect(route('login'));
        $this->get(route('reports.export.pdf'))->assertRedirect(route('login'));
        $this->get(route('reports.export.excel'))->assertRedirect(route('login'));
    }
}
