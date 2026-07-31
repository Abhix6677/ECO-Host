<?php

namespace Tests\Feature;

use App\Models\Deployment;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use ZipArchive;

class PhaseFourLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.cocalc.receiver_url' => 'http://localhost:9000',
            'services.cocalc.secret_key'   => 'ecohost_cocalc_secret_key_2026',
        ]);

        // Fake all CoCalc deploy calls — individual tests that test CoCalc-specific
        // behaviour override this with their own Http::fake() call.
        Http::fake([
            'http://localhost:9000/api/deploy' => Http::response([
                'status'          => 'success',
                'message'         => 'Deployed.',
                'site_uuid'       => 'fake-uuid',
                'user_id'         => '1',
                'file_count'      => 2,
                'cocalc_path'     => '/home/user/websites/public_sites/fake-uuid',
                'public_url_path' => '/storage/sites/fake-uuid/',
                'live_url'        => 'https://test.trycloudflare.com/storage/sites/fake-uuid/',
            ], 200),
            'http://localhost:9000/api/site/*' => Http::response([
                'status'        => 'success',
                'message'       => 'Deleted.',
                'site_uuid'     => 'fake-uuid',
                'deleted_paths' => [],
            ], 200),
        ]);
    }

    private function createValidZip(): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'eco_p4_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE);
        $zip->addFromString('index.html', '<html><body><h1>Lifecycle Test</h1></body></html>');
        $zip->close();
        return new UploadedFile($tempPath, 'website.zip', 'application/zip', null, true);
    }

    private function uploadSite(User $user, string $name = 'Lifecycle Test Site'): Website
    {
        $this->actingAs($user)->post('/websites', [
            'name'     => $name,
            'zip_file' => $this->createValidZip(),
        ]);
        return Website::where('user_id', $user->id)->where('name', $name)->firstOrFail();
    }

    // -----------------------------------------------------------------------
    // Website Details Page Tests
    // -----------------------------------------------------------------------

    public function test_website_details_page_renders_for_owner(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user, 'Details Test Site');

        $response = $this->actingAs($user)->get("/websites/{$website->id}");

        $response->assertStatus(200);
        $response->assertSee('Details Test Site');
        $response->assertSee('Upload Date');
        $response->assertSee('Storage Size');
        $response->assertSee('Deployment History');
    }

    public function test_website_details_page_forbidden_for_other_user(): void
    {
        $userA   = User::factory()->create();
        $userB   = User::factory()->create();
        $website = $this->uploadSite($userA, 'User A Site');

        $response = $this->actingAs($userB)->get("/websites/{$website->id}");
        $response->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // Website Redeploy Tests
    // -----------------------------------------------------------------------

    public function test_redeploy_updates_deployment_history_and_logs(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user, 'Redeploy Test Site');

        // First deploy
        $this->actingAs($user)->post("/websites/{$website->id}/deploy");
        $this->assertCount(1, Deployment::where('website_id', $website->id)->get());

        // Second deploy (redeploy)
        $this->actingAs($user)->post("/websites/{$website->id}/deploy");
        $this->assertCount(2, Deployment::where('website_id', $website->id)->get());

        $website->refresh();
        $this->assertSame('live', $website->status);

        // Details page should list both deployment runs
        $response = $this->actingAs($user)->get("/websites/{$website->id}");
        $response->assertSee('2 Total Deployment Runs');
    }

    // -----------------------------------------------------------------------
    // Website Delete Tests
    // -----------------------------------------------------------------------

    public function test_user_can_delete_website_and_cascade_deployments(): void
    {
        $user    = User::factory()->create();
        $website = $this->uploadSite($user, 'Delete Test Site');

        // Deploy first
        $this->actingAs($user)->post("/websites/{$website->id}/deploy");
        $this->assertDatabaseHas('deployments', ['website_id' => $website->id]);

        // Now delete
        $response = $this->actingAs($user)->delete("/websites/{$website->id}");
        $response->assertRedirect('/websites');

        // Verify website and deployment history are purged from DB
        $this->assertDatabaseMissing('websites', ['id' => $website->id]);
        $this->assertDatabaseMissing('deployments', ['website_id' => $website->id]);
    }

    public function test_user_cannot_delete_another_users_website(): void
    {
        $userA   = User::factory()->create();
        $userB   = User::factory()->create();
        $website = $this->uploadSite($userA, 'Protected Site');

        $response = $this->actingAs($userB)->delete("/websites/{$website->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('websites', ['id' => $website->id]);
    }

    public function test_cocalc_remote_file_purge_triggered_on_delete_when_target_is_cocalc(): void
    {
        config(['services.cocalc.target' => 'cocalc']);

        Http::fake([
            'http://localhost:9000/api/site/*' => Http::response([
                'status'  => 'success',
                'message' => 'Successfully deleted website files from CoCalc storage.',
            ], 200),
        ]);

        $user    = User::factory()->create();
        $website = $this->uploadSite($user, 'CoCalc Purge Site');

        $response = $this->actingAs($user)->delete("/websites/{$website->id}");
        $response->assertRedirect('/websites');

        Http::assertSent(function ($request) use ($website) {
            return $request->method() === 'DELETE' &&
                   str_contains($request->url(), "/api/site/{$website->uuid}");
        });

        config(['services.cocalc.target' => 'local']);
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
