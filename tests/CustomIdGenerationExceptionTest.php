<?php

use Aware\CustomId\Exceptions\CustomIdGenerationException;

it('creates exception with default message', function () {
    $exception = new CustomIdGenerationException('invoice', 5);

    expect($exception->getMessage())->toBe('Failed to generate unique ID for invoice after 5 attempts');
    expect($exception->modelType)->toBe('invoice');
    expect($exception->attempts)->toBe(5);
});

it('creates exception with custom message', function () {
    $exception = new CustomIdGenerationException('order', 3, 'Custom error message');

    expect($exception->getMessage())->toBe('Custom error message');
    expect($exception->modelType)->toBe('order');
    expect($exception->attempts)->toBe(3);
});

it('extends runtime exception', function () {
    $exception = new CustomIdGenerationException('test', 1);

    expect($exception)->toBeInstanceOf(\RuntimeException::class);
    expect($exception)->toBeInstanceOf(\Exception::class);
});

it('preserves model type and attempts in properties', function () {
    $types = ['user', 'product', 'order', 'invoice', 'payment'];

    foreach ($types as $type) {
        $exception = new CustomIdGenerationException($type, 10);

        expect($exception->modelType)->toBe($type);
        expect($exception->attempts)->toBe(10);
    }
});
