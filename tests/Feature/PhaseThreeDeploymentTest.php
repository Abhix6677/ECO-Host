<?php

namespace Tests\Feature;

use App\Models\Deployment;
use App\Models\User;
use App\Models\Website;
use App\Services\CloudflareTunnelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class PhaseThreeDeploymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Override CoCalc receiver URL so Http::fake() matches
        config([
            'services.cocalc.receiver_url' => 'http://localhost:9000',
            'services.cocalc.secret_key'   => 'ecohost_cocalc_secret_key_2026',
        ]);

        // All tests in this class fake a successful CoCalc response so
        // we are testing the Laravel-side deployment pipeline, not a real CoCalc.
        Http::fake([
            'http://localhost:9000/api/deploy' => Http::response([
                'status'          => 'success',
                'message'         => 'Deployed successfully',
                'site_uuid'       => 'fake-uuid',
                'user_id'         => '1',
                'file_count'      => 2,
                'cocalc_path'     => '/home/user/websites/public_sites/fake-uuid',
                'public_url_path' => '/storage/sites/fake-uuid/',
                'live_url'        => 'https://test.trycloudflare.com/storage/sites/fake-uuid/',
            ], 200),
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function createValidZip(): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'eco_dep_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE);
        $zip->addFromString('index.html', '<html><body><h1>Deploy Test</h1></body></html>');
        $zip->addFromString('style.css', 'body{color:#fff}');
        $zip->close();
        return new UploadedFile($tempPath, 'website.zip', 'application/zip', null, true);
    }

    private function uploadSite(User $user, string $name = 'Deploy Test Site'): Website
    {
        $this->actingAs($user)->post('/websites', [
            'name'     => $name,
            'zip_file' => $this->createValidZip(),
        ]);
        return Website::where('user_id', $user->id)->where('name', $name)->firstOrFail();
    }

    // -----------------------------------------------------------------------
    // Deployments Index
    // -----------------------------------------------------------------------

    public function test_deployments_index_renders(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/deployments');
        $response->assertStatus(200);
        $response->assertSee('Deployment History');
    }

    // -----------------------------------------------------------------------
    // Deploy Action
    // -----------------------------------------------------------------------

    public function test_deploy_creates_deployment_record(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        $this->actingAs($user)->post("/websites/{$website->id}/deploy");

        $this->assertDatabaseHas('deployments', [
            'website_id' => $website->id,
            'user_id'    => $user->id,
        ]);
    }

    public function test_successful_deploy_marks_website_live(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        $this->actingAs($user)->post("/websites/{$website->id}/deploy");

        $website->refresh();
        $this->assertSame('live', $website->status);
        $this->assertNotNull($website->live_url);
    }

    public function test_successful_deploy_records_live_url(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        $this->actingAs($user)->post("/websites/{$website->id}/deploy");

        $deployment = Deployment::where('website_id', $website->id)->first();
        $this->assertNotNull($deployment);
        $this->assertSame('live', $deployment->status);
        $this->assertNotNull($deployment->live_url);
        $this->assertNotNull($deployment->deployed_at);
    }

    public function test_deploy_stores_cocalc_path(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        $this->actingAs($user)->post("/websites/{$website->id}/deploy");

        $website->refresh();
        $this->assertNotNull($website->public_path);
        // In cocalc mode public_path is the CoCalc server path
        $this->assertStringContainsString('cocalc://', $website->public_path);
    }

    public function test_deploy_captures_log_output(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        $this->actingAs($user)->post("/websites/{$website->id}/deploy");

        $deployment = Deployment::where('website_id', $website->id)->first();
        $this->assertNotEmpty($deployment->log_output);
        $this->assertStringContainsString('Deployment started', $deployment->log_output);
    }

    public function test_deploy_redirects_to_websites_on_success(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        $response = $this->actingAs($user)->post("/websites/{$website->id}/deploy");

        // On success redirects to websites.index
        $response->assertRedirect('/websites');
    }

    public function test_unauthorized_user_cannot_deploy_another_users_site(): void
    {
        $userA   = User::factory()->create();
        $userB   = User::factory()->create();
        $website = $this->uploadSite($userA);

        $response = $this->actingAs($userB)->post("/websites/{$website->id}/deploy");
        $response->assertStatus(403);
    }

    public function test_deploy_fails_gracefully_when_source_missing(): void
    {
        $user = User::factory()->create();

        $website = $user->websites()->create([
            'uuid'              => Str::uuid(),
            'name'              => 'Ghost Site',
            'slug'              => 'ghost-site',
            'original_filename' => 'ghost.zip',
            'storage_path'      => 'websites/user_' . $user->id . '/nonexistent_uuid',
            'size_kb'           => 0,
            'status'            => 'ready',
        ]);

        $response = $this->actingAs($user)->post("/websites/{$website->id}/deploy");
        $response->assertSessionHas('error');

        $website->refresh();
        $this->assertSame('ready', $website->status);
        $this->assertDatabaseMissing('deployments', ['website_id' => $website->id]);
    }

    public function test_redeploy_creates_additional_deployment_record(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        $this->actingAs($user)->post("/websites/{$website->id}/deploy");
        $this->actingAs($user)->post("/websites/{$website->id}/deploy");

        $this->assertCount(2, Deployment::where('website_id', $website->id)->get());

        $website->refresh();
        $this->assertSame('live', $website->status);
    }

    // -----------------------------------------------------------------------
    // CloudflareTunnelService unit tests
    // -----------------------------------------------------------------------

    public function test_tunnel_service_returns_local_url_when_not_configured(): void
    {
        $service = new CloudflareTunnelService();
        $uuid    = (string) Str::uuid();

        $url = $service->getLiveUrl($uuid);

        $this->assertStringContainsString($uuid, $url);
        $this->assertStringContainsString('storage/sites', $url);
    }

    public function test_tunnel_service_is_not_configured_without_env_var(): void
    {
        $service = new CloudflareTunnelService();
        $this->assertFalse($service->isTunnelConfigured());
    }

    // -----------------------------------------------------------------------
    // Cleanup
    // -----------------------------------------------------------------------

    protected function tearDown(): void
    {
        foreach (['websites', 'public/sites'] as $dir) {
            $path = storage_path('app/' . $dir);
            if (is_dir($path)) {
                $this->rrmdir($path);
            }
        }
        parent::tearDown();
    }

    private function rrmdir(string $dir): void
    {
        foreach (new \FilesystemIterator($dir) as $item) {
            $item->isDir() ? $this->rrmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
