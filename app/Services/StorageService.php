<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StorageService
{
    /**
     * Build the storage path for a user's site directory.
     * Pattern: websites/user_{userId}/{siteUuid}/
     */
    public function getSiteStoragePath(int $userId, string $siteUuid): string
    {
        return "websites/user_{$userId}/{$siteUuid}";
    }

    /**
     * Build the public serving path where deployed files are copied.
     * Pattern: public/sites/{siteUuid}/
     */
    public function getSitePublicPath(string $siteUuid): string
    {
        return "public/sites/{$siteUuid}";
    }

    /**
     * Return the absolute filesystem path for a storage-relative path.
     */
    public function absolutePath(string $relativePath): string
    {
        return storage_path("app/{$relativePath}");
    }

    /**
     * Ensure a directory exists, creating it (with parents) if it does not.
     */
    public function ensureDirectoryExists(string $absolutePath): void
    {
        if (!is_dir($absolutePath)) {
            mkdir($absolutePath, 0755, true);
        }
    }

    /**
     * Recursively delete a directory and all its contents.
     * Safe — it canonically resolves the path and requires it to live
     * inside storage/app to prevent accidental system-wide deletes.
     *
     * @throws RuntimeException if the path escapes storage/app
     */
    public function deleteDirectory(string $absolutePath): bool
    {
        $safeBase = realpath(storage_path('app'));

        // Canonicalise only if the directory currently exists
        $real = is_dir($absolutePath) ? realpath($absolutePath) : null;

        if ($real !== null && !str_starts_with($real, $safeBase)) {
            throw new RuntimeException(
                "Refusing to delete path outside storage/app: {$absolutePath}"
            );
        }

        if (!is_dir($absolutePath)) {
            return true; // Nothing to delete
        }

        $this->rrmdir($absolutePath);
        return true;
    }

    /**
     * Copy all files from one directory into another.
     * Both paths must already exist (or destination will be created).
     */
    public function copyDirectory(string $source, string $destination): void
    {
        $this->ensureDirectoryExists($destination);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Recursive directory removal (rmdir -rf equivalent in PHP).
     */
    private function rrmdir(string $dir): void
    {
        foreach (new \FilesystemIterator($dir) as $item) {
            if ($item->isDir() && !$item->isLink()) {
                $this->rrmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        rmdir($dir);
    }
}
