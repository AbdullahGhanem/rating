<?php

namespace Ghanem\Rating;

use Illuminate\Support\ServiceProvider;

class RatingServiceProvider extends ServiceProvider 
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
	    $this->publishes([
	        __DIR__.'/../database/migrations/' => database_path('migrations')
	    ], 'migrations');
    }
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
