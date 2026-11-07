<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_tutorials_to_login(): void
    {
        $response = $this->get('/tutorials');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_tutorials_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/tutorials');

        $response->assertOk();
        $response->assertSee('Pusat Bantuan & Panduan Finora');
        $response->assertSee('Wallets & Multi-Asset Accounts');
        $response->assertSee('Smart Quick-Add Bar');
        $response->assertSee('AI Receipt Scanner');
        $response->assertSee('Bank Mutation Smart Import');
        $response->assertSee('Debt & Receivable Tracker');
        $response->assertSee('Recurring Bills & Cashflow Calendar');
        $response->assertSee('Financial Health Score');
        $response->assertSee('Cashflow Forecast');
        $response->assertSee('Monthly Financial Wrapped');
        $response->assertSee('Finora AI Financial Advisor');
        $response->assertSee('Minimalist Dark Mode');
        $response->assertSee('Mulai Tur Interaktif');
        $response->assertViewHas('tourSteps', function ($tourSteps) {
            return count($tourSteps) === 6;
        });
        $response->assertViewHas('features', function ($features) {
            return count($features) === 12;
        });
    }

    public function test_tutorial_hub_script_is_not_html_escaped(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get('/tutorials')->getContent();

        $this->assertMatchesRegularExpression('/<script>\s*window\.tutorialHub.*?<\/script>/s', $html);
        preg_match('/<script>\s*window\.tutorialHub.*?<\/script>/s', $html, $matches);

        // HTML entities inside an inline <script> cause a JS SyntaxError that breaks the whole page.
        $this->assertStringNotContainsString('&quot;', $matches[0]);
        $this->assertStringNotContainsString('&#039;', $matches[0]);
        $this->assertStringContainsString('startTour()', $matches[0]);
    }

    public function test_navigation_includes_tutorials_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('tutorials.index'));
        $response->assertSee('Panduan & Tutorial');
    }
}

