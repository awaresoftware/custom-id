<?php

use Aware\CustomId\Exceptions\CustomIdGenerationException;
use Aware\CustomId\Services\IdentificationService;

it('generates an id with default configuration', function () {
    $service = new IdentificationService();

    $id = $service->generate('test', fn () => false);

    expect($id)->toBeString()->toHaveLength(8);
});

it('generates an id with custom length', function () {
    $service = new IdentificationService();

    $id = $service->generate('test', fn () => false, ['length' => 12]);

    expect($id)->toBeString()->toHaveLength(12);
});

it('generates an id with a prefix', function () {
    $service = new IdentificationService();

    $id = $service->generate('test', fn () => false, ['prefix' => 'PRJ-']);

    expect($id)->toBeString()->toStartWith('PRJ-');
});

it('generates an id with custom character set', function () {
    $service = new IdentificationService();

    $charset = '0123456789';
    $id = $service->generate('test', fn () => false, ['character_set' => $charset, 'length' => 6]);

    expect($id)->toBeString()->toHaveLength(6);
    expect(preg_match('/^[0-9]{6}$/', $id))->toBe(1);
});

it('generates unique ids on repeated calls', function () {
    $service = new IdentificationService();

    $ids = [];
    for ($i = 0; $i < 100; $i++) {
        $ids[] = $service->generate('test', fn () => false);
    }

    expect(count($ids))->toBe(100);
    expect(count(array_unique($ids)))->toBe(100);
});

it('retries on collision via exists callback', function () {
    $service = new IdentificationService();
    $attemptedIds = [];

    $id = $service->generate('test', function ($id) use (&$attemptedIds) {
        $attemptedIds[] = $id;
        return count($attemptedIds) <= 3;
    });

    expect(count($attemptedIds))->toBeGreaterThan(3);
    expect($id)->toBeString();
});

it('throws exception after max attempts', function () {
    $service = new IdentificationService();

    $service->generate('test', fn () => true, ['max_attempts' => 5]);
})->throws(CustomIdGenerationException::class);

it('includes model type in exception message', function () {
    $service = new IdentificationService();

    try {
        $service->generate('invoice', fn () => true, ['max_attempts' => 3]);
    } catch (CustomIdGenerationException $e) {
        expect($e->getMessage())->toContain('invoice');
        expect($e->modelType)->toBe('invoice');
        expect($e->attempts)->toBe(3);
    }
});

it('uses config values when no config override provided', function () {
    config(['custom-id.default_length' => 10]);
    config(['custom-id.default_prefix' => 'CFG-']);
    config(['custom-id.character_set' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789']);
    config(['custom-id.max_attempts' => 10]);

    $service = new IdentificationService();

    $id = $service->generate('test', fn () => false);

    expect($id)->toStartWith('CFG-');
    expect(strlen($id) - 4)->toBe(10);
});

it('generates ids only from configured character set', function () {
    $charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    config(['custom-id.character_set' => $charset]);

    $service = new IdentificationService();

    for ($i = 0; $i < 500; $i++) {
        $id = $service->generate('test', fn () => false);

        for ($j = 0; $j < strlen($id); $j++) {
            expect(strpos($charset, $id[$j]))->not->toBeFalse();
        }
    }
});

it('respects max attempts config', function () {
    $service = new IdentificationService();

    $attempts = 0;
    $callback = function () use (&$attempts) {
        $attempts++;
        return true;
    };

    try {
        $service->generate('test', $callback, ['max_attempts' => 7]);
    } catch (CustomIdGenerationException) {
        // expected
    }

    expect($attempts)->toBe(7);
});

it('combines prefix and random string correctly', function () {
    $service = new IdentificationService();

    $id = $service->generate('test', fn () => false, [
        'prefix' => 'ORD-',
        'length' => 6,
        'character_set' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    ]);

    expect($id)->toMatch('/^ORD-[A-Z]{6}$/');
});
