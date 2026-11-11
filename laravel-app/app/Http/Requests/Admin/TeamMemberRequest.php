<?php

namespace App\Http\Requests\Admin;

use App\Models\TeamMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamMemberRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('slug'))) {
            $slug = trim($this->input('slug'));
            $this->merge(['slug' => $slug === '' ? null : Str::slug($slug)]);
        }

        if (! $this->exists('is_active')) {
            $this->merge(['is_active' => false]);
        }

        foreach (['email_public', 'contact_number_public'] as $publicationField) {
            if (! $this->exists($publicationField)) {
                $this->merge([$publicationField => false]);
            }
        }

        if ($this->input('status') === 'published' && ! $this->filled('published_at')) {
            $member = $this->route('teamMember');
            $publishedAt = $member?->status === 'published' && $member->published_at
                ? $member->published_at->format('Y-m-d\\TH:i')
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
        $member = $this->route('teamMember');
        $categoryRule = Rule::exists('team_categories', 'id')->whereNull('deleted_at');
        if (! $member || (int) $this->input('team_category_id') !== (int) $member->team_category_id) {
            $categoryRule->where('is_active', true);
        }

        $rules = [
            'slug' => ['nullable', 'string', 'alpha_dash', 'max:180', Rule::unique('team_members', 'slug')->ignore($member?->id)],
            'full_name_en' => ['required', 'string', 'max:255'],
            'full_name_hi' => ['required', 'string', 'max:255'],
            'designation_en' => ['required', 'string', 'max:255'],
            'designation_hi' => ['required', 'string', 'max:255'],
            'team_category_id' => ['required', 'integer', $categoryRule],
            'photograph_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('visibility', 'public')->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])],
            'registration_number' => ['nullable', 'string', 'max:120'],
            'office_block' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:254'],
            'email_public' => ['required', 'boolean'],
            'contact_number' => ['nullable', 'string', 'regex:/\A[0-9+(). -]{3,32}\z/'],
            'contact_number_public' => ['required', 'boolean'],
            'responsibilities_en' => ['nullable', 'string', 'max:20000'],
            'responsibilities_hi' => ['nullable', 'string', 'max:20000'],
            'display_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['required', 'boolean'],
            'status' => ['required', Rule::in(TeamMember::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
        ];

        if ($this->input('status') === 'scheduled') {
            $rules['published_at'] = ['required', 'date', 'after:now'];
        }
        if ($this->filled('published_at')) {
            $rules['expires_at'][] = 'after_or_equal:published_at';
        }

        return $rules;
    }
}
