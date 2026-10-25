<?php

namespace App\Providers;
use App\Services\GeneralService;
use Cloudinary\Cloudinary;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(GeneralService::class, function ($app) {
            return new GeneralService();
        });

        // Same configuration cloudinary-labs/cloudinary-laravel used: the CLOUDINARY_URL.
        $this->app->singleton(Cloudinary::class, function ($app) {
            return new Cloudinary($app['config']->get('services.cloudinary.url'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
