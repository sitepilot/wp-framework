<?php

namespace Sitepilot\WpFramework\Foundation;

use Sitepilot\WpFramework\Support\Arr;
use Sitepilot\WpFramework\Traits\HasHooks;
use Sitepilot\WpFramework\Container\Container;
use Sitepilot\WpFramework\Traits\HasShortcodes;
use Sitepilot\WpFramework\Support\ServiceProvider;

class Application extends Container
{
    use HasHooks, HasShortcodes;

    /**
     * The cached application version.
     */
    protected ?string $version = null;

    /**
     * The path to the application boot file.
     */
    protected string $file;

    /**
     * The base path to the application.
     */
    protected string $base_path;

    /**
     * The base url to the application.
     */
    protected string $base_url;

    /**
     * The application namespace.
     */
    protected string $namespace;

    /**
     * The application boot hook.
     */
    protected string $boot_hook = 'after_setup_theme';

    /**
     * Indicates if the application has "booted".
     */
    protected bool $booted = false;

    /**
     * All of the registered service providers.
     *
     * @var ServiceProvider[]
     */
    protected $service_providers = [];

    /**
     * The names of the loaded service providers.
     */
    protected array $loaded_providers = [];

    /**
     * Create a new application instance.
     */
    public function __construct(string $namespace, string $file)
    {
        $this->namespace = $namespace;

        $this->set_paths($file);

        $this->instance(self::class, $this);

        $this->add_action($this->boot_hook, 'boot');

        $this->action('registered');
    }

    /**
     * Set the base paths for the application.
     */
    public function set_paths(string $file): void
    {
        $this->file = $file;
        $this->base_path = dirname($file);

        if ($this->is_plugin()) {
            $this->base_url = plugins_url('', $this->file);
        } else {
            $theme = wp_get_theme(basename($this->path()));
            $this->base_url = $theme->get_stylesheet_directory_uri();
        }
    }

    /**
     * Register a service provider with the application.
     *
     * @param  ServiceProvider|string  $provider
     * @param  bool  $force
     * @return ServiceProvider
     */
    public function register($provider, $force = false)
    {
        if (($registered = $this->get_provider($provider)) && !$force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolve_provider($provider);
        }

        $provider->register();

        // If there are bindings / singletons set as properties on the provider we
        // will spin through them and register them with the application, which
        // serves as a convenience layer while registering a lot of bindings.
        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons as $key => $value) {
                $this->singleton($key, $value);
            }
        }

        $this->mark_as_registered($provider);

        $provider->call_register_callbacks();

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->is_booted()) {
            $this->boot_provider($provider);
        }

        return $provider;
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->is_booted()) {
            return;
        }

        array_walk($this->service_providers, function ($p) {
            $this->boot_provider($p);
        });

        $this->booted = true;

        $this->action('booted');
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param  ServiceProvider|string  $provider
     * @return ServiceProvider|null
     */
    public function get_provider($provider)
    {
        return array_values($this->get_providers($provider))[0] ?? null;
    }

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param  ServiceProvider|string  $provider
     * @return array
     */
    public function get_providers($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return Arr::where($this->service_providers, function ($value) use ($name) {
            return $value instanceof $name;
        });
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param  string  $provider
     * @return ServiceProvider
     */
    public function resolve_provider($provider)
    {
        return new $provider($this);
    }

    /**
     * Boot the given service provider.
     *
     * @param  ServiceProvider  $provider
     * @return void
     */
    protected function boot_provider(ServiceProvider $provider)
    {
        $provider->call_booting_callbacks();

        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        $provider->call_booted_callbacks();
    }

    /**
     * Mark the given provider as registered.
     *
     * @param  ServiceProvider  $provider
     * @return void
     */
    protected function mark_as_registered($provider)
    {
        $this->service_providers[] = $provider;

        $this->loaded_providers[get_class($provider)] = true;
    }

    /**
     * Get the namespaced path to the application.
     */
    public function namespace(string $path = '', string $separator = '/')
    {
        return $this->namespace . ($path ? $separator . $path : $path);
    }

    /**
     * Get the application version.
     */
    public function version(): ?string
    {
        if ($this->version) {
            return $this->version;
        }

        if ($this->is_plugin()) {
            $plugin = get_file_data($this->file, [
                'version' => 'Version'
            ], 'plugin');

            $version = $plugin['version'] ?? null;
        } else {
            $theme = wp_get_theme(basename($this->path()));
            $version = $theme->get('Version') ? $theme->get('Version') : null;
        }

        $this->version = $version;

        return $this->version;
    }

    /**
     * Returns the application script version.
     */
    public function script_version(): string
    {
        $version = $this->version();

        if ($this->is_dev()) {
            $version = time();
        }

        return $version;
    }

    /**
     * Get the url path of the application.
     */
    public function url(string $path = ''): string
    {
        return $this->base_url . ($path ? '/' . $path : $path);
    }

    /**
     * Get the path to the application boot file.
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * Get the base path of the application.
     */
    public function path(string $path = ''): string
    {
        return $this->base_path . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the public directory.
     */
    public function public_path(string $path = ''): string
    {
        return $this->filter('public_path', $this->path('public')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the url to the public directory.
     */
    public function public_url(string $path = ''): string
    {
        return $this->filter('public_url', $this->url('public')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the resources directory.
     */
    public function resource_path(string $path = ''): string
    {
        return $this->filter('resource_path', $this->path('resources')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the views directory.
     */
    public function view_path(string $path = ''): string
    {
        return $this->filter('view_path', $this->resource_path('views')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the public / web directory.
     */
    public function lang_path(string $path = ''): string
    {
        return $this->filter('lang_path', $this->resource_path('lang')) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the storage directory.
     */
    public function storage_path(string $path = ''): string
    {
        return $this->filter('storage_path', wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $this->namespace()) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Determine if the application is a plugin.
     */
    public function is_plugin(): bool
    {
        return strpos($this->base_path, WP_PLUGIN_DIR) !== false;
    }

    /**
     * Determine if the application is a theme.
     */
    public function is_theme(): bool
    {
        $themes_dir = dirname(get_template_directory());

        return strpos($this->base_path, $themes_dir) === true;
    }

    /**
     * Determine if the application is in development mode.
     */
    public function is_dev(): bool
    {
        return strpos($this->version(), 'dev') !== false;
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function is_booted()
    {
        return $this->booted;
    }
}
