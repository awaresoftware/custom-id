<?php

namespace Aware\CustomId;

use Aware\CustomId\Services\IdentificationService;
use Illuminate\Support\ServiceProvider;

class CustomIdServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/custom-id.php',
            'custom-id'
        );

        // Register IdentificationService as singleton
        $this->app->singleton(IdentificationService::class, function ($app) {
            return new IdentificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/custom-id.php' => config_path('custom-id.php'),
        ], 'custom-id-config');

        // Publish users migration (optional)
        $this->publishes([
            __DIR__.'/../database/migrations/convert_users_table_to_custom_id.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_convert_users_table_to_custom_id.php'),
        ], 'custom-id-users-migration');
    }
}
