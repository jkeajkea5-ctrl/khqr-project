<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaStorage
{
    public static function disk(): string
    {
        $disk = trim((string) config('filesystems.media_disk', 'public'));

        return $disk !== '' ? $disk : 'public';
    }

    public static function store(?UploadedFile $file, string $directory): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store($directory, self::disk());
    }

    public static function delete(mixed $path): void
    {
        $normalizedPath = MediaPath::normalize($path);

        if ($normalizedPath === null) {
            return;
        }

        Storage::disk(self::disk())->delete($normalizedPath);
    }

    public static function url(string $path): string
    {
        $normalizedPath = ltrim($path, '/');
        $disk = self::disk();
        $driver = (string) config("filesystems.disks.{$disk}.driver", 'local');

        if ($driver === 'local' && $disk === 'public') {
            $prefix = filled(env('VERCEL')) ? 'vercel-storage/' : 'storage/';

            return asset($prefix.$normalizedPath);
        }

        return route('media.show', ['path' => $normalizedPath]);
    }

    public static function exists(string $path): bool
    {
        return Storage::disk(self::disk())->exists($path);
    }

    public static function readStream(string $path)
    {
        return Storage::disk(self::disk())->readStream($path);
    }

    public static function mimeType(string $path): ?string
    {
        return Storage::disk(self::disk())->mimeType($path);
    }

    public static function lastModified(string $path): ?int
    {
        try {
            return Storage::disk(self::disk())->lastModified($path);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function putFromFile(string $path, string $sourcePath): void
    {
        $stream = fopen($sourcePath, 'rb');

        if ($stream === false) {
            return;
        }

        try {
            Storage::disk(self::disk())->put($path, $stream);
        } finally {
            fclose($stream);
        }
    }
}
