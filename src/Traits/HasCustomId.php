<?php

namespace Aware\CustomId\Traits;

use Aware\CustomId\Exceptions\CustomIdGenerationException;
use Aware\CustomId\Services\IdentificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

trait HasCustomId
{
    /**
     * Boot the trait.
     *
     * Sets the model key before creation. Skips if another listener
     * already set a non-empty key.
     */
    protected static function bootHasCustomId(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = $model->generateCustomId();
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
     * Generate a unique custom ID for this model.
     *
     * The service handles collision detection with existing records (both
     * active and soft-deleted). Race conditions from concurrent inserts are
     * handled in {@see performInsert}.
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
     * Check if a custom ID already exists, including soft-deleted records.
     */
    protected function customIdExists(string $id): bool
    {
        if (method_exists(static::class, 'withTrashed')) {
            return static::withTrashed()->where($this->getKeyName(), $id)->exists();
        }

        return static::where($this->getKeyName(), $id)->exists();
    }

    /**
     * Perform the actual insert, retrying on unique constraint violations
     * to handle race conditions from concurrent inserts.
     *
     * @throws UniqueConstraintViolationException
     */
    protected function performInsert(Builder $query): bool
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return parent::performInsert($query);
            } catch (UniqueConstraintViolationException $e) {
                $attempt++;
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                $this->{$this->getKeyName()} = $this->generateCustomId();
            }
        }

        return false;
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
