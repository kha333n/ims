<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open()
            ->title('Installment Management System')
            ->minWidth(1024)
            ->minHeight(768)
            ->width(1280)
            ->height(800);
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        // Load IMS security extension in production builds only.
        // The DLL is NTS and only compatible with NativePHP's bundled PHP.
        $extPath = dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'ext' . DIRECTORY_SEPARATOR . 'php_ims.dll';

        if (file_exists($extPath)) {
            return [
                'extension' => $extPath,
            ];
        }

        return [];
    }
}
