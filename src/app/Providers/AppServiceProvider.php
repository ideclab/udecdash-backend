<?php

namespace App\Providers;

use App\Models\AccessCode;
use App\Models\AttachmentInteraction;
use App\Models\User;
use App\Observers\AccessCodeObserver;
use App\Observers\AttachmentObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        AttachmentInteraction::observe(AttachmentObserver::class);
        AccessCode::observe(AccessCodeObserver::class);
    }
}
