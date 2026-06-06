<?php

use Aware\CustomId\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('test_models', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('name')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('assigns custom id on model creation', function () {
    $model = TestModel::create(['name' => 'Test']);

    expect($model->id)->toBeString();
    expect(strlen($model->id))->toBe(8);
    expect($model->id)->not->toBeEmpty();
});

it('does not overwrite existing id', function () {
    $model = new TestModel();
    $model->id = 'custom-id-123';
    $model->name = 'Test';
    $model->save();

    expect($model->id)->toBe('custom-id-123');
});

it('returns false for getIncrementing', function () {
    $model = new TestModel();

    expect($model->getIncrementing())->toBeFalse();
});

it('returns string for key type', function () {
    $model = new TestModel();

    expect($model->getKeyType())->toBe('string');
});

it('generates unique ids for multiple models', function () {
    $ids = [];
    for ($i = 0; $i < 20; $i++) {
        $model = TestModel::create(['name' => "Item {$i}"]);
        $ids[] = $model->id;
    }

    expect(count($ids))->toBe(20);
    expect(count(array_unique($ids)))->toBe(20);
});

it('uses model specific config when provided', function () {
    Schema::dropIfExists('custom_config_models');

    Schema::create('custom_config_models', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    $model = CustomConfigModel::create(['name' => 'Test']);

    expect($model->id)->toStartWith('CFG-');
    expect(strlen($model->id) - 4)->toBe(10);

    Schema::dropIfExists('custom_config_models');
});

it('checks id existence including soft deleted records', function () {
    Schema::dropIfExists('soft_delete_models');

    Schema::create('soft_delete_models', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->string('name')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $model1 = SoftDeleteModel::create(['name' => 'First']);
    $model1->delete();

    $model2 = SoftDeleteModel::create(['name' => 'Second']);

    expect($model1->id)->not->toBe($model2->id);

    Schema::dropIfExists('soft_delete_models');
});

it('gets correct custom id type from class name', function () {
    $model = new TestModel();
    $reflection = new ReflectionMethod($model, 'getCustomIdType');
    $reflection->setAccessible(true);
    $type = $reflection->invoke($model);

    expect($type)->toBe('testmodel');
});

class TestModel extends Model
{
    use HasCustomId;

    protected $table = 'test_models';
    protected $guarded = [];
}

class CustomConfigModel extends Model
{
    use HasCustomId;

    protected $table = 'custom_config_models';
    protected $guarded = [];

    protected function getCustomIdConfig(): ?array
    {
        return [
            'length' => 10,
            'prefix' => 'CFG-',
        ];
    }
}

class SoftDeleteModel extends Model
{
    use HasCustomId;

    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'soft_delete_models';
    protected $guarded = [];
}
