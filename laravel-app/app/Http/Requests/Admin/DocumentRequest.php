<?php

namespace App\Http\Requests\Admin;

use App\Models\Document;
use App\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $slug = trim($this->input('slug'));
            $this->merge(['slug' => $slug === '' ? null : Str::slug($slug)]);
        }

        if ($this->input('status') === 'published' && ! $this->filled('published_at')) {
            $document = $this->route('document');
            $publishedAt = $document?->status === 'published' && $document->published_at
                ? $document->published_at->format('Y-m-d\\TH:i')
                : now()->format('Y-m-d\\TH:i');
            $this->merge(['published_at' => $publishedAt]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $document = $this->route('document');
        $categoryRule = Rule::exists('document_categories', 'id')->whereNull('deleted_at');
        if (! $document || (int) $this->input('category_id') !== (int) $document->category_id) {
            $categoryRule->where('is_active', true);
        }
        $rules = [
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:180', Rule::unique('documents', 'slug')->ignore($document?->id)],
            'category_id' => ['required', 'integer', $categoryRule],
            'media_asset_id' => ['required', 'integer', function (string $attribute, mixed $value, \Closure $fail): void {
                $allowedMimes = ['application/pdf', 'application/msword', 'application/x-ole-storage', 'application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                $asset = MediaAsset::query()->find($value);
                $document = $this->route('document');
                $nonPublicStatus = $this->input('status') === 'draft';
                $isPublic = $asset?->visibility === 'public';
                $isOwnedPrivateDraft = $asset?->visibility === 'private'
                    && $nonPublicStatus
                    && ((int) $asset->uploaded_by === (int) $this->user()?->id || $this->user()?->can('media.view_private'));
                $isExistingPrivateDraft = $document instanceof Document
                    && $document->status === 'draft'
                    && $nonPublicStatus
                    && (int) $document->media_asset_id === (int) $value
                    && $asset?->visibility === 'private';

                if (! $asset
                    || $asset->storage_disk !== config('portal.media.disk', 'local')
                    || ! in_array($asset->mime_type, $allowedMimes, true)
                    || (! $isPublic && ! $isOwnedPrivateDraft && ! $isExistingPrivateDraft)) {
                    $fail(__('ui.admin_content.invalid_document_file'));
                }
            }],
            'title_en' => ['required', 'string', 'max:255'],
            'title_hi' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:50000'],
            'description_hi' => ['nullable', 'string', 'max:50000'],
            'version' => ['required', 'string', 'max:50'],
            'reference_number' => ['nullable', 'string', 'max:150'],
            'issuing_authority' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(Document::STATUSES)],
            'version_change_notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($this->input('status') === 'scheduled') {
            $rules['published_at'] = ['required', 'date', 'after:now'];
        } elseif ($this->input('status') === 'published') {
            $rules['published_at'][] = 'before_or_equal:now';
        }
        if ($this->filled('published_at')) {
            $rules['expires_at'][] = 'after_or_equal:published_at';
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $document = $this->route('document');
            if (! $document || $validator->errors()->isNotEmpty()) {
                return;
            }

            $versionChanged = (string) $this->input('version') !== (string) $document->version;
            $fileChanged = (int) $this->input('media_asset_id') !== (int) $document->media_asset_id;
            if ($versionChanged !== $fileChanged) {
                $validator->errors()->add('version', __('ui.admin_content.version_file_together'));
            }
        });
    }
}
