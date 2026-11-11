<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use ZipArchive;

class MediaUploadRequest extends FormRequest
{
    /** @var array<string, list<string>> */
    private const MIME_BY_EXTENSION = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword', 'application/x-ole-storage'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls' => ['application/vnd.ms-excel', 'application/x-ole-storage'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'ppt' => ['application/vnd.ms-powerpoint', 'application/x-ole-storage'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'mp4' => ['video/mp4'],
    ];

    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->can('media.upload')) {
            return false;
        }

        return ! $this->boolean('create_documents')
            || ($user->can('documents.create') && $user->can('documents.upload') && $user->can('documents.edit'));
    }

    public function rules(): array
    {
        $upload = $this->file('file');
        $isVideo = $upload instanceof UploadedFile
            && strtolower($upload->getClientOriginalExtension()) === 'mp4';
        $maxKilobytes = $isVideo
            ? config('portal.media.max_video_upload_kb', 51200)
            : config('portal.media.max_upload_kb', 20480);

        return [
            'file' => ['required_without:files', 'missing_with:files', 'file', 'max:'.$maxKilobytes, 'extensions:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,mp4'],
            'files' => ['required_without:file', 'missing_with:file', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'max:'.config('portal.media.max_upload_kb', 20480), 'extensions:pdf,doc,docx,xls,xlsx,ppt,pptx'],
            'create_documents' => ['sometimes', 'accepted'],
            'document_category_id' => [Rule::requiredIf($this->boolean('create_documents')), 'nullable', 'integer', Rule::exists('document_categories', 'id')->whereNull('deleted_at')->where('is_active', true)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $uploads = $this->file('files') ?: [$this->file('file')];
            foreach ($uploads as $index => $upload) {
                $field = $this->hasFile('files') ? 'files.'.$index : 'file';
                $this->validateUpload($validator, $upload, $field);
            }
        });
    }

    private function validateUpload(Validator $validator, mixed $upload, string $field): void
    {
            if (! $upload instanceof UploadedFile || ! $upload->isValid()) {
                $validator->errors()->add($field, __('ui.media.invalid_file_type'));

                return;
            }

            $extension = strtolower($upload->getClientOriginalExtension());
            $mime = strtolower((string) $upload->getMimeType());

            if (! isset(self::MIME_BY_EXTENSION[$extension]) || ! in_array($mime, self::MIME_BY_EXTENSION[$extension], true)) {
                $validator->errors()->add($field, __('ui.media.invalid_file_type'));

                return;
            }

            if (in_array($extension, ['docx', 'xlsx', 'pptx'], true) && ! $this->isValidOpenXmlContainer($upload, $extension)) {
                $validator->errors()->add($field, __('ui.media.invalid_file_type'));

                return;
            }

            if (in_array($extension, ['doc', 'xls', 'ppt'], true) && ! $this->hasOleSignature($upload)) {
                $validator->errors()->add($field, __('ui.media.invalid_file_type'));

                return;
            }

            if ($extension === 'pdf' && ! str_starts_with((string) file_get_contents($upload->getRealPath(), false, null, 0, 5), '%PDF-')) {
                $validator->errors()->add($field, __('ui.media.invalid_file_type'));

                return;
            }

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $image = @getimagesize($upload->getRealPath());
                $maximum = (int) config('portal.media.max_image_dimension', 8000);

                if (! is_array($image)
                    || ($image['mime'] ?? null) !== $mime
                    || (int) $image[0] < 1
                    || (int) $image[1] < 1
                    || (int) $image[0] > $maximum
                    || (int) $image[1] > $maximum) {
                    $validator->errors()->add($field, __('ui.media.invalid_image_dimensions'));
                }
            }
    }

    private function isValidOpenXmlContainer(UploadedFile $upload, string $extension): bool
    {
        if (! class_exists(ZipArchive::class)) {
            return false;
        }

        $expectedPrefix = match ($extension) {
            'docx' => 'word/',
            'xlsx' => 'xl/',
            'pptx' => 'ppt/',
        };

        $zip = new ZipArchive();
        if ($zip->open($upload->getRealPath()) !== true) {
            return false;
        }

        $hasContentTypes = false;
        $hasOfficeContent = false;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);
            if (! is_string($name)) {
                continue;
            }

            $hasContentTypes = $hasContentTypes || $name === '[Content_Types].xml';
            $hasOfficeContent = $hasOfficeContent || str_starts_with($name, $expectedPrefix);
        }

        $zip->close();

        return $hasContentTypes && $hasOfficeContent;
    }

    private function hasOleSignature(UploadedFile $upload): bool
    {
        $signature = file_get_contents($upload->getRealPath(), false, null, 0, 8);

        return $signature === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
    }
}
