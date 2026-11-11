<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\MediaAsset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $diskName = (string) config('portal.media.disk', 'local');
        $disk = Storage::disk($diskName);

        foreach (DocumentCategory::query()->orderBy('display_order')->get() as $category) {
            $slug = 'demo-'.$category->slug;
            $filename = $slug.'.pdf';
            $storageKey = 'media/'.$filename;
            $sourcePath = database_path('seeders/files/'.$filename);
            if (! is_file($sourcePath)) {
                continue;
            }

            if (! $disk->exists($storageKey)) {
                $contents = file_get_contents($sourcePath);
                if (! is_string($contents)) {
                    throw new \RuntimeException("Unable to read demo document: {$sourcePath}");
                }
                $disk->put($storageKey, $contents);
            }

            $asset = MediaAsset::query()->firstOrCreate(
                ['storage_key' => $storageKey],
                [
                    'original_filename' => $filename,
                    'storage_disk' => $diskName,
                    'mime_type' => 'application/pdf',
                    'size_bytes' => $disk->size($storageKey),
                    'checksum_sha256' => hash_file('sha256', $disk->path($storageKey)),
                    'visibility' => 'private',
                ],
            );

            $document = Document::withTrashed()->firstOrNew(['slug' => $slug]);
            if (! $document->exists) {
                $document->fill([
                    'category_id' => $category->getKey(),
                    'media_asset_id' => $asset->getKey(),
                    'title_en' => '[DEMO DRAFT] '.$category->name_en.' sample document',
                    'title_hi' => '[डेमो ड्राफ़्ट] '.$category->name_hi.' नमूना दस्तावेज़',
                    'description_en' => 'Demonstration content only. The attached PDF is not an official government document. Replace it with verified, approved bilingual material before publication.',
                    'description_hi' => 'यह केवल प्रदर्शन हेतु सामग्री है। संलग्न पीडीएफ़ आधिकारिक शासकीय दस्तावेज़ नहीं है। प्रकाशित करने से पहले इसे सत्यापित और स्वीकृत द्विभाषी सामग्री से बदलें।',
                    'version' => '1.0',
                    'display_order' => 10,
                    'status' => 'draft',
                ])->save();
            } elseif ($document->trashed()) {
                $document->restore();
            }
        }
    }
}
