<?php

namespace Sitepilot\WpFramework\Support;

use Sitepilot\WpFramework\Traits\HasHooks;
use Sitepilot\WpFramework\Support\Application;
use Sitepilot\WpFramework\Traits\HasAttributes;

abstract class ServiceProvider
{
    use HasHooks, HasAttributes;

    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * The service provider alias.
     *
     * @var string
     */
    protected string $alias;

    /**
     * Create a new service provider instance.
     *
     * @param Application $app
     * @return void
     */
    public function __construct(string $alias, Application $app)
    {
        $this->alias = $alias;
        $this->app = $app;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get service provider namespace.
     *
     * @param string $path
     * @param string $separator
     * @return string
     */
    public function get_namespace(string $path = '', string $separator = '/'): string
    {
        return $this->app->get_namespace($this->alias . ($path ?  $separator . $path : ''), $separator);
    }
}
