# POC: PHP Extension for IMS

## What This Tests

1. Can we compile a PHP extension (.dll) compatible with NativePHP's PHP 8.4?
2. Can NativePHP load it via the `phpIni()` method in NativeAppServiceProvider?
3. Does calling extension functions from Laravel code work?
4. Does removing the extension crash the app (via a vendor patch calling a disguised function)?
5. Does file integrity checking (SHA-256) detect modifications?

## Prerequisites

1. **Visual Studio 2022 Build Tools** with C++ workload
2. **PHP 8.4.x Developer Pack** (NTS, VS17, x64) extracted to `build/extension/deps/php-dev/`

## Build

```cmd
cd build\extension\poc
build_poc.bat
```

Output: `php_ims.dll`

## Test Locally (before building the full app)

```cmd
REM Use NativePHP's PHP binary:
set PHP=dist\win-unpacked\resources\app.asar.unpacked\resources\php\php.exe

REM Test extension loads:
%PHP% -d "extension=build\extension\poc\php_ims.dll" -m

REM Test license function:
%PHP% -d "extension=build\extension\poc\php_ims.dll" -r "var_dump(ims_get_license_status());"

REM Test integrity check:
%PHP% -d "extension=build\extension\poc\php_ims.dll" -r "var_dump(ims_check_integrity('app/Http/Middleware/SubscriptionGate.php'));"

REM Test disguised function:
%PHP% -d "extension=build\extension\poc\php_ims.dll" -r "var_dump(zrx_session_gc_probability(1440));"
```

## Integrate with NativePHP

In `app/Providers/NativeAppServiceProvider.php`, add to `phpIni()`:

```php
public function phpIni(): array
{
    return [
        'extension' => base_path('build/extension/poc/php_ims.dll'),
    ];
}
```

## Test Vendor Patch (manual, for POC)

Edit one vendor file in the installed app (dist/) to call the disguised function.
Then test: remove the dll -> app should crash with "Call to undefined function".

## POC Functions

- `ims_get_license_status(): array` -- Returns hardcoded valid license
- `ims_check_integrity(string $path): string|false` -- SHA-256 hash of file
- `ims_set_watch(string $path, string $hash): bool` -- Set file to integrity-check
- `zrx_session_gc_probability(int $maxlifetime): int` -- Disguised license check
