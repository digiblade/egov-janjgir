<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function __invoke(Request $request, MediaAsset $mediaAsset): StreamedResponse
    {
        abort_unless($mediaAsset->visibility === 'public', 404);
        abort_unless($mediaAsset->storage_disk === config('portal.media.disk', 'local'), 404);
        abort_unless($this->isSafeStorageKey($mediaAsset->storage_key), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($mediaAsset->storage_key), 404);

        $mimeType = strtolower($mediaAsset->mime_type);
        $inlineMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $isInline = in_array($mimeType, $inlineMimeTypes, true)
            || $mimeType === 'video/mp4'
            || ($mimeType === 'application/pdf' && $request->boolean('preview'));

        return $disk->response($mediaAsset->storage_key, $this->safeFilename($mediaAsset->original_filename), [
            'Content-Type' => $isInline ? $mimeType : 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=300',
        ], $isInline ? 'inline' : 'attachment');
    }

    private function isSafeStorageKey(string $key): bool
    {
        return str_starts_with($key, 'media/')
            && ! str_contains($key, '..')
            && ! str_contains($key, '\\')
            && preg_match('/[\x00-\x1f\x7f]/', $key) !== 1;
    }

    private function safeFilename(string $filename): string
    {
        $filename = basename(str_replace('\\', '/', $filename));
        $filename = preg_replace('/[\x00-\x1f\x7f]/u', '', $filename) ?? '';

        return mb_substr(trim($filename, " .\t\n\r\0\x0B") ?: 'download', 0, 255);
    }
}
