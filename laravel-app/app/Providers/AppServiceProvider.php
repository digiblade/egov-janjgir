<?php

namespace App\Providers;

use App\Models\User;
use App\Support\AdminNavigationBuilder;
use App\Support\PublicSiteSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole(config('portal.roles.super_admin', 'Super Admin')) ? true : null;
        });

        View::composer(['layouts.admin', 'admin.dashboard'], function ($view): void {
            $user = Auth::user();

            $view->with(
                'adminNavigation',
                $user instanceof User ? app(AdminNavigationBuilder::class)->for($user) : [],
            );
        });

        View::composer('layouts.public', function ($view): void {
            $view->with('publicSiteSettings', app(PublicSiteSettings::class)->all());
        });
    }
}
