<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Character Set
    |--------------------------------------------------------------------------
    |
    | The character set used for generating custom IDs. This set removes
    | ambiguous characters (0, O, 1, I, L) to improve readability.
    |
    */
    'character_set' => env('CUSTOM_ID_CHARSET', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Generation Attempts
    |--------------------------------------------------------------------------
    |
    | The maximum number of attempts to generate a unique ID before throwing
    | an exception. This prevents infinite loops in case of collisions.
    |
    */
    'max_attempts' => 10,

    /*
    |--------------------------------------------------------------------------
    | Default ID Length
    |--------------------------------------------------------------------------
    |
    | The default length for generated IDs when not specified by the model.
    |
    */
    'default_length' => 8,

    /*
    |--------------------------------------------------------------------------
    | Default Prefix
    |--------------------------------------------------------------------------
    |
    | The default prefix for generated IDs when not specified by the model.
    |
    */
    'default_prefix' => '',

    /*
    |--------------------------------------------------------------------------
    | User Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to the User model when using the optional
    | users table migration. You can override the default settings here.
    |
    */
    'users' => [
        'length' => 8,
        'prefix' => '',
        // 'character_set' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789', // Uses default if not set
    ],

    /*
    |--------------------------------------------------------------------------
    | Users Migration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the optional users table migration that converts
    | the users table from integer auto-increment IDs to custom string IDs.
    |
    */
    'users_migration' => [
        /*
        |--------------------------------------------------------------------------
        | Related Tables
        |--------------------------------------------------------------------------
        |
        | Additional tables that have foreign keys referencing users.id.
        | The migration automatically handles common Laravel tables:
        | - sessions (user_id)
        | - personal_access_tokens (tokenable_id, polymorphic)
        | - notifications (notifiable_id, polymorphic)
        | - oauth_access_tokens (user_id)
        | - oauth_auth_codes (user_id)
        | - oauth_clients (user_id)
        |
        | Add your custom tables here:
        |
        | 'related_tables' => [
        |     'posts' => [
        |         'column' => 'user_id',
        |         'polymorphic' => false,
        |     ],
        |     'comments' => [
        |         'column' => 'author_id',
        |         'polymorphic' => false,
        |     ],
        |     'activity_log' => [
        |         'column' => 'causer_id',
        |         'polymorphic' => true,
        |         'morph_type' => 'causer_type',
        |         'morph_value' => 'App\\Models\\User',
        |     ],
        | ],
        |
        */
        'related_tables' => [
            // Add your custom tables here
        ],
    ],
];
