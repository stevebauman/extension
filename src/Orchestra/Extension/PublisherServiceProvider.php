<?php namespace Orchestra\Extension;

use Illuminate\Support\ServiceProvider;

class PublisherServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerMigration();
		$this->registerAssetPublisher();
	}

	/**
	 * Register the service provider for Orchestra Platform migrator.
	 *
	 * @return void
	 */
	protected function registerMigration()
	{
		$this->app->make('migration.repository');
		
		$this->app['orchestra.publisher.migrate'] = $this->app->share(function ($app)
		{
			return new Publisher\MigrateManager($app);
		});
	}

	/**
	 * Register the service provider for Orchestra Platform asset publisher.
	 *
	 * @return void
	 */
	protected function registerAssetPublisher()
	{
		$this->app['orchestra.publisher.asset'] = $this->app->share(function ($app)
		{
			return new Publisher\AssetManager($app);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('orchestra.publisher.migrate', 'orchestra.publisher.asset');
	}
}