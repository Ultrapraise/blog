<?php namespace WebEd\Plugins\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use WebEd\Plugins\Blog\Hook\RegisterDashboardStats;

class HookServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        add_action(WEBED_DASHBOARD_STATS, [RegisterDashboardStats::class, 'handle'], 25);
    }

    /**
     * Register any application services.s
     *
     * @return void
     */
    public function register()
    {

    }
}
