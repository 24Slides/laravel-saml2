<?php

namespace Slides\Saml2;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\Slides\Saml2\Contracts\IdentityProvider::class, config('saml2.tenantModel'));
        $this->app->bind(\Slides\Saml2\Contracts\ResolvesIdentityProvider::class, config('saml2.resolvers.idp'));
        $this->app->bind(\Slides\Saml2\Contracts\ResolvesIdpConfig::class, config('saml2.resolvers.config'));
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootMiddleware();
        $this->bootRoutes();
        $this->bootPublishes();
        $this->bootCommands();
        $this->loadMigrations();
    }

    /**
     * Bootstrap the routes.
     *
     * @return void
     */
    protected function bootRoutes()
    {
        if ($this->app['config']['saml2.useRoutes']) {
            include __DIR__ . '/Http/routes.php';
        }
    }

    /**
     * Bootstrap the publishable files.
     *
     * @return void
     */
    protected function bootPublishes()
    {
        $source = __DIR__ . '/../config/saml2.php';

        $this->publishes([$source => config_path('saml2.php')]);
        $this->mergeConfigFrom($source, 'saml2');
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
            \Slides\Saml2\Commands\TenantCredentials::class
        ]);
    }

    /**
     * Bootstrap the console commands.
     *
     * @return void
     */
    protected function bootMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('saml2.resolveTenant', \Slides\Saml2\Http\Middleware\ResolveTenant::class);
    }

    /**
     * Load the package migrations.
     *
     * @return void
     */
    protected function loadMigrations(): void
    {
        if (config('saml2.load_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [];
    }
}
