<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
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

        View::composer('*', function ($view) {
            $view->with('user', Auth::user());
        });

        View::composer('*', function ($view) {
            if (Auth::check()) {
                $view->with('company', CompanySetting::where('company_uid', Auth::user()->company_uid)->first());
            }
        });
    }
}
