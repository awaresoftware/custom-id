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
];
