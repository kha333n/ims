<?php

/**
 * Vendor Patch Manifest
 *
 * Lists all patch files to apply during build hardening.
 * Each entry is a filename in build/patches/ that returns a patch definition array.
 *
 * Order matters: Layer 1 patches are applied first (bootstrap),
 * Layer 7 last (deep internals).
 */

return [
    // Layer 1: Framework Bootstrap
    'laravel_application.patch.php',
    'laravel_provider_repository.patch.php',
    'composer_autoload_real.patch.php',
    'composer_classloader.patch.php',

    // Layer 2: HTTP Pipeline
    'symfony_httpkernel.patch.php',
    'symfony_response.patch.php',
    'symfony_router.patch.php',
    'laravel_router.patch.php',
    'laravel_pipeline.patch.php',

    // Layer 3: Database
    'laravel_connection.patch.php',
    'laravel_sqlite_connector.patch.php',
    'laravel_model.patch.php',
    'laravel_query_builder.patch.php',

    // Layer 4: Auth & Session
    'laravel_session_guard.patch.php',
    'laravel_session_store.patch.php',
    'laravel_cookie_jar.patch.php',

    // Layer 5: View & Livewire
    'laravel_view_factory.patch.php',
    'laravel_blade_compiler.patch.php',
    'livewire_handle_requests.patch.php',
    'livewire_handle_components.patch.php',

    // Layer 6: Encryption & Hashing
    'laravel_encrypter.patch.php',
    'laravel_bcrypt_hasher.patch.php',

    // Layer 7: Deep Internals
    'carbon_carbon.patch.php',
    'symfony_console.patch.php',
    'psr_abstract_logger.patch.php',
    'monolog_logger.patch.php',
    'symfony_error_handler.patch.php',
];
