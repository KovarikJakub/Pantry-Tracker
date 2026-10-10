<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Shared\Infrastructure\Providers\ModuleServiceProvider;
use ReflectionClass;

/**
 * Registers every concrete module provider found at
 * modules/<Module>/Infrastructure/Providers/*ServiceProvider.php.
 */
class ModuleDiscoveryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ($this->discoverModuleProviders() as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * @return list<class-string<ModuleServiceProvider>>
     */
    private function discoverModuleProviders(): array
    {
        $providers = [];

        foreach (glob(base_path('modules/*/Infrastructure/Providers/*ServiceProvider.php')) ?: [] as $file) {
            $module = basename(dirname($file, 3));
            $class = 'Modules\\'.$module.'\\Infrastructure\\Providers\\'.basename($file, '.php');

            if (class_exists($class)
                && is_subclass_of($class, ModuleServiceProvider::class)
                && ! (new ReflectionClass($class))->isAbstract()) {
                $providers[] = $class;
            }
        }

        return $providers;
    }
}
