<?php

namespace Aware\CustomId\Traits;

use Aware\CustomId\Exceptions\CustomIdGenerationException;
use Aware\CustomId\Services\IdentificationService;
use Illuminate\Database\Eloquent\Model;

trait HasCustomId
{
    /**
     * Boot the trait.
     */
    protected static function bootHasCustomId(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = $model->generateCustomIdWithRetry();
            }
        });
    }

    /**
     * Get the model type for ID generation.
     * Override this method if your config key differs from the class name.
     */
    protected function getCustomIdType(): string
    {
        return strtolower(class_basename(static::class));
    }

    /**
     * Get the custom ID configuration for this model.
     * Override this method to provide model-specific configuration.
     *
     * @return array|null Configuration array with keys: length, prefix, character_set, max_attempts
     */
    protected function getCustomIdConfig(): ?array
    {
        return null;
    }

    /**
     * Generate a custom ID with retry logic for race condition handling.
     *
     * @throws CustomIdGenerationException
     */
    protected function generateCustomIdWithRetry(): string
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return $this->generateCustomId();
            } catch (CustomIdGenerationException $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
            }
        }

        // This should never be reached, but satisfies static analysis
        throw new CustomIdGenerationException($this->getCustomIdType(), $maxRetries);
    }

    /**
     * Generate a unique custom ID for this model.
     *
     * @throws CustomIdGenerationException
     */
    protected function generateCustomId(): string
    {
        $service = app(IdentificationService::class);

        return $service->generate(
            $this->getCustomIdType(),
            fn (string $id) => $this->customIdExists($id),
            $this->getCustomIdConfig()
        );
    }

    /**
     * Check if a custom ID already exists.
     * Includes soft-deleted records if the model uses SoftDeletes.
     */
    protected function customIdExists(string $id): bool
    {
        // Include soft-deleted records if model uses SoftDeletes
        if (method_exists(static::class, 'withTrashed')) {
            return static::withTrashed()->where($this->getKeyName(), $id)->exists();
        }

        return static::where($this->getKeyName(), $id)->exists();
    }

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the data type of the primary key.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
