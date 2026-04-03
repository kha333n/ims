@echo off
REM ═══════════════════════════════════════════════════════════════
REM  POC Test Suite
REM
REM  Tests the extension with NativePHP's PHP binary.
REM  Run this AFTER build_poc.bat succeeds.
REM ═══════════════════════════════════════════════════════════════

echo.
echo === IMS Extension POC Tests ===
echo.

set "DLL=%~dp0php_ims.dll"
set "NATIVE_PHP=%~dp0..\..\..\dist\win-unpacked\resources\app.asar.unpacked\resources\php\php.exe"
set "SYS_PHP=php"

REM Use NativePHP PHP if available, otherwise system PHP
if exist "%NATIVE_PHP%" (
    set "PHP=%NATIVE_PHP%"
    echo Using NativePHP PHP
) else (
    set "PHP=%SYS_PHP%"
    echo Using system PHP (NativePHP build not found)
)

if not exist "%DLL%" (
    echo ERROR: php_ims.dll not found. Run build_poc.bat first.
    exit /b 1
)

echo PHP: %PHP%
echo DLL: %DLL%
echo.

REM ── Test 1: Extension loads ───────────────────────────────────

echo [TEST 1] Extension loads...
"%PHP%" -d "extension=%DLL%" -r "echo function_exists('ims_get_license_status') ? 'PASS' : 'FAIL';" 2>nul
echo.

REM ── Test 2: License status returns valid ──────────────────────

echo [TEST 2] License status...
"%PHP%" -d "extension=%DLL%" -r "var_dump(ims_get_license_status());" 2>nul
echo.

REM ── Test 3: Integrity check computes SHA-256 ──────────────────

echo [TEST 3] Integrity check (hash a file)...
"%PHP%" -d "extension=%DLL%" -r "echo ims_check_integrity('composer.json') ? 'PASS: got hash' : 'FAIL: no hash'; echo PHP_EOL;" 2>nul
echo.

REM ── Test 4: Disguised function works ──────────────────────────

echo [TEST 4] Disguised function zrx_session_gc_probability...
"%PHP%" -d "extension=%DLL%" -r "echo zrx_session_gc_probability(1440) === 1440 ? 'PASS: returns 1440' : 'FAIL'; echo PHP_EOL;" 2>nul
echo.

REM ── Test 5: Without extension, disguised function crashes ─────

echo [TEST 5] Without extension, disguised function is undefined...
"%PHP%" -r "zrx_session_gc_probability(1440);" 2>&1 | findstr /i "undefined"
if %errorlevel% equ 0 (
    echo PASS: Correctly crashes without extension
) else (
    echo FAIL: Did not crash as expected
)
echo.

REM ── Test 6: phpinfo shows extension ───────────────────────────

echo [TEST 6] phpinfo shows IMS extension...
"%PHP%" -d "extension=%DLL%" -i 2>nul | findstr /i "IMS Extension"
echo.

echo === Tests Complete ===
