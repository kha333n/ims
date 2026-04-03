<?php

/**
 * POC Test: Apply a single vendor patch to test extension dependency.
 *
 * This patches Illuminate\Foundation\Application::boot() to call
 * zrx_session_gc_probability(). If the extension is not loaded,
 * the app will crash with "Call to undefined function".
 *
 * Usage: php build/extension/poc/test_patch.php apply
 *        php build/extension/poc/test_patch.php revert
 */

$action = $argv[1] ?? 'help';
$targetFile = __DIR__ . '/../../../vendor/laravel/framework/src/Illuminate/Foundation/Application.php';

if (! file_exists($targetFile)) {
    echo "ERROR: Target file not found: {$targetFile}\n";
    exit(1);
}

$patchCode = <<<'PATCH'

        // runtime cache initialization
        if (!zrx_session_gc_probability(ini_get("session.gc_maxlifetime") ?: 1440)) {
            throw new \RuntimeException("Runtime initialization failed");
        }
PATCH;

$anchor = 'public function boot()';
$afterAnchor = '{';

$content = file_get_contents($targetFile);

switch ($action) {
    case 'apply':
        if (str_contains($content, 'zrx_session_gc_probability')) {
            echo "Patch already applied.\n";
            exit(0);
        }

        // Find the boot() method opening brace
        $pos = strpos($content, $anchor);
        if ($pos === false) {
            echo "ERROR: Could not find anchor: {$anchor}\n";
            exit(1);
        }

        $bracePos = strpos($content, $afterAnchor, $pos);
        if ($bracePos === false) {
            echo "ERROR: Could not find opening brace after anchor\n";
            exit(1);
        }

        // Insert patch after the opening brace
        $content = substr($content, 0, $bracePos + 1) . $patchCode . substr($content, $bracePos + 1);

        file_put_contents($targetFile, $content);
        echo "Patch APPLIED to Application.php boot()\n";
        echo "The app will now crash if zrx_session_gc_probability() is undefined.\n";
        break;

    case 'revert':
        if (! str_contains($content, 'zrx_session_gc_probability')) {
            echo "Patch not found (already clean).\n";
            exit(0);
        }

        $content = str_replace($patchCode, '', $content);
        file_put_contents($targetFile, $content);
        echo "Patch REVERTED from Application.php boot()\n";
        break;

    case 'status':
        if (str_contains($content, 'zrx_session_gc_probability')) {
            echo "Patch is APPLIED\n";
        } else {
            echo "Patch is NOT applied (clean)\n";
        }
        break;

    default:
        echo "Usage: php test_patch.php [apply|revert|status]\n";
        break;
}
