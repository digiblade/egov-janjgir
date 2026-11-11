<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class MediaImageOptimizer
{
    /**
     * Re-encode supported images to remove embedded metadata and reduce size.
     * Returns a temporary path only when GD produced a smaller valid output.
     */
    public function optimize(UploadedFile $upload): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $mime = strtolower((string) $upload->getMimeType());
        [$createFunction, $writeFunction] = match ($mime) {
            'image/jpeg' => ['imagecreatefromjpeg', 'imagejpeg'],
            'image/png' => ['imagecreatefrompng', 'imagepng'],
            'image/webp' => ['imagecreatefromwebp', 'imagewebp'],
            default => [null, null],
        };

        if (! is_string($createFunction) || ! is_string($writeFunction)
            || ! function_exists($createFunction) || ! function_exists($writeFunction)) {
            return null;
        }

        $source = @$createFunction($upload->getRealPath());
        if (! $source instanceof \GdImage) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $maxDimension = max(1, (int) config('portal.media.max_optimized_dimension', 2400));
        $scale = min(1, $maxDimension / max($sourceWidth, $sourceHeight));
        $width = max(1, (int) floor($sourceWidth * $scale));
        $height = max(1, (int) floor($sourceHeight * $scale));
        $optimized = imagecreatetruecolor($width, $height);

        if (! $optimized instanceof \GdImage) {
            imagedestroy($source);

            return null;
        }

        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($optimized, false);
            imagesavealpha($optimized, true);
            $transparent = imagecolorallocatealpha($optimized, 0, 0, 0, 127);
            imagefilledrectangle($optimized, 0, 0, $width, $height, $transparent);
        } else {
            $white = imagecolorallocate($optimized, 255, 255, 255);
            imagefilledrectangle($optimized, 0, 0, $width, $height, $white);
        }

        imagecopyresampled($optimized, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        $temporaryPath = tempnam(sys_get_temp_dir(), 'egov-media-');
        if (! is_string($temporaryPath)) {
            imagedestroy($optimized);
            imagedestroy($source);

            return null;
        }

        $written = match ($mime) {
            'image/jpeg' => @$writeFunction($optimized, $temporaryPath, 84),
            'image/png' => @$writeFunction($optimized, $temporaryPath, 6),
            'image/webp' => @$writeFunction($optimized, $temporaryPath, 84),
            default => false,
        };

        imagedestroy($optimized);
        imagedestroy($source);

        if (! $written || ! is_file($temporaryPath) || filesize($temporaryPath) >= (int) $upload->getSize()) {
            @unlink($temporaryPath);

            return null;
        }

        return $temporaryPath;
    }
}
