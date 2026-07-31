<?php

namespace Tests\Feature;

use App\Models\Deployment;
use App\Models\User;
use App\Models\Website;
use App\Services\CoCalcReceiverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use ZipArchive;

class PhaseThreePointFiveCoCalcTest extends TestCase
{
    use RefreshDatabase;

    private const RECEIVER_URL = 'http://localhost:9000';

    protected function setUp(): void
    {
        parent::setUp();

        // Pin receiver URL so Http::fake() pattern always matches
        config([
            'services.cocalc.receiver_url' => self::RECEIVER_URL,
            'services.cocalc.secret_key'   => 'ecohost_cocalc_secret_key_2026',
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function createValidZip(): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'eco_cocalc_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE);
        $zip->addFromString('index.html', '<html><body><h1>CoCalc Test</h1></body></html>');
        $zip->close();
        return new UploadedFile($tempPath, 'website.zip', 'application/zip', null, true);
    }

    private function uploadSite(User $user, string $name = 'CoCalc Test Site'): Website
    {
        $this->actingAs($user)->post('/websites', [
            'name'     => $name,
            'zip_file' => $this->createValidZip(),
        ]);
        return Website::where('user_id', $user->id)->where('name', $name)->firstOrFail();
    }

    private function fakeSuccessfulDeploy(string $siteUuid = 'test-uuid'): void
    {
        Http::fake([
            self::RECEIVER_URL . '/api/deploy' => Http::response([
                'status'          => 'success',
                'message'         => 'Site deployed successfully on CoCalc Ubuntu.',
                'site_uuid'       => $siteUuid,
                'user_id'         => '1',
                'file_count'      => 1,
                'cocalc_path'     => "/home/user/websites/public_sites/{$siteUuid}",
                'public_url_path' => "/storage/sites/{$siteUuid}/",
                'live_url'        => "https://test.trycloudflare.com/storage/sites/{$siteUuid}/",
            ], 200),
        ]);
    }

    // -----------------------------------------------------------------------
    // Health check
    // -----------------------------------------------------------------------

    public function test_cocalc_health_check_success(): void
    {
        Http::fake([
            self::RECEIVER_URL . '/health' => Http::response([
                'status'     => 'ok',
                'server'     => 'EcoHost CoCalc Receiver',
                'storage'    => '/home/user/websites',
                'cloudflare' => 'https://test.trycloudflare.com',
            ], 200),
        ]);

        $service = new CoCalcReceiverService();
        $result  = $service->healthCheck();

        $this->assertTrue($result['online']);
        $this->assertSame('ok', $result['data']['status']);
    }

    public function test_cocalc_health_check_failure_when_offline(): void
    {
        Http::fake([
            self::RECEIVER_URL . '/health' => Http::response('Server Error', 500),
        ]);

        $service = new CoCalcReceiverService();
        $result  = $service->healthCheck();

        $this->assertFalse($result['online']);
    }

    // -----------------------------------------------------------------------
    // Deploy — CoCalcReceiverService unit test
    // -----------------------------------------------------------------------

    public function test_cocalc_deploy_transmits_payload_with_token_header(): void
    {
        $this->fakeSuccessfulDeploy('test-uuid-1234');

        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        $service = new CoCalcReceiverService();
        $result  = $service->deployToCoCalc($website);

        $this->assertSame('success', $result['status']);
        $this->assertSame('test-uuid-1234', $result['site_uuid']);
        $this->assertNotNull($result['live_url']);

        // Multipart request was sent to the right URL with the auth header
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-EcoHost-Token') &&
                   $request->url() === self::RECEIVER_URL . '/api/deploy';
        });
    }

    // -----------------------------------------------------------------------
    // Deploy — full DeploymentService pipeline
    // -----------------------------------------------------------------------

    public function test_deployment_service_uses_cocalc_target_when_configured(): void
    {
        Http::fake([
            self::RECEIVER_URL . '/api/deploy' => Http::response([
                'status'          => 'success',
                'message'         => 'Deployed to CoCalc Ubuntu',
                'site_uuid'       => 'test-uuid-5678',
                'user_id'         => '1',
                'file_count'      => 2,
                'cocalc_path'     => '/home/user/websites/public_sites/test-uuid-5678',
                'public_url_path' => '/storage/sites/test-uuid-5678/',
                'live_url'        => 'https://test.trycloudflare.com/storage/sites/test-uuid-5678/',
            ], 200),
        ]);

        $user    = User::factory()->create();
        $website = $this->uploadSite($user, 'Remote CoCalc Site');

        $this->actingAs($user)->post("/websites/{$website->id}/deploy");

        $website->refresh();
        $this->assertSame('live', $website->status);
        $this->assertStringContainsString('trycloudflare.com', $website->live_url);

        $deployment = Deployment::where('website_id', $website->id)->first();
        $this->assertNotNull($deployment);
        $this->assertSame('live', $deployment->status);
        $this->assertStringContainsString('CoCalc Ubuntu', $deployment->log_output);
        $this->assertStringContainsString('Deployed to CoCalc Ubuntu', $deployment->log_output);
    }

    // -----------------------------------------------------------------------
    // Delete — CoCalcReceiverService
    // -----------------------------------------------------------------------

    public function test_cocalc_delete_sends_delete_request(): void
    {
        $this->fakeSuccessfulDeploy();

        $user    = User::factory()->create();
        $website = $this->uploadSite($user);

        Http::fake([
            self::RECEIVER_URL . '/api/site/*' => Http::response([
                'status'        => 'success',
                'message'       => 'Deleted.',
                'site_uuid'     => $website->uuid,
                'deleted_paths' => [],
            ], 200),
        ]);

        $service = new CoCalcReceiverService();
        $result  = $service->deleteFromCoCalc($website);

        $this->assertTrue($result);
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
