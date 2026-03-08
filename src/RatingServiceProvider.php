<?php

namespace Ghanem\Rating;

use Illuminate\Support\ServiceProvider;

class RatingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/rating.php' => config_path('rating.php'),
            ], 'rating-config');

            $this->publishesMigrations([
                __DIR__ . '/database/migrations' => database_path('migrations'),
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/rating.php', 'rating');
    }
}
