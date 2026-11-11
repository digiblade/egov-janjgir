<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FaqManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', Faq::STATUSES)],
            'category' => ['nullable', 'integer', 'exists:faq_categories,id'],
        ]);
        $faqs = Faq::query()->with(['category', 'lastEditor'])
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('question_en', 'like', '%'.$term.'%')
                ->orWhere('question_hi', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderBy('display_order')->orderBy('id')->paginate(15)->withQueryString();
        $categories = FaqCategory::query()->orderBy('display_order')->orderBy('name_en')->get();

        return view('admin.faqs.index', compact('faqs', 'categories', 'filters'));
    }

    public function create(): View
    {
        return view('admin.faqs.form', [
            'faq' => new Faq(['status' => 'draft']),
            'categories' => FaqCategory::query()->where('is_active', true)->orderBy('display_order')->get(),
            'isEditing' => false,
        ]);
    }

    public function store(FaqRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $this->ensurePublishPermission($request, $attributes['status']);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $faq = Faq::query()->create($attributes);
        $faq->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return to_route('admin.faqs.edit', $faq)->with('status', __('ui.admin_content.saved'));
    }

    public function edit(Faq $faq): View
    {
        $categories = FaqCategory::query()
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $faq->category_id))
            ->orderBy('display_order')->get();

        return view('admin.faqs.form', compact('faq', 'categories') + ['isEditing' => true]);
    }

    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $attributes = $request->validated();
        $this->ensurePublishPermission($request, $attributes['status'], $faq->status, $attributes, $faq);
        $attributes['published_at'] = $this->publicationDate($attributes['status'], $attributes['published_at'] ?? null);
        $faq->update($attributes);
        $faq->forceFill(['updated_by' => $request->user()->id])->save();

        return to_route('admin.faqs.edit', $faq)->with('status', __('ui.admin_content.saved'));
    }

    public function destroy(Request $request, Faq $faq): RedirectResponse
    {
        $faq->forceFill(['updated_by' => $request->user()->id])->save();
        $faq->delete();

        return to_route('admin.faqs.index')->with('status', __('ui.admin_content.deleted'));
    }

    private function publicationDate(string $status, mixed $publishedAt): mixed
    {
        return $status === 'published' && ! $publishedAt ? now() : $publishedAt;
    }

    private function ensurePublishPermission(Request $request, string $status, ?string $currentStatus = null, array $attributes = [], ?Faq $current = null): void
    {
        $publicationTransition = $currentStatus === null
            ? in_array($status, ['published', 'scheduled'], true)
            : (($status !== $currentStatus && (in_array($status, ['published', 'scheduled'], true) || in_array($currentStatus, ['published', 'scheduled'], true)))
                || (in_array($status, ['published', 'scheduled'], true) && $current && (
                    $this->dateMinute($attributes['published_at'] ?? null) !== $this->dateMinute($current->published_at)
                    || $this->dateMinute($attributes['expires_at'] ?? null) !== $this->dateMinute($current->expires_at)
                )));

        if ($publicationTransition) {
            abort_unless($request->user()->can('faqs.publish'), 403);
        }
    }

    private function dateMinute(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i') : null;
    }
}
