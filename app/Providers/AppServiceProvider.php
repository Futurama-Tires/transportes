<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;            
use Illuminate\Notifications\DatabaseNotification;
use App\Observers\DatabaseNotificationObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        DatabaseNotification::observe(DatabaseNotificationObserver::class);
    }
}
