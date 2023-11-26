<?php

namespace Slides\Saml2;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected bool $defer = false;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/saml2.php', 'saml2');

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
        $this->bootPublishes();
        $this->bootMiddleware();
        $this->bootRoutes();
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
        $this->publishes([__DIR__ . '/../config/saml2.php' => config_path('saml2.php')]);
    }

    /**
     * Bootstrap the console commands.
     *
     * @return void
     */
    protected function bootCommands()
    {
        $this->commands([
            \Slides\Saml2\Commands\Create::class,
            \Slides\Saml2\Commands\Update::class,
            \Slides\Saml2\Commands\ListAll::class,
//            \Slides\Saml2\Commands\DeleteTenant::class,
//            \Slides\Saml2\Commands\RestoreTenant::class,
//            \Slides\Saml2\Commands\ListTenants::class,
//            \Slides\Saml2\Commands\TenantCredentials::class
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
