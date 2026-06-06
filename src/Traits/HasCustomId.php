<?php

namespace Aware\CustomId\Traits;

use Aware\CustomId\Exceptions\CustomIdGenerationException;
use Aware\CustomId\Services\IdentificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\SoftDeletes;

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
     * Initialize the trait.
     */
    public function initializeHasCustomId(): void
    {
        $this->incrementing = false;
        $this->keyType = 'string';
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
     * Perform a model insert operation with retry for concurrent insert race conditions.
     */
    protected function performInsert(\Illuminate\Database\Eloquent\Builder $query): bool
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return parent::performInsert($query);
            } catch (QueryException $e) {
                if (!$this->isUniqueConstraintError($e)) {
                    throw $e;
                }

                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw new CustomIdGenerationException($this->getCustomIdType(), $maxRetries);
                }

                // Reset state so the creating event can re-run and regenerate the ID
                $this->exists = false;
                $this->wasRecentlyCreated = false;
                $this->{$this->getKeyName()} = null;
            }
        }

        return false;
    }

    /**
     * Determine if a query exception is due to a unique constraint violation.
     */
    protected function isUniqueConstraintError(QueryException $e): bool
    {
        return str_contains(strtoupper($e->getMessage()), 'UNIQUE') ||
            str_contains(strtoupper($e->getMessage()), 'DUPLICATE');
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
        if (trait_uses_recursive(static::class) !== null && in_array(SoftDeletes::class, trait_uses_recursive(static::class))) {
            return static::withTrashed()->where($this->getKeyName(), $id)->exists();
        }

        return static::where($this->getKeyName(), $id)->exists();
    }

}
