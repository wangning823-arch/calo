<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => '测试用户',
            'phone' => '13800138000',
            'password' => 'password123',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
        ]);
    }

    public function test_unagreed_user_redirected_to_agreement(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('agreement.show'));
    }

    public function test_agreed_user_can_access_dashboard(): void
    {
        $this->user->update(['agreed_at' => now()]);
        $this->actingAs($this->user);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_agreement_page_shows_content(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('agreement.show'));
        $response->assertStatus(200);
        $response->assertSee('用户协议');
        $response->assertSee('隐私政策');
    }

    public function test_accept_agreement(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('agreement.accept'));
        $response->assertRedirect();

        $this->user->refresh();
        $this->assertNotNull($this->user->agreed_at);
    }

    public function test_terms_page(): void
    {
        $response = $this->get(route('pages.terms'));
        $response->assertStatus(200);
        $response->assertSee('用户协议');
    }

    public function test_privacy_page(): void
    {
        $response = $this->get(route('pages.privacy'));
        $response->assertStatus(200);
        $response->assertSee('隐私政策');
    }

    public function test_disclaimer_page(): void
    {
        $response = $this->get(route('pages.disclaimer'));
        $response->assertStatus(200);
        $response->assertSee('免责声明');
    }

    public function test_icp_page(): void
    {
        $response = $this->get(route('pages.icp'));
        $response->assertStatus(200);
        $response->assertSee('ICP备案');
    }

    public function test_disclaimer_mentions_medical(): void
    {
        $response = $this->get(route('pages.disclaimer'));
        $response->assertSee('非医疗建议');
    }
}
