<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PrivateReportStorage
{
    public const ID_PATTERN = '[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}';

    public function retentionSeconds(): int
    {
        $hours = filter_var(config('reports.retention_hours'), FILTER_VALIDATE_INT);
        return ($hours !== false && $hours > 0 && $hours <= 8760 ? $hours : 24) * 3600;
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk('reports');
    }

    public function store(string $pdf): array
    {
        if (!str_starts_with($pdf, '%PDF-')) {
            throw new RuntimeException('Invalid report output.');
        }

        $disk = $this->disk();
        $id = (string) Str::uuid();
        $pending = $id . '.tmp';
        $filename = $id . '.pdf';
        $createdAt = now()->getTimestamp();

        try {
            // Local rename publishes the complete PDF atomically. No shared date-based name.
            if (!$disk->put($pending, $pdf, ['visibility' => 'private']) ||
                !touch($disk->path($pending), $createdAt) ||
                !$disk->move($pending, $filename)) {
                throw new RuntimeException('Unable to store report.');
            }
        } finally {
            // A failed generation never leaves a partial PDF available for download.
            if ($disk->exists($pending)) $disk->delete($pending);
        }

        return [
            'generation_id' => $id,
            'file_name' => $filename,
            'file_path' => '/api/reports/' . $id . '/download',
            'expires_at' => now()->setTimestamp($createdAt + $this->retentionSeconds())->toIso8601String(),
        ];
    }

    private function managedPath(string $filename): ?string
    {
        $disk = $this->disk();
        $root = realpath($disk->path(''));
        $candidate = $disk->path($filename);
        // Only direct regular files on this local disk; never follow a file symlink.
        if ($root === false || is_link($candidate) || !is_file($candidate) || !is_readable($candidate)) {
            return null;
        }
        $resolved = realpath($candidate);
        return $resolved !== false && dirname($resolved) === $root ? $resolved : null;
    }

    public function read(string $id): ?string
    {
        if (!preg_match('/\A' . self::ID_PATTERN . '\z/D', $id)) return null;
        $path = $this->managedPath($id . '.pdf');
        if ($path === null) return null;
        clearstatcache(true, $path);
        $createdAt = @filemtime($path);
        if ($createdAt === false || $createdAt + $this->retentionSeconds() <= now()->getTimestamp()) return null;

        // Snapshot bytes before responding: pruning at the expiry boundary cannot
        // remove a BinaryFileResponse's file between controller and sendContent.
        $pdf = @file_get_contents($path);
        return is_string($pdf) && str_starts_with($pdf, '%PDF-') ? $pdf : null;
    }

    public function prune(): int
    {
        $disk = $this->disk();
        $deleted = 0;
        // Nonrecursive; unknown files and all historical/public directories are ignored.
        foreach ($disk->files() as $filename) {
            if (!preg_match('/\A' . self::ID_PATTERN . '\.(?:pdf|tmp)\z/D', $filename)) continue;
            $path = $this->managedPath($filename);
            if ($path === null) continue;
            clearstatcache(true, $path);
            $createdAt = @filemtime($path);
            if ($createdAt !== false && $createdAt + $this->retentionSeconds() <= now()->getTimestamp()) {
                if (!$disk->delete($filename)) throw new RuntimeException('Unable to prune report.');
                $deleted++;
            }
        }
        return $deleted;
    }
}
