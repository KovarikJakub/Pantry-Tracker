<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Modules\Identity\Domain\IUserRepository;
use Modules\Identity\Infrastructure\Persistence\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IUserRepository::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadModules();
    }

    /**
     * Loads each module's conventional folders:
     *
     * - Presentation/routes/web.php         -> web routes
     * - Presentation/views                  -> views under the "<module-kebab>::" namespace
     * - Infrastructure/Database/Migrations  -> migrations
     */
    private function loadModules(): void
    {
        foreach (glob(base_path('modules/*'), GLOB_ONLYDIR) ?: [] as $modulePath) {
            if (is_dir($modulePath.'/Infrastructure/Database/Migrations')) {
                $this->loadMigrationsFrom($modulePath.'/Infrastructure/Database/Migrations');
            }

            if (is_dir($modulePath.'/Presentation/views')) {
                $this->loadViewsFrom($modulePath.'/Presentation/views', Str::kebab(basename($modulePath)));
            }

            if (is_file($modulePath.'/Presentation/routes/web.php') && ! $this->app->routesAreCached()) {
                Route::middleware('web')->group($modulePath.'/Presentation/routes/web.php');
            }
        }
    }
}
