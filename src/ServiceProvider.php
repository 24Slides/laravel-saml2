<?php

namespace Slides\Saml2;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

/**
 * Class ServiceProvider.
 *
 * @package Slides\Saml2
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @param Filesystem $filesystem
     * @return void
     */
    public function boot(Filesystem $filesystem)
    {
        $this->bootMiddleware();
        $this->bootRoutes();
        $this->bootPublishes($filesystem);
        $this->bootCommands();
        $this->loadMigrations();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/saml2.php', 'saml2');
    }

    /**
     * Bootstrap the routes.
     *
     * @return void
     */
    protected function bootRoutes()
    {
        if ($this->app['config']['saml2.useRoutes'] == true) {
            include __DIR__ . '/Http/routes.php';
        }
    }

    /**
     * Bootstrap the publishable files.
     *
     * @param Filesystem $filesystem
     * @return void
     */
    protected function bootPublishes(Filesystem $filesystem)
    {
        $this->publishes([
            __DIR__ . '/../config/saml2.php' => config_path('saml2.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_saml2_tenants_table.stub' => $this->getMigrationFileName($filesystem),
            ], 'saml2-migrations');
        }
    }

    /**
     * Bootstrap the console commands.
     *
     * @return void
     */
    protected function bootCommands()
    {
        $this->commands([
            \Slides\Saml2\Commands\CreateTenant::class,
            \Slides\Saml2\Commands\UpdateTenant::class,
            \Slides\Saml2\Commands\DeleteTenant::class,
            \Slides\Saml2\Commands\RestoreTenant::class,
            \Slides\Saml2\Commands\ListTenants::class,
            \Slides\Saml2\Commands\TenantCredentials::class,
        ]);
    }

    /**
     * Bootstrap the console commands.
     *
     * @return void
     */
    protected function bootMiddleware()
    {
        $this->app['router']->aliasMiddleware('saml2.resolveTenant', \Slides\Saml2\Http\Middleware\ResolveTenant::class);
    }

    /**
     * Load the package migrations.
     *
     * @return void
     */
    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName($filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath() . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path . '*_create_saml2_tenants_table.php');
            })->push($this->app->databasePath() . "/migrations/{$timestamp}_create_saml2_tenants_table.php")
            ->first();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
