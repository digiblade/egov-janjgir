<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\TrainingCategory;
use App\Models\TrainingResource;
use App\Support\SafeUrl;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function documents(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $documents = Document::query()->publiclyVisible()
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->whereHas('file', fn ($q) => $q->where('visibility', 'public'))
            ->with(['category', 'file', 'versions.file' => fn ($q) => $q->where('visibility', 'public')])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($s) => $s->where('title_en', 'like', "%{$term}%")->orWhere('title_hi', 'like', "%{$term}%")->orWhere('description_en', 'like', "%{$term}%")->orWhere('description_hi', 'like', "%{$term}%")))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['type'] ?? null, fn ($q, $mime) => $q->whereHas('file', fn ($f) => $f->where('mime_type', $mime)))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('published_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('published_at', '<=', $date))
            ->orderBy('display_order')->orderByDesc('published_at')->paginate(12)->withQueryString();
        $categories = DocumentCategory::query()->where('is_active', true)->orderBy('display_order')->get();
        $types = Document::query()->publiclyVisible()->whereHas('file', fn ($q) => $q->where('visibility', 'public'))->join('media_assets', 'documents.media_asset_id', '=', 'media_assets.id')->distinct()->orderBy('media_assets.mime_type')->pluck('media_assets.mime_type');

        return view('site.documents.index', compact('documents', 'categories', 'types', 'filters'));
    }

    public function document(Document $document): View
    {
        abort_unless(Document::query()->publiclyVisible()->whereKey($document->getKey())->whereHas('category', fn ($q) => $q->where('is_active', true))->whereHas('file', fn ($q) => $q->where('visibility', 'public'))->exists(), 404);
        $document->load(['category', 'file', 'versions.file' => fn ($q) => $q->where('visibility', 'public')]);

        return view('site.documents.show', compact('document'));
    }

    public function training(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'category' => ['nullable', 'string', 'max:100'], 'type' => ['nullable', 'string', 'max:40']]);
        $resources = TrainingResource::query()->publiclyVisible()
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->where(fn ($q) => $q->whereNull('media_asset_id')->orWhereHas('file', fn ($f) => $f->where('visibility', 'public')))
            ->with(['category', 'file', 'thumbnail'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($s) => $s->where('title_en', 'like', "%{$term}%")->orWhere('title_hi', 'like', "%{$term}%")->orWhere('description_en', 'like', "%{$term}%")->orWhere('description_hi', 'like', "%{$term}%")))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('resource_type', $type))
            ->orderBy('display_order')->orderByDesc('training_date')->paginate(12)->withQueryString();
        $categories = TrainingCategory::query()->where('is_active', true)->orderBy('display_order')->get();
        $types = TrainingResource::query()->publiclyVisible()->distinct()->orderBy('resource_type')->pluck('resource_type');

        return view('site.training.index', compact('resources', 'categories', 'types', 'filters'));
    }

    public function resource(TrainingResource $trainingResource): View
    {
        abort_unless(TrainingResource::query()->publiclyVisible()->whereKey($trainingResource->getKey())->whereHas('category', fn ($q) => $q->where('is_active', true))->where(fn ($q) => $q->whereNull('media_asset_id')->orWhereHas('file', fn ($f) => $f->where('visibility', 'public')))->exists(), 404);
        $trainingResource->load(['category', 'file', 'thumbnail']);
        $externalUrl = SafeUrl::normalize($trainingResource->external_url);
        $videoEmbedUrl = $this->youtubeEmbedUrl($externalUrl);
        $thumbnailUrl = $trainingResource->thumbnail?->visibility === 'public' ? route('media.public', $trainingResource->thumbnail) : null;

        return view('site.training.show', compact('trainingResource', 'externalUrl', 'videoEmbedUrl', 'thumbnailUrl'));
    }

    public function faqs(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);
        $faqs = Faq::query()
            ->publiclyVisible()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with('category')
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(function ($search) use ($term): void {
                $search->where('question_en', 'like', '%'.$term.'%')
                    ->orWhere('question_hi', 'like', '%'.$term.'%')
                    ->orWhere('answer_en', 'like', '%'.$term.'%')
                    ->orWhere('answer_hi', 'like', '%'.$term.'%');
            }))
            ->when($filters['category'] ?? null, fn ($query, $slug) => $query->whereHas('category', fn ($category) => $category->where('slug', $slug)))
            ->orderBy('category_id')
            ->orderBy('display_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();
        $categories = FaqCategory::query()->where('is_active', true)->orderBy('display_order')->get();

        return view('site.faqs.index', compact('categories', 'faqs', 'filters'));
    }

    public function reports(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:120'], 'category' => ['nullable', 'string', 'max:100'], 'period' => ['nullable', 'string', 'max:100'], 'office' => ['nullable', 'string', 'max:120'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);
        $reports = Report::query()->publiclyVisible()->whereHas('category', fn ($q) => $q->where('is_active', true))->whereHas('file', fn ($q) => $q->where('visibility', 'public'))->with(['category', 'file'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($s) => $s->where('title_en', 'like', "%{$term}%")->orWhere('title_hi', 'like', "%{$term}%")->orWhere('description_en', 'like', "%{$term}%")->orWhere('description_hi', 'like', "%{$term}%")))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['period'] ?? null, fn ($q, $period) => $q->where('report_period', $period))
            ->when($filters['office'] ?? null, fn ($q, $office) => $q->where('office_block', $office))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('report_date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('report_date', '<=', $date))
            ->orderBy('display_order')->orderByDesc('published_at')->paginate(12)->withQueryString();
        $categories = ReportCategory::query()->where('is_active', true)->orderBy('display_order')->get();
        $publicReports = Report::query()->publiclyVisible()
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->whereHas('file', fn ($q) => $q->where('visibility', 'public'));
        $periods = (clone $publicReports)->whereNotNull('report_period')->distinct()->orderByDesc('report_period')->pluck('report_period');
        $offices = (clone $publicReports)->whereNotNull('office_block')->distinct()->orderBy('office_block')->pluck('office_block');

        return view('site.reports.index', compact('reports', 'categories', 'periods', 'offices', 'filters'));
    }

    public function report(Report $report): View
    {
        abort_unless(Report::query()->publiclyVisible()->whereKey($report->getKey())->whereHas('category', fn ($q) => $q->where('is_active', true))->whereHas('file', fn ($q) => $q->where('visibility', 'public'))->exists(), 404);
        $report->load(['category', 'file']);

        return view('site.reports.show', compact('report'));
    }

    private function youtubeEmbedUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $parts = parse_url($url);
        if (! is_array($parts) || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');
        $videoId = null;

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === '/watch') {
                parse_str((string) ($parts['query'] ?? ''), $query);
                $videoId = is_string($query['v'] ?? null) ? $query['v'] : null;
            } elseif (preg_match('~\A/(?:embed|shorts)/([A-Za-z0-9_-]{11})\z~', $path, $matches) === 1) {
                $videoId = $matches[1];
            }
        } elseif (in_array($host, ['youtu.be', 'www.youtu.be'], true)
            && preg_match('~\A/([A-Za-z0-9_-]{11})\z~', $path, $matches) === 1) {
            $videoId = $matches[1];
        } elseif (in_array($host, ['youtube-nocookie.com', 'www.youtube-nocookie.com'], true)
            && preg_match('~\A/embed/([A-Za-z0-9_-]{11})\z~', $path, $matches) === 1) {
            $videoId = $matches[1];
        }

        return is_string($videoId) && preg_match('/\A[A-Za-z0-9_-]{11}\z/', $videoId) === 1
            ? 'https://www.youtube-nocookie.com/embed/'.$videoId
            : null;
    }
}
