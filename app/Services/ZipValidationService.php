<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use ZipArchive;
use RuntimeException;

class ZipValidationService
{
    /**
     * Maximum allowed extracted size in bytes (150 MB)
     */
    private const MAX_EXTRACTED_SIZE_BYTES = 157286400;

    /**
     * Maximum number of file entries allowed inside a zip
     */
    private const MAX_FILE_ENTRIES = 2000;

    /**
     * File extensions that are absolutely forbidden inside uploaded zips.
     * These are server-side executable and dangerous file types.
     */
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'com', 'msi',
        'sh', 'bash', 'zsh', 'fish', 'ps1', 'psm1', 'psd1',
        'py', 'pyc', 'rb', 'pl', 'lua',
        'env', 'htaccess', 'htpasswd',
        'asp', 'aspx', 'cfm', 'cgi', 'shtml',
        'jar', 'war', 'class',
        'dll', 'so', 'dylib',
    ];

    /**
     * Validate the uploaded ZIP file and extract it to the target directory.
     *
     * @param UploadedFile $file   The uploaded zip file
     * @param string       $dest   Absolute path where content should be extracted
     *
     * @return array{fileCount: int, totalSizeKb: int}
     *
     * @throws RuntimeException on any validation or extraction failure
     */
    public function validateAndExtract(UploadedFile $file, string $dest): array
    {
        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            throw new RuntimeException('The uploaded file is not a valid ZIP archive or is corrupted.');
        }

        try {
            $numFiles   = $zip->count();
            $hasIndex   = false;
            $totalBytes = 0;

            // -------------------------------------------------------
            // PRE-EXTRACTION SCAN
            // -------------------------------------------------------
            if ($numFiles === 0) {
                throw new RuntimeException('The uploaded ZIP archive is empty.');
            }

            if ($numFiles > self::MAX_FILE_ENTRIES) {
                throw new RuntimeException(
                    "ZIP contains too many files ({$numFiles}). Maximum allowed is " . self::MAX_FILE_ENTRIES . "."
                );
            }

            for ($i = 0; $i < $numFiles; $i++) {
                $stat = $zip->statIndex($i);

                // Zip bomb protection: check uncompressed sizes before extracting
                $totalBytes += $stat['size'];
                if ($totalBytes > self::MAX_EXTRACTED_SIZE_BYTES) {
                    throw new RuntimeException(
                        'ZIP archive exceeds the maximum allowed extracted size of 50 MB. ' .
                        'This may be a zip bomb or an oversized archive.'
                    );
                }

                $entryName = $stat['name'];

                // Path traversal protection: reject any entry with ../ or absolute paths
                if (
                    str_contains($entryName, '../') ||
                    str_contains($entryName, '..\\') ||
                    str_starts_with($entryName, '/') ||
                    str_starts_with($entryName, '\\') ||
                    (strlen($entryName) >= 2 && ctype_alpha($entryName[0]) && $entryName[1] === ':')
                ) {
                    throw new RuntimeException(
                        "Path traversal attempt detected in ZIP entry: \"{$entryName}\". Upload rejected."
                    );
                }

                // Skip directory entries for extension checking
                if (str_ends_with($entryName, '/')) {
                    continue;
                }

                // Forbidden extension check
                $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
                if (in_array($ext, self::FORBIDDEN_EXTENSIONS, true)) {
                    throw new RuntimeException(
                        "Forbidden file type detected: \".{$ext}\" files are not allowed for security reasons."
                    );
                }

                // Check for root-level index.html (handle case where files are in a subdir or root)
                $basename = basename($entryName);
                if (strtolower($basename) === 'index.html') {
                    $hasIndex = true;
                }
            }

            if (!$hasIndex) {
                throw new RuntimeException(
                    'Your ZIP archive must contain an "index.html" file. ' .
                    'This is required as the entry point for your static website.'
                );
            }

            // -------------------------------------------------------
            // SAFE EXTRACTION
            // -------------------------------------------------------
            if (!is_dir($dest)) {
                mkdir($dest, 0755, true);
            }

            // Set memory limit for large zip processing
            ini_set('memory_limit', '512M');

            // Extract each file individually using a canonical path check to prevent traversal
            $realDest = realpath($dest);

            for ($i = 0; $i < $numFiles; $i++) {
                $stat      = $zip->statIndex($i);
                $entryName = $stat['name'];

                // Normalise and canonically verify the path stays inside $dest
                $normalised = $this->normalisePath($dest . DIRECTORY_SEPARATOR . $entryName);

                $checkNormalised = PHP_OS_FAMILY === 'Windows' ? strtolower($normalised) : $normalised;
                $checkDest       = PHP_OS_FAMILY === 'Windows' ? strtolower($realDest . DIRECTORY_SEPARATOR) : ($realDest . DIRECTORY_SEPARATOR);

                if (!str_starts_with($checkNormalised, $checkDest)) {
                    throw new RuntimeException("Unsafe path detected during extraction: \"{$entryName}\".");
                }

                // Skip directories (we'll create them implicitly)
                if (str_ends_with($entryName, '/') || str_ends_with($entryName, '\\')) {
                    if (!is_dir($normalised)) {
                        mkdir($normalised, 0755, true);
                    }
                    continue;
                }

                // Ensure parent directory exists
                $parentDir = dirname($normalised);
                if (!is_dir($parentDir)) {
                    mkdir($parentDir, 0755, true);
                }

                // Stream-copy from zip to disk (avoids holding all content in RAM)
                $stream = $zip->getStream($entryName);
                if ($stream === false) {
                    throw new RuntimeException("Failed to read entry \"{$entryName}\" from ZIP archive.");
                }

                $out = fopen($normalised, 'wb');
                if ($out === false) {
                    fclose($stream);
                    throw new RuntimeException("Failed to write file \"{$entryName}\" during extraction.");
                }

                stream_copy_to_stream($stream, $out);
                fclose($stream);
                fclose($out);
            }

        } finally {
            $zip->close();
        }

        // Auto-unwrap single top-level directory if index.html is not at root (e.g. GitHub zip archives)
        $this->autoUnwrapTopLevelFolder($dest);

        if (!file_exists($dest . DIRECTORY_SEPARATOR . 'index.html')) {
            throw new RuntimeException(
                'Your repository/ZIP archive must contain an "index.html" file at root. ' .
                'This is required as the entry point for your static website.'
            );
        }

        // Calculate actual extracted size (sum all file bytes on disk)
        $actualBytes = $this->calculateDirSize($dest);
        $sizeKb      = (int) ceil($actualBytes / 1024);
        $fileCount   = iterator_count(
            new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dest, \FilesystemIterator::SKIP_DOTS))
        );

        return [
            'fileCount'   => $fileCount,
            'totalSizeKb' => $sizeKb,
        ];
    }

    /**
     * Auto-unwrap single top-level wrapper directory (e.g. GitHub archive `reponame-main/`).
     */
    private function autoUnwrapTopLevelFolder(string $dest): void
    {
        $indexPath = $dest . DIRECTORY_SEPARATOR . 'index.html';
        if (file_exists($indexPath)) {
            return;
        }

        $items = array_diff(scandir($dest), ['.', '..']);
        if (count($items) === 1) {
            $singleItem = current($items);
            $subDirPath = $dest . DIRECTORY_SEPARATOR . $singleItem;

            if (is_dir($subDirPath) && file_exists($subDirPath . DIRECTORY_SEPARATOR . 'index.html')) {
                $subItems = array_diff(scandir($subDirPath), ['.', '..']);
                foreach ($subItems as $subItem) {
                    rename(
                        $subDirPath . DIRECTORY_SEPARATOR . $subItem,
                        $dest . DIRECTORY_SEPARATOR . $subItem
                    );
                }
                @rmdir($subDirPath);
            }
        }
    }

    /**
     * Normalise a file system path without requiring the path to exist yet.
     */
    private function normalisePath(string $path): string
    {
        $parts    = [];
        $segments = preg_split('/[\\/\\\\]+/', $path);

        foreach ($segments as $seg) {
            if ($seg === '' || $seg === '.') {
                continue;
            }
            if ($seg === '..') {
                array_pop($parts);
            } else {
                $parts[] = $seg;
            }
        }

        // Re-attach drive letter on Windows (e.g. C:)
        $sep    = DIRECTORY_SEPARATOR;
        $result = implode($sep, $parts);

        // If original path had an absolute slash, re-add it
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || str_starts_with($path, '/')) {
            $result = $sep . $result;
        }

        return $result;
    }

    /**
     * Recursively calculate total bytes of files in a directory.
     */
    private function calculateDirSize(string $dir): int
    {
        $total = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $total += $file->getSize();
            }
        }
        return $total;
    }
}
