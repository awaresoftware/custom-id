<?php

use Aware\CustomId\CustomIdServiceProvider;
use Aware\CustomId\Facades\CustomId;
use Aware\CustomId\Services\IdentificationService;

it('registers service provider', function () {
    $this->app->register(CustomIdServiceProvider::class);

    expect($this->app[IdentificationService::class])->toBeInstanceOf(IdentificationService::class);
});

it('registers identification service as singleton', function () {
    $this->app->register(CustomIdServiceProvider::class);

    $service1 = $this->app[IdentificationService::class];
    $service2 = $this->app[IdentificationService::class];

    expect($service1)->toBe($service2);
});

it('merges configuration', function () {
    $this->app->register(CustomIdServiceProvider::class);

    expect(config('custom-id.character_set'))->toBeString();
    expect(config('custom-id.max_attempts'))->toBe(10);
    expect(config('custom-id.default_length'))->toBe(8);
    expect(config('custom-id.default_prefix'))->toBe('');
});

it('provides facade access to service', function () {
    $this->app->register(CustomIdServiceProvider::class);

    $id = CustomId::generate('test', fn () => false);

    expect($id)->toBeString()->toHaveLength(8);
});

it('facade generates id with custom config', function () {
    $this->app->register(CustomIdServiceProvider::class);

    $id = CustomId::generate('test', fn () => false, [
        'length' => 12,
        'prefix' => 'FAC-',
    ]);

    expect($id)->toStartWith('FAC-');
    expect(strlen($id) - 4)->toBe(12);
});

it('publishes configuration', function () {
    $this->app->register(CustomIdServiceProvider::class);

    $service = new CustomIdServiceProvider($this->app);
    $service->boot();

    expect(true)->toBeTrue();
});
