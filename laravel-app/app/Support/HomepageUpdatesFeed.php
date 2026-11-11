<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Document;
use App\Models\HomepageUpdate;
use App\Models\Notice;
use App\Models\Report;
use App\Models\TrainingResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class HomepageUpdatesFeed
{
    /** @var array<string, class-string<Model>> */
    private const SOURCES = [
        'notices' => Notice::class,
        'documents' => Document::class,
        'reports' => Report::class,
        'training' => TrainingResource::class,
        'announcements' => Announcement::class,
    ];

    /**
     * @param list<string> $enabledSources
     * @return Collection<int, HomepageFeedItem>
     */
    public function latest(array $enabledSources, int $limit = 5): Collection
    {
        $items = collect();

        $manual = HomepageUpdate::query()
            ->publiclyVisible()
            ->orderByDesc('published_at')
            ->limit(25)
            ->get();

        foreach ($manual as $update) {
            $items->push(new HomepageFeedItem(
                title_en: $update->title_en,
                title_hi: $update->title_hi,
                summary_en: $update->summary_en,
                summary_hi: $update->summary_hi,
                url: SafeUrl::normalize($update->url),
                published_at: $update->published_at,
                source: 'manual',
                source_id: $update->getKey(),
            ));
        }

        foreach (array_values(array_intersect(array_unique($enabledSources), array_keys(self::SOURCES))) as $source) {
            $query = $this->sourceQuery($source);

            foreach ($query->orderByDesc('published_at')->limit(25)->get() as $record) {
                $items->push(new HomepageFeedItem(
                    title_en: (string) $record->title_en,
                    title_hi: (string) $record->title_hi,
                    summary_en: $record->description_en,
                    summary_hi: $record->description_hi,
                    url: route($this->routeFor($source), $record),
                    published_at: $record->published_at,
                    source: $source,
                    source_id: $record->getKey(),
                ));
            }
        }

        return $items
            ->sortByDesc(fn (HomepageFeedItem $item): int => $item->published_at?->getTimestamp() ?? 0)
            ->unique(fn (HomepageFeedItem $item): string => $this->dedupeKey($item))
            ->take(max(0, $limit))
            ->values();
    }

    private function sourceQuery(string $source): Builder
    {
        $model = self::SOURCES[$source];
        $query = $model::query()->publiclyVisible();

        return match ($source) {
            'documents' => $query
                ->whereHas('category', fn (Builder $category): Builder => $category->where('is_active', true))
                ->whereHas('file', fn (Builder $file): Builder => $file->where('visibility', 'public')),
            'reports' => $query
                ->whereHas('category', fn (Builder $category): Builder => $category->where('is_active', true))
                ->whereHas('file', fn (Builder $file): Builder => $file->where('visibility', 'public')),
            'training' => $query
                ->whereHas('category', fn (Builder $category): Builder => $category->where('is_active', true))
                ->where(fn (Builder $resource): Builder => $resource
                    ->whereNull('media_asset_id')
                    ->orWhereHas('file', fn (Builder $file): Builder => $file->where('visibility', 'public'))),
            default => $query,
        };
    }

    private function routeFor(string $source): string
    {
        return match ($source) {
            'notices' => 'notices.show',
            'documents' => 'documents.show',
            'reports' => 'reports.show',
            'training' => 'training.show',
            'announcements' => 'announcements.show',
        };
    }

    private function dedupeKey(HomepageFeedItem $item): string
    {
        if ($item->url !== null) {
            $parts = parse_url($item->url);
            if (is_array($parts)) {
                $host = isset($parts['host']) && strtolower($parts['host']) !== strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST))
                    ? strtolower($parts['host'])
                    : '';

                return 'url:'.$host.($parts['path'] ?? '').(isset($parts['query']) ? '?'.$parts['query'] : '');
            }
        }

        $title = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $item->title_en ?: $item->title_hi) ?? ''));
        $summary = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $item->summary_en ?: $item->summary_hi) ?? ''));

        return 'text:'.hash('sha256', $title.'|'.$summary.'|'.($item->published_at?->toDateString() ?? ''));
    }
}
