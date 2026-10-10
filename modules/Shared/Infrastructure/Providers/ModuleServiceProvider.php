<?php

namespace Modules\Shared\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Base provider for every module. Wires up the module's conventional folders:
 *
 * - Presentation/routes/web.php           -> web routes
 * - Presentation/views                    -> views under the "<module-kebab>::" namespace
 * - Infrastructure/Database/Migrations    -> migrations
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $modulePath = $this->modulePath();

        if (is_dir($modulePath.'/Infrastructure/Database/Migrations')) {
            $this->loadMigrationsFrom($modulePath.'/Infrastructure/Database/Migrations');
        }

        if (is_dir($modulePath.'/Presentation/views')) {
            $this->loadViewsFrom($modulePath.'/Presentation/views', Str::kebab($this->moduleName()));
        }

        if (is_file($modulePath.'/Presentation/routes/web.php') && ! $this->app->routesAreCached()) {
            Route::middleware('web')->group($modulePath.'/Presentation/routes/web.php');
        }
    }

    /**
     * Module name derived from the namespace, e.g. "Modules\Household\..." -> "Household".
     */
    protected function moduleName(): string
    {
        return explode('\\', static::class)[1];
    }

    protected function modulePath(): string
    {
        return base_path('modules/'.$this->moduleName());
    }
}
