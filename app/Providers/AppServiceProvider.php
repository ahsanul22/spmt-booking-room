<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        view()->composer([
            'layouts.schedule',
            'layouts.navigation',
            'pic.rooms.index',
            'shared.dashboard-shortcuts',
        ], function ($view) {
            $user = auth()->user();
            $count = 0;
            if ($user && $user->can('access-pic')) {
                try {
                    $count = $user->pendingApprovalsCount();
                } catch (\Throwable) {
                    $count = 0;
                }
            }
            $view->with('pendingApprovalsCount', $count);
        });
    }
}
