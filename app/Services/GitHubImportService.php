<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GitHubImportService
{
    /**
     * Parse a GitHub repository URL and download its source code as an UploadedFile ZIP object.
     *
     * Supports URLs like:
     *   - https://github.com/username/repository
     *   - https://github.com/username/repository.git
     *   - https://github.com/username/repository/tree/main
     *   - https://github.com/username/repository/tree/master
     *   - https://github.com/username/repository/tree/dev
     *
     * @param string $url
     * @return array{file: UploadedFile, repoName: string, owner: string, branch: string}
     *
     * @throws RuntimeException on invalid URL or download failure
     */
    public function downloadGitHubRepoZip(string $url): array
    {
        $parsed = $this->parseGitHubUrl($url);
        $owner    = $parsed['owner'];
        $repo     = $parsed['repo'];
        $branch   = $parsed['branch'];

        $zipBytes = null;
        $branchesToTry = $branch ? [$branch] : ['main', 'master', 'dev'];

        foreach ($branchesToTry as $tryBranch) {
            $zipUrl = "https://github.com/{$owner}/{$repo}/archive/refs/heads/{$tryBranch}.zip";
            
            try {
                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'EcoHost-Importer/1.0',
                        'Accept'     => 'application/vnd.github.v3+json, application/zip',
                    ])
                    ->get($zipUrl);

                if ($response->successful() && strlen($response->body()) > 100) {
                    $zipBytes = $response->body();
                    $branch   = $tryBranch;
                    break;
                }
            } catch (\Throwable $e) {
                // Try next branch candidate
                continue;
            }
        }

        if (!$zipBytes) {
            throw new RuntimeException(
                "Could not download GitHub repository from {$url}. " .
                "Please verify the repository is Public and contains a valid 'main' or 'master' branch."
            );
        }

        // Save bytes to temporary zip file
        $tempPath = sys_get_temp_dir() . '/github_' . Str::uuid() . '.zip';
        file_put_contents($tempPath, $zipBytes);

        $uploadedFile = new UploadedFile(
            $tempPath,
            "github_{$owner}_{$repo}_{$branch}.zip",
            'application/zip',
            null,
            true
        );

        return [
            'file'     => $uploadedFile,
            'repo'     => $repo,
            'repoName' => $repo,
            'owner'    => $owner,
            'branch'   => $branch,
        ];
    }

    /**
     * Parse GitHub URL into owner, repo, and optional branch.
     */
    public function parseGitHubUrl(string $url): array
    {
        $cleanUrl = trim($url);
        $cleanUrl = preg_replace('/\.git$/i', '', $cleanUrl);

        if (!preg_match('/github\.com\/([^\/]+)\/([^\/]+)(?:\/tree\/([^\/]+))?/i', $cleanUrl, $matches)) {
            throw new RuntimeException('Invalid GitHub repository URL. Format should be: https://github.com/username/repository');
        }

        return [
            'owner'  => $matches[1],
            'repo'   => $matches[2],
            'branch' => $matches[3] ?? null,
        ];
    }
}
