<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class AppInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_info_to_login(): void
    {
        $response = $this->get(route('info.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_info_page_in_indonesian(): void
    {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        $response = $this->actingAs($user)->get(route('info.index'));

        $response->assertOk();
        $response->assertViewIs('info.index');
        $response->assertViewHasAll([
            'appMeta',
            'stats',
            'releases',
            'roadmap',
            'systemSpecs',
        ]);

        $response->assertSee('Finora');
        $response->assertSee(config('app.version', '2.5.0'));
        $response->assertSee('Aurora Zenith');
        $response->assertSee('v2.5.0');
        $response->assertSee('v2.4.0');
        $response->assertSee('v1.0.0');
        $response->assertSee('Semua Sistem Normal');
        $response->assertSee('Open Banking API Direct Sync');
        $response->assertSee('Diagnostik Sistem');
    }

    public function test_authenticated_user_can_view_info_page_in_english(): void
    {
        $user = User::factory()->create([
            'locale' => 'en',
        ]);

        $response = $this->actingAs($user)->get(route('info.index'));

        $response->assertOk();
        $response->assertViewIs('info.index');

        // Check English translations & content
        $response->assertSee('App Info &amp; Version', false);
        $response->assertSee('All Systems Operational');
        $response->assertSee('What&#039;s New in v2.5.0', false);
        $response->assertSee('Latest Release');
        $response->assertSee('Active Version');
        $response->assertSee('In Development');
        $response->assertSee('Upcoming Feature Roadmap');
        $response->assertSee('System Diagnostics &amp; Technical Specs', false);
        $response->assertSee('Copy System Info');
    }

    public function test_user_can_switch_locale_via_quick_switch_route(): void
    {
        $user = User::factory()->create([
            'locale' => 'id',
        ]);

        // Switch to English
        $response = $this->actingAs($user)->get(route('locale.switch', 'en'));

        $response->assertRedirect();
        $this->assertSame('en', session('locale'));
        $this->assertSame('en', $user->fresh()->locale);

        // Verify page renders in English
        $infoResponse = $this->actingAs($user->fresh())->get(route('info.index'));
        $infoResponse->assertSee('All Systems Operational');

        // Switch back to Indonesian
        $responseId = $this->actingAs($user->fresh())->get(route('locale.switch', 'id'));
        $responseId->assertRedirect();
        $this->assertSame('id', session('locale'));
        $this->assertSame('id', $user->fresh()->locale);
    }

    public function test_whats_new_endpoint_returns_json(): void
    {
        $user = User::factory()->create([
            'locale' => 'en',
        ]);

        $response = $this->actingAs($user)->get(route('info.whats-new'));

        $response->assertOk();
        $response->assertJsonStructure([
            'version',
            'latest_release' => [
                'version',
                'name',
                'codename',
                'date',
                'is_latest',
                'summary',
                'highlights',
                'changes',
            ],
        ]);
        $response->assertJsonPath('version', config('app.version', '2.5.0'));
        $response->assertJsonPath('latest_release.version', 'v2.5.0');
        $response->assertJsonPath('latest_release.badge', 'Latest Release');
    }

    public function test_navigation_sidebar_and_footer_contain_info_links(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('info.index'));
        $response->assertSee(route('locale.switch', 'en'));
        $response->assertSee(route('locale.switch', 'id'));
        $response->assertSee('v' . config('app.version', '2.5.0'));
    }

    public function test_profile_page_contains_info_and_version_card(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee(route('info.index'));
        $response->assertSee('v' . config('app.version', '2.5.0'));
    }
}
