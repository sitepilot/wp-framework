<?php

namespace Sitepilot\WpFramework\Traits;

trait HasHooks
{
    /**
     * Get namespaced hook name.
     */
    public function get_hook(string $hook): string
    {
        if (method_exists($this, 'get_namespace')) {
            return $this->get_namespace($hook);
        }

        return $this->app->get_namespace($hook);
    }

    /**
     * Calls the callback functions that have been added to an action hook.
     */
    public function action(string $hook, ...$args): void
    {
        do_action($this->get_hook($hook), ...$args);
    }

    /**
     * Calls the callback functions that have been added to a filter hook.
     *
     * @param mixed $value
     */
    public function filter(string $hook, $value)
    {
        return apply_filters($this->get_hook($hook), $value);
    }

    /**
     * Adds a callback to a filter hook.
     */
    public function add_filter(string $hook, $callback, ...$args): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        add_filter($hook, $callback, ...$args);
    }

    /**
     * Returns a value to a filter hook.
     *
     * @param mixed $value
     */
    public function add_filter_value(string $hook, $value, ...$args): void
    {
        add_filter($hook, function () use ($value) {
            return $value;
        }, ...$args);
    }

    /**
     * Adds a callback to a action hook.
     *
     * @param mixed $callback
     */
    public function add_action(string $hook, $callback, ...$args): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        add_action($hook, $callback, ...$args);
    }
}
