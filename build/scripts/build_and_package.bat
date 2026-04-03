@echo off
echo ============================================
echo  IMS Build + Harden + Package
echo ============================================
echo.

cd /d C:\Users\kha33\PhpstormProjects\installements

REM === Step 1: Build NativePHP app ===
echo [1/5] Building NativePHP app...
call php artisan native:build win all
if errorlevel 1 (
    echo BUILD FAILED
    exit /b 1
)

REM === Step 2: Run post-build (copy DLL + vendor patch) ===
echo.
echo [2/5] Running post-build hardening...
call php build/scripts/post_build_poc.php
if errorlevel 1 (
    echo POST-BUILD FAILED
    exit /b 1
)

REM === Step 3: Compile extension with import lib from built php.exe ===
echo.
echo [3/5] Recompiling extension against built php.exe...
call "C:\Program Files\Microsoft Visual Studio\18\Community\VC\Auxiliary\Build\vcvarsall.bat" x64 -vcvars_ver=14.4

set DIST_PHP=C:\Users\kha33\PhpstormProjects\installements\dist\win-unpacked\resources\app.asar.unpacked\resources\php
set PHP_DEV=C:\Users\kha33\PhpstormProjects\installements\build\extension\deps\php-dev
set POC_DIR=C:\Users\kha33\PhpstormProjects\installements\build\extension\poc

cd /d %POC_DIR%
del /q php_ims_poc.obj php_ims.dll php_ims.lib php_ims.exp php_exe.lib php_exe.exp php_exe.def php_exports_raw.txt 2>nul

dumpbin /exports "%DIST_PHP%\php.exe" /out:php_exports_raw.txt
echo LIBRARY php.exe > php_exe.def
echo EXPORTS >> php_exe.def
for /f "tokens=4" %%a in ('findstr /r "^  *[0-9]" php_exports_raw.txt') do echo %%a >> php_exe.def
lib /nologo /def:php_exe.def /out:php_exe.lib /machine:x64

cl /nologo /O2 /MT /W3 /D "PHP_WIN32" /D "ZEND_WIN32" /D "ZEND_DEBUG=0" /D "COMPILE_DL_IMS" /D "HAVE_IMS=1" /D "WIN32" /D "_WINDOWS" /D "_CRT_SECURE_NO_WARNINGS" /I "%PHP_DEV%\include" /I "%PHP_DEV%\include\main" /I "%PHP_DEV%\include\TSRM" /I "%PHP_DEV%\include\Zend" /I "%PHP_DEV%\include\ext" /I "%PHP_DEV%\include\win32" /c php_ims_poc.c
if errorlevel 1 (
    echo COMPILE FAILED
    exit /b 1
)
link /nologo /dll /out:php_ims.dll php_ims_poc.obj php_exe.lib bcrypt.lib kernel32.lib
if errorlevel 1 (
    echo LINK FAILED
    exit /b 1
)
del /q php_ims_poc.obj php_ims.lib php_ims.exp php_exports_raw.txt php_exe.def php_exe.lib php_exe.exp 2>nul

REM Copy freshly compiled DLL to dist
mkdir "%DIST_PHP%\ext" 2>nul
copy /y php_ims.dll "%DIST_PHP%\ext\php_ims.dll"
echo [OK] Extension copied to dist

REM === Step 4: Create php.ini in dist ===
echo.
echo [4/5] Creating php.ini for extension loading...
echo extension_dir=ext > "%DIST_PHP%\php.ini"
echo extension=php_ims.dll >> "%DIST_PHP%\php.ini"
echo [OK] php.ini created

REM === Step 5: Repackage installer ===
echo.
echo [5/5] Repackaging installer (NSIS)...
cd /d C:\Users\kha33\PhpstormProjects\installements\vendor\nativephp\electron\resources\js

REM Delete old installer
del /q "C:\Users\kha33\PhpstormProjects\installements\dist\Installment Management System-*-setup.exe" 2>nul
del /q "C:\Users\kha33\PhpstormProjects\installements\dist\Installment Management System-*-setup.exe.blockmap" 2>nul

REM Set env vars that electron-builder.js needs
set NATIVEPHP_APP_ID=com.nativephp.app
set NATIVEPHP_APP_NAME=Installment Management System
set NATIVEPHP_APP_FILENAME=installment-management-system
set NATIVEPHP_APP_VERSION=1.0.0
set NATIVEPHP_APP_AUTHOR=Techmiddle Technologies
set NATIVEPHP_APP_COPYRIGHT=Copyright 2026
set NATIVEPHP_BUILDING=true
set NATIVEPHP_UPDATER_ENABLED=false

REM Run electron-builder to repackage from existing win-unpacked
npx electron-builder --win nsis --x64 --prepackaged "C:\Users\kha33\PhpstormProjects\installements\dist\win-unpacked" --config electron-builder.js -p never
if errorlevel 1 (
    echo WARNING: electron-builder failed. The win-unpacked dir is still valid for direct testing.
    echo You can run: dist\win-unpacked\installment-management-system.exe
)

cd /d C:\Users\kha33\PhpstormProjects\installements

echo.
echo ============================================
echo  BUILD COMPLETE
echo ============================================
echo.
echo Installer: dist\Installment Management System-1.0.0-setup.exe
echo Unpacked:  dist\win-unpacked\
echo.
dir dist\*.exe 2>nul
