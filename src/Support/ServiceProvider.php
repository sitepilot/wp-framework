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
     */
    protected Application $app;

    /**
     * The service provider alias.
     */
    protected string $alias;

    /**
     * Create a new service provider instance.
     */
    public function __construct(string $alias, Application $app)
    {
        $this->alias = $alias;
        $this->app = $app;
    }

    /**
     * Register service providers.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap service provider.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get service provider namespace.
     */
    public function get_namespace(string $path = '', string $separator = '/'): string
    {
        return $this->app->get_namespace($this->alias . ($path ?  $separator . $path : ''), $separator);
    }
}
