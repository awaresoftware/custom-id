<?php

namespace Aware\CustomId\Facades;

use Aware\CustomId\Services\IdentificationService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string generate(string $modelType, callable $existsCallback, ?array $config = null)
 *
 * @see \Aware\CustomId\Services\IdentificationService
 */
class CustomId extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return IdentificationService::class;
    }
}
