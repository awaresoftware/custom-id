<?php

namespace Aware\CustomId\Services;

use Aware\CustomId\Exceptions\CustomIdGenerationException;

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
     */
    public function generate(string $modelType, callable $existsCallback, ?array $config = null): string
    {
        $length = $config['length'] ?? config('custom-id.default_length', 8);
        $prefix = $config['prefix'] ?? config('custom-id.default_prefix', '');
        $characterSet = $config['character_set'] ?? config('custom-id.character_set');
        $maxAttempts = $config['max_attempts'] ?? config('custom-id.max_attempts', 10);

        if ($length <= 0) {
            throw new CustomIdGenerationException($modelType, 0, "ID length must be greater than 0");
        }

        if (strlen($characterSet) === 0) {
            throw new CustomIdGenerationException($modelType, 0, "Character set must not be empty");
        }

        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $id = $this->generateRandomString($characterSet, $length);
            $fullId = $prefix.$id;

            // Check if ID already exists
            if (! $existsCallback($fullId)) {
                return $fullId;
            }

            $attempts++;
        }

        throw new CustomIdGenerationException($modelType, $maxAttempts);
    }

    /**
     * Generate a random string from the character set.
     */
    protected function generateRandomString(string $characterSet, int $length): string
    {
        $charactersLength = strlen($characterSet);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characterSet[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
