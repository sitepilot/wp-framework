<?php

namespace Sitepilot\WpFramework\Traits;

trait HasHooks
{
    /**
     * Get namespaced hook name.
     *
     * @param string $hook
     * @return void
     */
    public function get_hook(string $hook)
    {
        if (method_exists($this, 'get_namespace')) {
            return $this->get_namespace($hook);
        }

        return $this->app->get_namespace($hook);
    }

    /**
     * Calls the callback functions that have been added to an action hook.
     *
     * @param string $name
     * @param array ...$args
     * @return void
     */
    public function action(string $hook, ...$args): void
    {
        do_action($this->get_hook($hook), ...$args);
    }

    /**
     * Calls the callback functions that have been added to a filter hook.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function filter(string $hook, mixed $value)
    {
        return apply_filters($this->get_hook($hook), $value);
    }

    /**
     * Adds a callback to a filter hook.
     *
     * @param string $hook
     * @param mixed $callback
     * @param integer $priority
     * @param integer $accepted_args
     * @return void
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
     * @param string $hook
     * @param mixed $value
     * @param integer $priority
     * @param integer $accepted_args
     * @return void
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
     * @param string $hook
     * @param mixed $callback
     * @param integer $priority
     * @param integer $accepted_args
     * @return void
     */
    public function add_action(string $hook, $callback, ...$args): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        add_action($hook, $callback, ...$args);
    }
}
