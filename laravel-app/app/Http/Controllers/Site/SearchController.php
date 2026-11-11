<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\Faq;
use App\Models\HomepageUpdate;
use App\Models\Notice;
use App\Models\Report;
use App\Models\TeamMember;
use App\Models\TrainingResource;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $term = trim($filters['q'] ?? '');
        $groups = [];

        if ($term !== '') {
            $groups = [
                ['title' => __('ui.site.notices_found'), 'results' => $this->search(Notice::query()->publiclyVisible(), $term, ['title_en', 'title_hi', 'description_en', 'description_hi'], 'notices_page', 'title', 'description', fn (Model $item): string => route('notices.show', $item))],
                ['title' => __('ui.site.announcements_found'), 'results' => $this->search(Announcement::query()->publiclyVisible(), $term, ['title_en', 'title_hi', 'description_en', 'description_hi'], 'announcements_page', 'title', 'description', fn (Model $item): string => route('announcements.show', $item))],
                ['title' => __('ui.site.documents_found'), 'results' => $this->search(Document::query()->publiclyVisible()->whereHas('category', fn ($query) => $query->where('is_active', true))->whereHas('file', fn ($query) => $query->where('visibility', 'public')), $term, ['title_en', 'title_hi', 'description_en', 'description_hi'], 'documents_page', 'title', 'description', fn (Model $item): string => route('documents.show', $item))],
                ['title' => __('ui.site.reports_found'), 'results' => $this->search(Report::query()->publiclyVisible()->whereHas('category', fn ($query) => $query->where('is_active', true))->whereHas('file', fn ($query) => $query->where('visibility', 'public')), $term, ['title_en', 'title_hi', 'description_en', 'description_hi'], 'reports_page', 'title', 'description', fn (Model $item): string => route('reports.show', $item))],
                ['title' => __('ui.site.training_found'), 'results' => $this->search(TrainingResource::query()->publiclyVisible()->whereHas('category', fn ($query) => $query->where('is_active', true))->where(fn ($query) => $query->whereNull('media_asset_id')->orWhereHas('file', fn ($file) => $file->where('visibility', 'public'))), $term, ['title_en', 'title_hi', 'description_en', 'description_hi'], 'training_page', 'title', 'description', fn (Model $item): string => route('training.show', $item))],
                ['title' => __('ui.site.team_found'), 'results' => $this->search(TeamMember::query()->visibleInDirectory(), $term, ['full_name_en', 'full_name_hi', 'designation_en', 'designation_hi', 'responsibilities_en', 'responsibilities_hi', 'office_block'], 'team_page', 'full_name', 'responsibilities', fn (Model $item): string => route('team.show', $item))],
                ['title' => __('ui.site.faqs_found'), 'results' => $this->search(Faq::query()->publiclyVisible()->whereHas('category', fn ($query) => $query->where('is_active', true))->with('category'), $term, ['question_en', 'question_hi', 'answer_en', 'answer_hi'], 'faqs_page', 'question', 'answer', fn (Model $item): string => route('faqs.index', ['q' => $term, 'category' => $item->category->slug]).'#faq-'.$item->getKey())],
                ['title' => __('ui.site.updates_found'), 'results' => $this->search(HomepageUpdate::query()->publiclyVisible(), $term, ['title_en', 'title_hi', 'summary_en', 'summary_hi'], 'updates_page', 'title', 'summary', fn (Model $item): ?string => \App\Support\SafeUrl::normalize($item->url))],
            ];
        }

        $hasResults = collect($groups)->contains(fn (array $group): bool => $group['results']->isNotEmpty());

        return view('site.search', compact('filters', 'term', 'groups', 'hasResults'));
    }

    /**
     * @param list<string> $fields
     * @param callable(Model): ?string $url
     * @return LengthAwarePaginator<int, array{title: string, summary: ?string, url: ?string, published_at: mixed}>
     */
    private function search(
        Builder $query,
        string $term,
        array $fields,
        string $pageName,
        string $titleField,
        string $summaryField,
        callable $url,
    ): LengthAwarePaginator {
        $query->where(function (Builder $search) use ($fields, $term): void {
            foreach ($fields as $index => $field) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $search->{$method}($field, 'like', '%'.$term.'%');
            }
        });

        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(5, ['*'], $pageName)
            ->withQueryString()
            ->through(fn (Model $item): array => [
                'title' => $item->localized($titleField) ?? '',
                'summary' => $item->localized($summaryField),
                'url' => $url($item),
                'published_at' => $item->published_at,
            ]);
    }
}
