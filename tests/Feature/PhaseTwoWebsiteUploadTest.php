<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class PhaseTwoWebsiteUploadTest extends TestCase
{
    use RefreshDatabase;

    private function createValidZip(): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'ecohost_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE);
        $zip->addFromString('index.html', '<html><body><h1>Hello EcoHost</h1></body></html>');
        $zip->addFromString('style.css', 'body { color: #fff; }');
        $zip->addFromString('js/app.js', 'console.log("Hello");');
        $zip->close();
        return new UploadedFile($tempPath, 'website.zip', 'application/zip', null, true);
    }

    private function createZipWithoutIndex(): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'ecohost_noidx_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE);
        $zip->addFromString('about.html', '<html><body>About</body></html>');
        $zip->close();
        return new UploadedFile($tempPath, 'noindex.zip', 'application/zip', null, true);
    }

    private function createZipWithPhp(): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'ecohost_php_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE);
        $zip->addFromString('index.html', '<html><body>Test</body></html>');
        $zip->addFromString('shell.php', '<?php phpinfo(); ?>');
        $zip->close();
        return new UploadedFile($tempPath, 'malicious.zip', 'application/zip', null, true);
    }

    public function test_upload_page_renders(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/websites/create');
        $response->assertStatus(200);
        $response->assertSee('Upload New Website');
    }

    public function test_websites_index_renders(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/websites');
        $response->assertStatus(200);
        $response->assertSee('My Websites');
    }

    public function test_valid_zip_uploads_successfully(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/websites', [
            'name'     => 'My Portfolio Site',
            'zip_file' => $this->createValidZip(),
        ]);
        $response->assertRedirect('/websites');
        $this->assertDatabaseHas('websites', [
            'user_id' => $user->id,
            'name'    => 'My Portfolio Site',
            'status'  => 'ready',
        ]);
    }

    public function test_upload_creates_storage_directory(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/websites', [
            'name'     => 'Storage Dir Test',
            'zip_file' => $this->createValidZip(),
        ]);
        $website = Website::where('user_id', $user->id)->first();
        $this->assertNotNull($website);
        $absPath = storage_path("app/{$website->storage_path}");
        $this->assertDirectoryExists($absPath);
        $this->assertFileExists("{$absPath}/index.html");
    }

    public function test_zip_without_index_html_is_rejected(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/websites', [
            'name'     => 'No Index Site',
            'zip_file' => $this->createZipWithoutIndex(),
        ]);
        $this->assertDatabaseMissing('websites', ['user_id' => $user->id]);
        $response->assertSessionHas('error');
    }

    public function test_zip_with_php_file_is_rejected(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/websites', [
            'name'     => 'Hacker Site',
            'zip_file' => $this->createZipWithPhp(),
        ]);
        $this->assertDatabaseMissing('websites', ['user_id' => $user->id]);
        $response->assertSessionHas('error');
    }

    public function test_upload_requires_name(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/websites', [
            'zip_file' => $this->createValidZip(),
        ]);
        $response->assertSessionHasErrors(['name']);
    }

    public function test_upload_requires_zip_file(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/websites', [
            'name' => 'No File Site',
        ]);
        $response->assertSessionHasErrors(['zip_file']);
    }

    public function test_user_can_delete_own_website(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/websites', [
            'name'     => 'Site To Delete',
            'zip_file' => $this->createValidZip(),
        ]);
        $website = Website::where('user_id', $user->id)->first();
        $this->assertNotNull($website);
        $response = $this->actingAs($user)->delete("/websites/{$website->id}");
        $response->assertRedirect('/websites');
        $this->assertDatabaseMissing('websites', ['id' => $website->id]);
    }

    public function test_user_cannot_delete_another_users_website(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $this->actingAs($userA)->post('/websites', [
            'name'     => 'User A Site',
            'zip_file' => $this->createValidZip(),
        ]);
        $website = Website::where('user_id', $userA->id)->first();
        $response = $this->actingAs($userB)->delete("/websites/{$website->id}");
        $response->assertStatus(403);
        $this->assertDatabaseHas('websites', ['id' => $website->id]);
    }

    public function test_websites_are_isolated_per_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Create a website directly in the DB for userA (bypassing upload to avoid session bleed)
        $userA->websites()->create([
            'uuid'              => \Illuminate\Support\Str::uuid(),
            'name'              => 'UserA Exclusive Site',
            'slug'              => 'usera-exclusive-site',
            'original_filename' => 'site.zip',
            'storage_path'      => 'websites/user_' . $userA->id . '/test',
            'size_kb'           => 10,
            'status'            => 'ready',
        ]);

        // User B should not see User A's site
        $response = $this->actingAs($userB)->get('/websites');
        $response->assertSee('No Websites Yet');
        $response->assertDontSee('UserA Exclusive Site');
    }

    protected function tearDown(): void
    {
        $storageBase = storage_path('app/websites');
        if (is_dir($storageBase)) {
            $this->rrmdir($storageBase);
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
