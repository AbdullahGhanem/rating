<?php

namespace Ghanem\Rating;

use Illuminate\Support\ServiceProvider;

class RatingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__ . '/database/migrations' => database_path('migrations'),
            ]);
        }
    }

    public function register(): void
    {
        //
    }
}
