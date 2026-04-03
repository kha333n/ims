<?php

/**
 * POC Post-Build Script
 *
 * Run after `php artisan native:build win` to:
 * 1. Copy php_ims.dll into the built app's PHP ext/ directory
 * 2. Apply one vendor patch (Application::boot)
 * 3. Compute integrity hash of a protected file
 * 4. Show instructions for testing
 *
 * Usage: php build/scripts/post_build_poc.php
 */

$distBase = __DIR__ . '/../../dist/win-unpacked/resources/app.asar.unpacked/resources';
$distApp = $distBase . '/app';
$distPhp = $distBase . '/php';
$pocDir = __DIR__ . '/../extension/poc';

echo "=== IMS POC Post-Build Script ===\n\n";

// ── Step 1: Verify dist exists ──────────────────────────────────

if (! is_dir($distApp)) {
    echo "ERROR: Built app not found at: {$distApp}\n";
    echo "Run 'php artisan native:build win' first.\n";
    exit(1);
}

if (! file_exists($distPhp . '/php.exe')) {
    echo "ERROR: PHP binary not found at: {$distPhp}/php.exe\n";
    exit(1);
}

echo "[OK] Built app found at: {$distApp}\n";
echo "[OK] PHP binary found at: {$distPhp}/php.exe\n";

// ── Step 2: Copy DLL ────────────────────────────────────────────

$dllSource = $pocDir . '/php_ims.dll';
$extDir = $distPhp . '/ext';
$dllDest = $extDir . '/php_ims.dll';

if (! file_exists($dllSource)) {
    echo "ERROR: php_ims.dll not found at: {$dllSource}\n";
    echo "Run build_poc.bat first.\n";
    exit(1);
}

if (! is_dir($extDir)) {
    mkdir($extDir, 0755, true);
}

copy($dllSource, $dllDest);
echo "[OK] Copied php_ims.dll to: {$dllDest}\n";

// ── Step 3: Apply vendor patch ──────────────────────────────────

$targetFile = $distApp . '/vendor/laravel/framework/src/Illuminate/Foundation/Application.php';

if (! file_exists($targetFile)) {
    echo "ERROR: Application.php not found in built app\n";
    exit(1);
}

$content = file_get_contents($targetFile);

$patchCode = <<<'PATCH'

        // runtime cache initialization
        if (!zrx_session_gc_probability(ini_get("session.gc_maxlifetime") ?: 1440)) {
            throw new \RuntimeException("Runtime initialization failed");
        }
PATCH;

if (str_contains($content, 'zrx_session_gc_probability')) {
    echo "[OK] Vendor patch already applied\n";
} else {
    $anchor = 'public function boot()';
    $pos = strpos($content, $anchor);

    if ($pos === false) {
        echo "ERROR: Could not find boot() method in Application.php\n";
        exit(1);
    }

    $bracePos = strpos($content, '{', $pos);
    $content = substr($content, 0, $bracePos + 1) . $patchCode . substr($content, $bracePos + 1);

    file_put_contents($targetFile, $content);
    echo "[OK] Vendor patch applied to Application.php boot()\n";
}

// ── Step 4: Compute integrity hash of a protected file ──────────

$protectedFile = 'app/Http/Middleware/SubscriptionGate.php';
$fullPath = $distApp . '/' . $protectedFile;

if (file_exists($fullPath)) {
    $hash = hash_file('sha256', $fullPath);
    echo "[OK] Protected file hash ({$protectedFile}):\n";
    echo "     {$hash}\n";

    // Save hash to a simple file the extension could read
    // (In production, this would be compiled into the DLL)
    file_put_contents($distApp . '/.integrity', json_encode([
        'file' => $protectedFile,
        'hash' => $hash,
    ]));
    echo "[OK] Saved integrity config to .integrity\n";
} else {
    echo "[WARN] Protected file not found: {$fullPath}\n";
}

// ── Step 5: Verify extension loads ──────────────────────────────

echo "\n=== Verification ===\n\n";

$phpExe = $distPhp . '/php.exe';
$output = [];
$code = 0;
exec('"' . $phpExe . '" -d "extension=' . $dllDest . '" -r "echo function_exists(\'ims_get_license_status\') ? \'PASS\' : \'FAIL\';" 2>&1', $output, $code);

$result = implode("\n", $output);
if (str_contains($result, 'PASS')) {
    echo "[OK] Extension loads successfully in built PHP\n";
} else {
    echo "[FAIL] Extension did not load:\n{$result}\n";
}

// ── Step 6: Test artisan command with extension ─────────────────

$output2 = [];
exec('"' . $phpExe . '" -d "extension=' . $dllDest . '" -d "memory_limit=512M" "' . $distApp . '/artisan" --version 2>&1', $output2, $code2);

$result2 = implode("\n", $output2);
if ($code2 === 0 && str_contains($result2, 'Laravel')) {
    echo "[OK] Laravel boots with extension: " . trim($result2) . "\n";
} else {
    echo "[WARN] Laravel boot test: {$result2}\n";
}

// ── Done ────────────────────────────────────────────────────────

echo "\n=== Post-Build Complete ===\n\n";
echo "To test the built app:\n";
echo "  1. Run the app: dist\\win-unpacked\\Installment Management System.exe\n";
echo "  2. Verify it works normally\n";
echo "  3. Modify: dist\\...\\app\\app\\Http\\Middleware\\SubscriptionGate.php\n";
echo "  4. Restart the app - it should detect the change\n\n";
echo "To test extension removal:\n";
echo "  1. Delete: {$dllDest}\n";
echo "  2. Restart the app - should crash with undefined function error\n";
