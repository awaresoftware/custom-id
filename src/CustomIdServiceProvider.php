<?php

namespace Aware\CustomId;

use Aware\CustomId\Services\IdentificationService;
use Illuminate\Support\ServiceProvider;

class CustomIdServiceProvider extends ServiceProvider
{
    /**
     * Cached migration timestamp, computed once per lifecycle.
     */
    private ?string $migrationTimestamp = null;

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/custom-id.php',
            'custom-id'
        );

        $this->app->singleton(IdentificationService::class, function ($app) {
            return new IdentificationService();
        });
    }

    public function boot(): void
    {
        $configPath = __DIR__.'/../config/custom-id.php';
        $migrationPath = __DIR__.'/../database/migrations/convert_users_table_to_custom_id.php.stub';

        $configPublish = [
            $configPath => config_path('custom-id.php'),
        ];

        $migrationPublish = [
            $migrationPath => database_path(
                'migrations/'.$this->migrationTimestamp().'_convert_users_table_to_custom_id.php'
            ),
        ];

        $this->publishes($configPublish, 'custom-id-config');
        $this->publishes($migrationPublish, 'custom-id-users-migration');
        $this->publishes(array_merge($configPublish, $migrationPublish), 'custom-id');
    }

    public function provides(): array
    {
        return [IdentificationService::class];
    }

    /**
     * Get the migration timestamp, computed once.
     */
    private function migrationTimestamp(): string
    {
        if ($this->migrationTimestamp === null) {
            $this->migrationTimestamp = date('Y_m_d_His');
        }

        return $this->migrationTimestamp;
    }
}
