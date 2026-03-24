# Code Obfuscation Guide

## Setup (one-time)

yakpro-po cannot be installed as a project dependency (php-parser version conflict with Laravel 12).
Install it globally or as a standalone tool:

```bash
# Option 1: Separate directory
cd C:\tools
git clone https://github.com/nicedevelopments/yakpro-po.git
cd yakpro-po
composer install
```

## Pre-build obfuscation

Before running `php artisan native:build`, obfuscate the source:

```bash
# Create obfuscated copy
php C:\tools\yakpro-po\yakpro-po.php \
  --config-file build/yakpro.cnf \
  app/ -o build/obfuscated/app/

# Replace source with obfuscated version
# (the build script handles this — see build/build.sh)
```

## What gets obfuscated
- `app/Services/` — all business logic including LicenseManager, encryption keys
- `app/Http/Middleware/` — subscription gate
- `app/Models/` — model classes
- `app/Livewire/` — component logic

## What does NOT get obfuscated
- `resources/views/` — Blade templates (they reference class/property names)
- `config/` — Laravel expects specific keys
- `routes/` — route definitions reference class names
- `vendor/` — third-party packages
- `database/` — migrations reference column names

## Alternative: IonCube Encoder ($399+)
- Compiles PHP to bytecode — much stronger than obfuscation
- Requires IonCube Loader extension in the NativePHP PHP binary
- See https://www.ioncube.com/php_encoder.php
