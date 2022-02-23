<?php

namespace Sitepilot\WpFramework\Support;

use Sitepilot\WpFramework\Traits\HasHooks;
use Sitepilot\WpFramework\Traits\HasShortcodes;
use Sitepilot\WpFramework\Foundation\Application;

abstract class ServiceProvider
{
    use HasHooks, HasShortcodes;

    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * Autoloaded service providers.
     */
    protected array $providers = [];

    /**
     * Autoloaded type aliases.
     */
    protected array $aliases = [];

    /**
     * All of the registered register callbacks.
     */
    protected array $register_callbacks = [];

    /**
     * All of the registered booting callbacks.
     */
    protected array $booting_callbacks = [];

    /**
     * All of the registered booted callbacks.
     */
    protected array $booted_callbacks = [];

    /**
     * Create a new service provider instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->registered(function () {
            foreach ($this->providers as $provider) {
                $this->app->register($provider);
            }

            foreach ($this->aliases as $alias => $abstract) {
                $this->app->alias($abstract, $alias);
            }
        });

        $this->booting(function () {
            $this->resolve_properties();
        });
    }

    /**
     * Register application services and filters.
     */
    public function register(): void
    {
        //
    }

    /**
     * Register a callback to be run after the "register" method is called.
     *
     * @param \Closure $callback
     * @return void
     */
    public function registered(\Closure $callback)
    {
        $this->register_callbacks[] = $callback;
    }

    /**
     * Register a callback to be run before the "boot" method is called.
     *
     * @param \Closure $callback
     * @return void
     */
    public function booting(\Closure $callback)
    {
        $this->booting_callbacks[] = $callback;
    }

    /**
     * Register a callback to be run after the "boot" method is called.
     *
     * @param \Closure $callback
     * @return void
     */
    public function booted(\Closure $callback)
    {
        $this->booted_callbacks[] = $callback;
    }

    /**
     * Call the register callbacks.
     */
    public function call_register_callbacks(): void
    {
        foreach ($this->register_callbacks as $callback) {
            $this->app->call($callback);
        }
    }

    /**
     * Call the booting callbacks.
     */
    public function call_booting_callbacks(): void
    {
        foreach ($this->booting_callbacks as $callback) {
            $this->app->call($callback);
        }
    }

    /**
     * Call the booted callbacks.
     */
    public function call_booted_callbacks(): void
    {
        foreach ($this->booted_callbacks as $callback) {
            $this->app->call($callback);
        }
    }

    /**
     * Proxy to app namespace.
     */
    public function namespace(string $path = '', string $separator = '/'): string
    {
        return $this->app->namespace($path, $separator);
    }

    /**
     * Automatically resolve typed properties.
     */
    public function resolve_properties(): void
    {
        $reflection = new \ReflectionClass(get_called_class());

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PROTECTED) as $property) {
            if (
                $property->getType()
                && empty($this->{$property->getName()})
                && !$property->getType()->isBuiltin()
            ) {
                $this->{$property->getName()} = $this->app->get($property->getType()->getName());
            }
        }
    }
}
