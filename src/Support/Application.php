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
     *
     * @var string
     */
    protected $namespace;

    /**
     * The file path for the application.
     *
     * @var string
     */
    public string $file;

    /**
     * The base path for the application.
     *
     * @var string
     */
    protected string $path;

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * Hook for booting the providers and application.
     *
     * @var string
     */
    protected $boot_hook = 'after_setup_theme';

    /**
     * The providers required by the application.
     *
     * @var array
     */
    protected $providers = [];

    /**
     * The deferred providers required by the application.
     *
     * @var array
     */
    protected $deferred_providers = [
        'acf' => AcfServiceProvider::class,
        'admin' => AdminServiceProvider::class
    ];

    /**
     * All of the registered providers.
     *
     * @var ServiceProvider[]
     */
    protected $loaded_providers = [];

    /**
     * Create a new application instance.
     *
     * @param  string|null $basePath
     * @return void
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
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Boot application and service providers.
     *
     * @return void
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
     *
     * @return string
     */
    public function get_version(): string
    {
        return '';
    }

    /**
     * Returns the plugin script version.
     *
     * @return string
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
     *
     * @return string
     */
    public function get_namespace($path = '', $separator = '/'): string
    {
        return $this->namespace . ($path ? $separator . $path : '');
    }

    /**
     * Get path to the application.
     *
     * @param string $path
     * @return string
     */
    public function get_path(string $path = ''): string
    {
        return $this->path . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * Register a provider with the application.
     *
     * @param ServiceProvider|string $provider
     * @param bool $force
     * @return Module
     */
    public function register_provider(string $alias, ServiceProvider|string $provider, $force = false)
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
     * 
     * @return self
     */
    public function add_providers(array $providers): self
    {
        $this->providers = array_merge($this->providers, $providers);

        return $this;
    }

    /**
     * Add deferred providers to the application.
     * 
     * @return self
     */
    public function add_deffered_providers(array $deferred_providers): self
    {
        $this->deferred_providers = array_merge($this->deferred_providers, $deferred_providers);

        return $this;
    }

    /**
     * Get provider.
     *
     * @param string $name
     * @return void
     */
    public function __get(string $alias)
    {
        if (array_key_exists($alias, $this->providers)) {
            return $this->register_provider($alias, $this->providers[$alias]);
        } elseif (array_key_exists($alias, $this->deferred_providers)) {
            return $this->register_provider($alias, $this->deferred_providers[$alias]);
        }

        wp_die("Service provider [$alias] is not registered or loaded.");
    }
}
