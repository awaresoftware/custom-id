# Custom ID Generator for Laravel

A Laravel package for generating unique custom IDs with configurable character sets, lengths, and prefixes. Perfect for creating human-readable, collision-resistant identifiers for your Eloquent models.

## Features

- 🎲 **Configurable ID generation** - Set length, prefix, and character set per model
- 🔒 **Collision detection** - Automatic retry mechanism with configurable attempts
- 🗑️ **Soft-delete aware** - Prevents ID reuse from soft-deleted records
- 🚀 **Race condition handling** - Built-in retry logic for concurrent requests
- 🎯 **Simple API** - Just use a trait and implement one method
- 📝 **Custom exceptions** - Detailed error information for debugging
- ⚡ **Zero dependencies** - Only requires `illuminate/support`

## Installation

```bash
composer require aware/custom-id
```

## Quick Start

### 1. Use the Trait

Add the `HasCustomId` trait to your model:

```php
use Aware\CustomId\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasCustomId;

    // Optional: Override if your config key differs from class name
    protected function getCustomIdType(): string
    {
        return 'event'; // Default is strtolower(class_basename(static::class))
    }

    // Optional: Provide custom configuration
    protected function getCustomIdConfig(): ?array
    {
        return [
            'length' => 6,
            'prefix' => 'EVT-',
        ];
    }
}
```

### 2. Update Your Migration

Set the primary key as a string:

```php
Schema::create('events', function (Blueprint $table) {
    $table->string('id', 10)->primary(); // Adjust length for prefix + ID length
    // ... other columns
});
```

### 3. Create Records

IDs are generated automatically:

```php
$event = Event::create([
    'name' => 'Laravel Conference 2025',
]);

echo $event->id; // EVT-A3K7P9
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=custom-id-config
```

### Global Defaults

Edit `config/custom-id.php`:

```php
return [
    // Character set (removes ambiguous characters by default)
    'character_set' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',

    // Maximum generation attempts before throwing exception
    'max_attempts' => 10,

    // Default ID length (when not specified by model)
    'default_length' => 8,

    // Default prefix
    'default_prefix' => '',
];
```

### Per-Model Configuration

Override `getCustomIdConfig()` in your model:

```php
protected function getCustomIdConfig(): ?array
{
    return [
        'length' => 8,              // ID length (excluding prefix)
        'prefix' => 'ORDER-',       // Prefix for the ID
        'character_set' => 'ABC123', // Custom character set
        'max_attempts' => 20,       // Override max retry attempts
    ];
}
```

**Or** load from your app's config:

```php
// config/orders.php
return [
    'id_generation' => [
        'length' => 8,
        'prefix' => 'ORDER-',
    ],
];

// app/Models/Order.php
protected function getCustomIdConfig(): ?array
{
    return config('orders.id_generation');
}
```

## Converting Users Table to Custom IDs

The package includes an optional migration to convert your existing `users` table from auto-incrementing integer IDs to custom string IDs.

### Publish the Migration

```bash
php artisan vendor:publish --tag=custom-id-users-migration
```

This will create a timestamped migration in your `database/migrations` directory.

### Configure the Migration

Before running the migration, update your `config/custom-id.php`:

```php
'users' => [
    'length' => 8,          // Custom ID length for users
    'prefix' => '',         // Optional prefix (e.g., 'USR-')
],

'users_migration' => [
    'related_tables' => [
        // Add your custom tables that reference users.id
        'posts' => [
            'column' => 'user_id',
            'polymorphic' => false,
        ],
        'comments' => [
            'column' => 'author_id',
            'polymorphic' => false,
        ],
        // For polymorphic relations
        'activity_log' => [
            'column' => 'causer_id',
            'polymorphic' => true,
            'morph_type' => 'causer_type',
            'morph_value' => 'App\\Models\\User',
        ],
    ],
],
```

### Auto-Detected Tables

The migration automatically handles these common Laravel tables:
- `sessions` (user_id)
- `personal_access_tokens` (tokenable_id - polymorphic)
- `notifications` (notifiable_id - polymorphic)
- `oauth_access_tokens` (user_id)
- `oauth_auth_codes` (user_id)
- `oauth_clients` (user_id)

### Run the Migration

```bash
php artisan migrate
```

### Update Your User Model

Add the `HasCustomId` trait to your User model:

```php
use Aware\CustomId\Traits\HasCustomId;

class User extends Authenticatable
{
    use HasCustomId;

    protected function getCustomIdConfig(): ?array
    {
        return config('custom-id.users');
    }
}
```

### Important Notes

1. **Backup your database** before running this migration
2. **Test thoroughly** in a development environment first
3. The migration supports MySQL, PostgreSQL, and SQLite
4. Reverting the migration assigns new sequential integer IDs (original IDs cannot be restored)
5. Related tables configured in the config will have their columns converted to string type

## Advanced Usage

### Soft Delete Awareness

The package automatically detects if your model uses `SoftDeletes` and includes trashed records when checking for ID uniqueness:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasCustomId, SoftDeletes;

    // IDs from soft-deleted records won't be reused
}
```

### Race Condition Handling

Built-in retry mechanism handles concurrent ID generation:

```php
// Automatically retries up to 3 times if generation fails
// due to race conditions or collisions
```

### Custom Exception Handling

Catch generation failures with detailed context:

```php
use Aware\CustomId\Exceptions\CustomIdGenerationException;

try {
    $model = MyModel::create($data);
} catch (CustomIdGenerationException $e) {
    echo $e->modelType;  // "my_model"
    echo $e->attempts;   // 10
    echo $e->getMessage(); // "Failed to generate unique ID for my_model after 10 attempts"
}
```

### Using the Service Directly

```php
use Aware\CustomId\Services\IdentificationService;

$service = app(IdentificationService::class);

$id = $service->generate(
    modelType: 'product',
    existsCallback: fn($id) => Product::where('sku', $id)->exists(),
    config: [
        'length' => 6,
        'prefix' => 'SKU-',
    ]
);
```

### Using the Facade

```php
use Aware\CustomId\Facades\CustomId;

$id = CustomId::generate(
    'ticket',
    fn($id) => Ticket::where('code', $id)->exists(),
    ['length' => 8, 'prefix' => 'TKT-']
);
```

## Character Sets

### Default Character Set
```
ABCDEFGHJKLMNPQRSTUVWXYZ23456789
```
Excludes: `0`, `O`, `1`, `I`, `L` (ambiguous characters)

### Common Alternatives

**Alphanumeric (uppercase):**
```php
'character_set' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
```

**Alphanumeric (mixed case):**
```php
'character_set' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
```

**Numbers only:**
```php
'character_set' => '0123456789'
```

**Base32 (Crockford):**
```php
'character_set' => '0123456789ABCDEFGHJKMNPQRSTVWXYZ'
```

## Collision Probability

With default settings (31 characters, length 8):
- Total possibilities: 31^8 = ~852 billion
- At 1 million records: collision probability < 0.0001%
- At 10 million records: collision probability < 0.001%

Increase length for larger datasets:
- Length 6: ~887 million combinations
- Length 8: ~852 billion combinations
- Length 10: ~819 trillion combinations

## Testing

```bash
composer test
```

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Credits

Developed by [Aware j.d.o.o.](https://aware.studio)

## Support

- **Issues:** [GitHub Issues](https://github.com/aware/custom-id/issues)
- **Email:** mirko@aware.studio
