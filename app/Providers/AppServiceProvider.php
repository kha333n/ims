<?php

namespace App\Providers;

use App\Models\Employee;
use App\Observers\EmployeeObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Employee::observe(EmployeeObserver::class);

        Blade::directive('money', function (string $expression): string {
            return "<?php echo formatMoney({$expression}); ?>";
        });

        Blade::directive('date', function (string $expression): string {
            return "<?php echo formatDate({$expression}); ?>";
        });
    }
}
