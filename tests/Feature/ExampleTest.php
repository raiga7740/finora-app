<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Take Control of Your Money');
        $response->assertSee('Build Your Future');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));

        // SEO Verification
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<meta name="keywords"', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta property="og:image"', false);
        $response->assertSee('<meta name="twitter:card"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('WebApplication', false);
        $response->assertSee('FAQPage', false);
        $response->assertSee('site.webmanifest', false);
    }

    public function test_authenticated_user_visiting_home_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_authenticated_user_dashboard_has_noindex_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false);
    }
}
