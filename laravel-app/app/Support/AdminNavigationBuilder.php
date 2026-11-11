<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

class AdminNavigationBuilder
{
    /**
     * Return only registered destinations the user can view. Server-side route
     * middleware remains the access-control boundary for every destination.
     *
     * @return list<array{key: string, label: string, route: string, url: string, active_pattern: string}>
     */
    public function for(User $user): array
    {
        $items = [
            ['key' => 'dashboard', 'permission' => 'dashboard.view', 'label' => 'dashboard', 'route' => 'admin.dashboard'],
            ['key' => 'homepage', 'permission' => 'homepage.view', 'label' => 'homepage', 'route' => 'admin.homepage.index'],
            ['key' => 'about-sections', 'permission' => 'about.view', 'label' => 'about_sections', 'route' => 'admin.about-sections.index'],
            ['key' => 'banners', 'permission' => 'banners.view', 'label' => 'banners', 'route' => 'admin.banners.index'],
            ['key' => 'kpis', 'permission' => 'kpis.view', 'label' => 'kpis', 'route' => 'admin.kpis.index'],
            ['key' => 'notices', 'permission' => 'notices.view', 'label' => 'notices', 'route' => 'admin.notices.index'],
            ['key' => 'announcements', 'permission' => 'announcements.view', 'label' => 'announcements', 'route' => 'admin.announcements.index'],
            ['key' => 'documents', 'permission' => 'documents.view', 'label' => 'documents', 'route' => 'admin.documents.index'],
            ['key' => 'training', 'permission' => 'training.view', 'label' => 'training', 'route' => 'admin.training.index'],
            ['key' => 'faqs', 'permission' => 'faqs.view', 'label' => 'faqs', 'route' => 'admin.faqs.index'],
            ['key' => 'team', 'permission' => 'team.view', 'label' => 'team', 'route' => 'admin.team.index'],
            ['key' => 'reports', 'permission' => 'reports.view', 'label' => 'reports', 'route' => 'admin.reports.index'],
            ['key' => 'gallery', 'permission' => 'gallery.view', 'label' => 'gallery', 'route' => 'admin.gallery.index'],
            ['key' => 'media', 'permission' => 'media.view', 'label' => 'media', 'route' => 'admin.media.index'],
            ['key' => 'contact', 'permission' => 'contact.view', 'label' => 'contact', 'route' => 'admin.contact.index'],
            ['key' => 'support', 'permission' => 'support.view', 'label' => 'support', 'route' => 'admin.support.index'],
            ['key' => 'users', 'permission' => 'users.view', 'label' => 'users', 'route' => 'admin.users.index'],
            ['key' => 'roles', 'permission' => 'roles.view', 'label' => 'roles', 'route' => 'admin.roles.index'],
            ['key' => 'settings', 'permission' => 'settings.view', 'label' => 'settings', 'route' => 'admin.settings.index'],
            ['key' => 'audit-logs', 'permission' => 'audit-logs.view', 'label' => 'audit_logs', 'route' => 'admin.audit-logs.index'],
        ];

        $visibleItems = [];

        foreach ($items as $item) {
            if (! $user->can($item['permission']) || ! Route::has($item['route'])) {
                continue;
            }

            $visibleItems[] = [
                'key' => $item['key'],
                'label' => $item['label'],
                'route' => $item['route'],
                'url' => route($item['route']),
                'active_pattern' => $item['key'] === 'dashboard' ? $item['route'] : "admin.{$item['key']}.*",
            ];
        }

        return $visibleItems;
    }
}
