@echo off
REM ═══════════════════════════════════════════════════════════════
REM  POC Build Script for php_ims.dll
REM
REM  Prerequisites:
REM    1. Visual Studio 2022 Build Tools with C++ workload
REM    2. PHP 8.4.x dev pack extracted to ..\deps\php-dev\
REM
REM  Usage: build_poc.bat
REM  Output: php_ims.dll in this directory
REM ═══════════════════════════════════════════════════════════════

echo.
echo === IMS Extension POC Build ===
echo.

REM ── Find Visual Studio ────────────────────────────────────────
REM Try common installation paths

set "VCVARS="

REM Try VS 2026 (18) first, then 2022
for %%V in (18 2022) do (
    for %%E in (BuildTools Community Professional Enterprise) do (
        if exist "C:\Program Files\Microsoft Visual Studio\%%V\%%E\VC\Auxiliary\Build\vcvarsall.bat" (
            set "VCVARS=C:\Program Files\Microsoft Visual Studio\%%V\%%E\VC\Auxiliary\Build\vcvarsall.bat"
            goto :found_vc
        )
    )
)

echo ERROR: Visual Studio 2022 not found!
echo Install: winget install Microsoft.VisualStudio.2022.BuildTools
echo Then add C++ workload.
exit /b 1

:found_vc
echo Found VS: %VCVARS%
echo Setting up x64 environment...
call "%VCVARS%" x64
if errorlevel 1 (
    echo ERROR: Failed to set up Visual Studio environment
    exit /b 1
)

REM ── Check PHP dev pack ────────────────────────────────────────

set "PHP_DEV=%~dp0..\deps\php-dev"

REM The dev pack might be extracted with a version subfolder
REM Try to find it
if not exist "%PHP_DEV%\include\main\php.h" (
    REM Check if there's a subfolder like php-8.4.12-devel-nts-Win32-vs17-x64
    for /d %%D in ("%PHP_DEV%\php-*") do (
        if exist "%%D\include\main\php.h" (
            set "PHP_DEV=%%D"
            goto :found_php
        )
    )
    echo ERROR: PHP dev pack not found at %PHP_DEV%
    echo Expected: %PHP_DEV%\include\main\php.h
    echo Download php-devel-pack-8.4.x-nts-Win32-vs17-x64.zip
    echo Extract to: %~dp0..\deps\php-dev\
    exit /b 1
)

:found_php
echo PHP dev pack: %PHP_DEV%

REM ── Verify php8.lib exists ────────────────────────────────────

if not exist "%PHP_DEV%\lib\php8.lib" (
    echo ERROR: php8.lib not found at %PHP_DEV%\lib\
    echo Make sure you downloaded the NTS (non-thread-safe) dev pack
    exit /b 1
)

echo php8.lib found: %PHP_DEV%\lib\php8.lib

REM ── Compile ───────────────────────────────────────────────────

echo.
echo Compiling php_ims_poc.c ...

cl /nologo /O2 /MD /W3 ^
    /D "PHP_WIN32" ^
    /D "ZEND_WIN32" ^
    /D "ZEND_DEBUG=0" ^
    /D "COMPILE_DL_IMS" ^
    /D "ZTS=0" ^
    /D "HAVE_IMS=1" ^
    /D "WIN32" ^
    /D "_WINDOWS" ^
    /I "%PHP_DEV%\include" ^
    /I "%PHP_DEV%\include\main" ^
    /I "%PHP_DEV%\include\TSRM" ^
    /I "%PHP_DEV%\include\Zend" ^
    /I "%PHP_DEV%\include\ext" ^
    /I "%PHP_DEV%\include\win32" ^
    /c php_ims_poc.c

if errorlevel 1 (
    echo.
    echo ERROR: Compilation failed!
    exit /b 1
)

echo Linking php_ims.dll ...

link /nologo /dll /out:php_ims.dll ^
    php_ims_poc.obj ^
    "%PHP_DEV%\lib\php8.lib" ^
    bcrypt.lib ^
    kernel32.lib

if errorlevel 1 (
    echo.
    echo ERROR: Linking failed!
    exit /b 1
)

REM ── Clean up ──────────────────────────────────────────────────

del /q php_ims_poc.obj 2>nul
del /q php_ims.exp 2>nul
del /q php_ims.lib 2>nul

REM ── Verify ────────────────────────────────────────────────────

echo.
echo === Build Successful ===
echo Output: %~dp0php_ims.dll
echo.
echo Testing with system PHP...
php -d "extension=%~dp0php_ims.dll" -m 2>&1 | findstr /i "ims"
if errorlevel 1 (
    echo WARNING: Could not verify with system PHP. May need NativePHP PHP.
)

echo.
echo Testing with NativePHP PHP...
set "NATIVE_PHP=%~dp0..\..\..\dist\win-unpacked\resources\app.asar.unpacked\resources\php\php.exe"
if exist "%NATIVE_PHP%" (
    "%NATIVE_PHP%" -d "extension=%~dp0php_ims.dll" -m 2>&1 | findstr /i "ims"
    if errorlevel 1 (
        echo WARNING: Extension not detected by NativePHP PHP
    ) else (
        echo SUCCESS: Extension loads in NativePHP PHP!
    )
    echo.
    "%NATIVE_PHP%" -d "extension=%~dp0php_ims.dll" -r "var_dump(ims_get_license_status());"
) else (
    echo NativePHP PHP not found at expected path. Build the app first.
)

echo.
echo === Done ===
