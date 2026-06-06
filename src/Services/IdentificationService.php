<?php

namespace Aware\CustomId\Services;

use Aware\CustomId\Exceptions\CustomIdGenerationException;
use InvalidArgumentException;

class IdentificationService
{
    /**
     * Generate a unique custom ID.
     *
     * @param  string  $modelType  The model type (for error messages)
     * @param  callable  $existsCallback  Callback to check if ID exists
     * @param  array|null  $config  Optional configuration override
     *                              ['length' => int, 'prefix' => string, 'character_set' => string, 'max_attempts' => int]
     *
     * @throws CustomIdGenerationException
     * @throws InvalidArgumentException
     */
    public function generate(string $modelType, callable $existsCallback, ?array $config = null): string
    {
        $length = $config['length'] ?? config('custom-id.default_length', 8);
        $prefix = $config['prefix'] ?? config('custom-id.default_prefix', '');
        $characterSet = $config['character_set'] ?? config('custom-id.character_set');
        $maxAttempts = $config['max_attempts'] ?? config('custom-id.max_attempts', 10);

        $this->validateConfig($length, $characterSet, $maxAttempts, $modelType);

        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $id = $this->generateRandomString($characterSet, $length);
            $fullId = $prefix.$id;

            if (! $existsCallback($fullId)) {
                return $fullId;
            }

            $attempts++;
        }

        throw new CustomIdGenerationException($modelType, $maxAttempts);
    }

    /**
     * Validate configuration values to prevent runtime errors.
     *
     * @throws InvalidArgumentException
     */
    protected function validateConfig(int $length, string $characterSet, int $maxAttempts, string $modelType): void
    {
        if ($length < 1) {
            throw new InvalidArgumentException(
                "ID length must be at least 1 for [{$modelType}], got [{$length}]."
            );
        }

        if (mb_strlen($characterSet) < 2) {
            throw new InvalidArgumentException(
                "Character set must contain at least 2 characters for [{$modelType}]."
            );
        }

        if ($maxAttempts < 1) {
            throw new InvalidArgumentException(
                "Max attempts must be at least 1 for [{$modelType}], got [{$maxAttempts}]."
            );
        }
    }

    /**
     * Generate a random string from the character set.
     */
    protected function generateRandomString(string $characterSet, int $length): string
    {
        $charactersLength = mb_strlen($characterSet);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= mb_substr($characterSet, random_int(0, $charactersLength - 1), 1);
        }

        return $randomString;
    }
}
