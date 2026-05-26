<?php

namespace App\Http\Controllers;

use App\Support\MediaPath;
use App\Support\MediaStorage;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    private const CACHE_CONTROL = 'public, max-age=31536000, s-maxage=31536000, immutable';

    public function show(Request $request, string $path)
    {
        $path = MediaPath::normalize($path);

        abort_unless($path !== null && MediaPath::isManagedPath($path), 404);
        abort_unless(MediaStorage::exists($path), 404);

        $stream = MediaStorage::readStream($path);
        $mimeType = MediaStorage::mimeType($path) ?: 'application/octet-stream';
        $lastModified = MediaStorage::lastModified($path);
        $etag = '"'.sha1($path.'|'.$lastModified).'"';

        if ($request->headers->get('if-none-match') === $etag) {
            return response('', 304, [
                'ETag' => $etag,
                'Cache-Control' => self::CACHE_CONTROL,
            ]);
        }

        $response = response()->stream(function () use ($stream): void {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => self::CACHE_CONTROL,
            'ETag' => $etag,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);

        if (is_int($lastModified) && $lastModified > 0) {
            $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified).' GMT');
        }

        return $response;
    }
}
