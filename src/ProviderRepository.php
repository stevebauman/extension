<?php namespace Orchestra\Extension;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Orchestra\Contracts\Foundation\DeferrableServiceContainer;

class ProviderRepository
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * List of services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * Construct a new finder.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Load available service providers.
     *
     * @param  array  $services
     * @return void
     */
    public function provides(array $services)
    {
        foreach ($services as $provider) {
            $instance = $this->app->resolveProviderClass($provider);

            if ($instance->isDeferred() && $this->app instanceof DeferrableServiceContainer) {
                $this->registerDeferredServiceProvider($instance, $provider);
            } else {
                $this->registerEagerServiceProvider($instance);
            }

            $this->services[] = $provider;
        }
    }

    /**
     * Register deferred service provider.
     *
     * @param  \Illuminate\Support\ServiceProvider  $instance
     * @param  string  $provider
     * @return void
     */
    protected function registerDeferredServiceProvider(ServiceProvider $instance, $provider)
    {
        $services = $this->app->getDeferredServices();

        foreach ($instance->provides() as $service) {
            $services[$service] = $provider;
        }

        $this->app->setDeferredServices($services);
    }

    /**
     * Register eager service provider.
     *
     * @param  \Illuminate\Support\ServiceProvider  $instance
     * @return void
     */
    protected function registerEagerServiceProvider(ServiceProvider $instance)
    {
        $this->app->register($instance);
    }
}