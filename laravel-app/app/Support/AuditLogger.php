<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /** @var list<string> */
    private const MODULES = [
        'about', 'announcements', 'authentication', 'banners', 'contact', 'documents', 'faqs',
        'gallery', 'homepage', 'kpis', 'media', 'notices', 'reports', 'roles',
        'settings', 'support', 'system', 'team', 'training', 'users',
    ];

    /** @return list<string> */
    public static function modules(): array
    {
        return self::MODULES;
    }

    /**
     * Record an action without persisting field values or request identifiers.
     * Anonymous records are intended only for failed sign-in attempts.
     *
     * @param list<string> $changedFields
     */
    public function record(
        string $action,
        ?Model $subject = null,
        array $changedFields = [],
        ?int $actorId = null,
        bool $allowAnonymous = false,
    ): ?AuditLog {
        $actorId ??= Auth::id();

        if ($actorId === null && ! $allowAnonymous) {
            return null;
        }

        $fields = collect($changedFields)
            ->filter(fn (mixed $field): bool => is_string($field) && preg_match('/\A[a-zA-Z0-9_.-]{1,80}\z/', $field) === 1)
            ->map(fn (string $field): string => strtolower($field))
            ->reject(fn (string $field): bool => collect([
                'password', 'remember', 'session', 'setup_key', 'storage_key', 'checksum',
                'file', 'token', 'secret', 'credential', 'private_key',
            ])->contains(fn (string $sensitive): bool => str_contains($field, $sensitive)))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return AuditLog::query()->create([
            'actor_id' => $actorId,
            'action' => substr($action, 0, 120),
            'module' => $this->moduleFor($action, $subject),
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject ? (string) $subject->getKey() : null,
            'changed_fields' => $fields,
            'occurred_at' => now(),
        ]);
    }

    private function moduleFor(string $action, ?Model $subject): string
    {
        $prefix = strtolower((string) str($action)->before('.'));
        $module = match ($prefix) {
            'auth', 'setup' => 'authentication',
            'role' => 'roles',
            'faq' => 'faqs',
            'department' => 'homepage',
            default => $prefix,
        };

        if (in_array($module, self::MODULES, true) && $module !== 'system') {
            return $module;
        }

        $subjectModule = $subject ? match (class_basename($subject)) {
            'Announcement' => 'announcements',
            'AboutSection' => 'about',
            'Banner' => 'banners',
            'ContactDetail' => 'contact',
            'DepartmentInformation', 'HomepageSection', 'HomepageUpdate', 'QuickLink' => 'homepage',
            'Document', 'DocumentCategory', 'DocumentVersion' => 'documents',
            'Faq', 'FaqCategory' => 'faqs',
            'GalleryAlbum', 'GalleryCategory', 'GalleryImage' => 'gallery',
            'Kpi' => 'kpis',
            'MediaAsset' => 'media',
            'Notice' => 'notices',
            'Report', 'ReportCategory' => 'reports',
            'Role' => 'roles',
            'SiteSetting' => 'settings',
            'SupportRequest' => 'support',
            'TeamCategory', 'TeamMember' => 'team',
            'TrainingCategory', 'TrainingResource' => 'training',
            'User' => 'users',
            default => null,
        } : null;

        return $subjectModule ?? 'system';
    }
}
