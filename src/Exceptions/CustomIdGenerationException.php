<?php

namespace Aware\CustomId\Exceptions;

use RuntimeException;

class CustomIdGenerationException extends RuntimeException
{
    public function __construct(
        public readonly string $modelType,
        public readonly int $attempts,
        string $message = '',
    ) {
        parent::__construct(
            $message ?: "Failed to generate unique ID for {$modelType} after {$attempts} attempts"
        );
    }
}
