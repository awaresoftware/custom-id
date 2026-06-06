<?php

use Aware\CustomId\Services\IdentificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('users');
});

it('generates unique ids for existing users', function () {
    DB::table('users')->insert([
        ['name' => 'User 1', 'email' => 'user1@test.com', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'User 2', 'email' => 'user2@test.com', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'User 3', 'email' => 'user3@test.com', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $service = app(IdentificationService::class);
    $users = DB::table('users')->select('id')->get();

    $config = [
        'length' => config('custom-id.users.length', config('custom-id.default_length', 8)),
        'prefix' => config('custom-id.users.prefix', config('custom-id.default_prefix', '')),
        'character_set' => config('custom-id.users.character_set', config('custom-id.character_set')),
        'max_attempts' => config('custom-id.max_attempts', 10),
    ];

    $generatedIds = [];
    $existingIds = [];

    foreach ($users as $user) {
        $newId = $service->generate(
            'user',
            fn (string $id) => in_array($id, $existingIds),
            $config
        );

        $generatedIds[] = $newId;
        $existingIds[] = $newId;
    }

    expect(count($generatedIds))->toBe(3);
    expect(count(array_unique($generatedIds)))->toBe(3);

    foreach ($generatedIds as $id) {
        expect($id)->toBeString();
        expect(strlen($id))->toBe(8);
    }
});

it('generates ids with user specific config', function () {
    config(['custom-id.users.length' => 10]);
    config(['custom-id.users.prefix' => 'USR-']);

    DB::table('users')->insert([
        ['name' => 'User 1', 'email' => 'user1@test.com', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $service = app(IdentificationService::class);
    $users = DB::table('users')->select('id')->get();

    $config = [
        'length' => config('custom-id.users.length', config('custom-id.default_length', 8)),
        'prefix' => config('custom-id.users.prefix', config('custom-id.default_prefix', '')),
        'character_set' => config('custom-id.users.character_set', config('custom-id.character_set')),
        'max_attempts' => config('custom-id.max_attempts', 10),
    ];

    $existingIds = [];
    foreach ($users as $user) {
        $newId = $service->generate('user', fn (string $id) => in_array($id, $existingIds), $config);
        $existingIds[] = $newId;

        expect($newId)->toStartWith('USR-');
        expect(strlen($newId) - 4)->toBe(10);
    }
});

it('handles empty users table', function () {
    $users = DB::table('users')->select('id')->get();

    expect($users->count())->toBe(0);
});

it('generates ids only from configured character set for users', function () {
    DB::table('users')->insert([
        ['name' => 'User 1', 'email' => 'user1@test.com', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $service = app(IdentificationService::class);
    $users = DB::table('users')->select('id')->get();

    $charset = config('custom-id.character_set');
    $config = [
        'length' => config('custom-id.default_length', 8),
        'prefix' => '',
        'character_set' => $charset,
        'max_attempts' => 10,
    ];

    foreach ($users as $user) {
        $newId = $service->generate('user', fn () => false, $config);

        for ($i = 0; $i < strlen($newId); $i++) {
            expect(strpos($charset, $newId[$i]))->not->toBeFalse();
        }
    }
});
