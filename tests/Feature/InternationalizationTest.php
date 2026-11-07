<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class InternationalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_locale_and_timezone_are_applied_by_middleware(): void
    {
        $user = User::factory()->create([
            'locale' => 'en',
            'timezone' => 'America/New_York',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertOk();

        $this->assertSame('en', App::getLocale());
        $this->assertSame('America/New_York', date_default_timezone_get());
    }

    public function test_ai_advisor_responds_in_english_for_english_queries(): void
    {
        $user = User::factory()->create([
            'locale' => 'en',
            'currency' => 'USD',
        ]);

        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'How can I manage my monthly budget and save more money?',
        ]);

        $response->assertOk();
        $reply = $response->json('reply');
        $this->assertNotEmpty($reply);
        $this->assertTrue(
            str_contains(strtolower($reply), 'budget') ||
            str_contains(strtolower($reply), 'saving') ||
            str_contains(strtolower($reply), 'finora')
        );
        $this->assertStringNotContainsString('Halo!', $reply);
        $this->assertStringNotContainsString('keuangan', strtolower($reply));
    }

    public function test_ai_advisor_rejects_out_of_context_in_english_for_english_user(): void
    {
        $user = User::factory()->create([
            'locale' => 'en',
        ]);

        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Can you write me a python script to scrape a website?',
        ]);

        $response->assertOk();
        $this->assertSame('guardrail_rejected', $response->json('mode'));
        $this->assertStringContainsString('Finora AI Financial Advisor', $response->json('reply'));
    }
}
