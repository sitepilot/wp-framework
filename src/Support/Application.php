<?php

namespace Sitepilot\WpFramework\Support;

use Sitepilot\WpFramework\Traits\HasHooks;
use Sitepilot\WpFramework\Acf\AcfServiceProvider;
use Sitepilot\WpFramework\Support\ServiceProvider;
use Sitepilot\WpFramework\Admin\AdminServiceProvider;

/**
 * @property AcfServiceProvider $acf
 * @property AdminServiceProvider $admin
 */
abstract class Application
{
    use HasHooks;

    /**
     * The namespace for the application.
     */
    protected string $namespace;

    /**
     * The file path for the application.
     */
    public string $file;

    /**
     * The base path for the application.
     */
    protected string $path;

    /**
     * Indicates if the application has "booted".
     */
    protected bool $booted = false;

    /**
     * Hook for booting the providers and application.
     */
    protected string $boot_hook = 'after_setup_theme';

    /**
     * The providers required by the application.
     */
    protected array $providers = [];

    /**
     * The deferred providers required by the application.
     */
    protected array $deferred_providers = [
        'acf' => AcfServiceProvider::class,
        'admin' => AdminServiceProvider::class
    ];

    /**
     * All of the registered providers.
     *
     * @var ServiceProvider[]
     */
    protected array $loaded_providers = [];

    /**
     * Create a new application instance.
     */
    public function __construct(string $namespace, string $file)
    {
        $this->file = $file;
        $this->path = dirname($file);
        $this->namespace = $namespace;

        $__register = '__register';
        if (method_exists($this, $__register)) {
            $this->$__register();
        }

        $this->register();

        foreach ($this->providers as $alias => $provider) {
            $this->register_provider($alias, $provider);
        }

        $this->add_action($this->boot_hook, 'boot_application');

        do_action($this->get_namespace('registered'), $this);
    }

    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Boot application and service providers.
     */
    public function boot_application(): void
    {
        foreach ($this->loaded_providers as $provider) {
            $provider->boot();
        }

        $__boot = '__boot';
        if (method_exists($this, $__boot)) {
            $this->$__boot();
        }

        $this->boot();

        $this->booted = true;

        do_action($this->get_namespace('booted'), $this);
    }

    /**
     * Returns the application version.
     */
    public function get_version(): string
    {
        return '';
    }

    /**
     * Returns the plugin script version.
     */
    public function get_script_version(): string
    {
        $version = $this->get_version();

        if (strpos($version, 'dev') !== false) {
            $version = time();
        }

        return $version;
    }

    /**
     * Get the application namespace.
     */
    public function get_namespace($path = '', $separator = '/'): string
    {
        return $this->namespace . ($path ? $separator . $path : '');
    }

    /**
     * Get path to the application.
     */
    public function get_path(string $path = ''): string
    {
        return $this->path . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * Register a provider with the application.
     *
     * @param ServiceProvider|string $provider
     */
    public function register_provider(string $alias, $provider, bool $force = false): ServiceProvider
    {
        if (($registered = $this->loaded_providers[$alias] ?? null) && !$force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your provider classes.
        if (is_string($provider)) {
            $provider = new $provider($alias, $this);
        }

        $provider->register();

        $this->loaded_providers[$alias] = $provider;

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->booted) {
            $provider->boot();
        }

        return $provider;
    }

    /**
     * Add providers to the application.
     */
    public function add_providers(array $providers): self
    {
        $this->providers = array_merge($this->providers, $providers);

        return $this;
    }

    /**
     * Add deferred providers to the application.
     */
    public function add_deffered_providers(array $deferred_providers): self
    {
        $this->deferred_providers = array_merge($this->deferred_providers, $deferred_providers);

        return $this;
    }

    /**
     * Dynamically retrieve provider by alias.
     */
    public function __get(string $alias): ServiceProvider
    {
        if (array_key_exists($alias, $this->providers)) {
            return $this->register_provider($alias, $this->providers[$alias]);
        } elseif (array_key_exists($alias, $this->deferred_providers)) {
            return $this->register_provider($alias, $this->deferred_providers[$alias]);
        }

        wp_die("Service provider [$alias] is not registered or loaded.");
    }
}
