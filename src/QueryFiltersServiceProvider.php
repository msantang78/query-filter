<?php namespace Msantang\QueryFilters;

use Illuminate\Support\ServiceProvider;

class QueryFiltersServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/queryfilters.php' => config_path('queryfilters.php'),
        ],'config');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->mergeConfigFrom(
            __DIR__.'/../config/queryfilters.php', 'queryfilters'
        );
		$this->registerQueryFilterGeneratorCommand();
	}

    private function registerQueryFilterGeneratorCommand()
    {
        $this->app->singleton('command.queryfilter.make', function ($app) {
            return $app['Msantang\QueryFilters\Commands\MakeQueryFilter'];
        });
        $this->commands('command.queryfilter.make');
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
