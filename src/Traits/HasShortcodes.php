<?php

namespace Sitepilot\WpFramework\Traits;

trait HasShortcodes
{
    /**
     * Adds a new shortcode.
     *
     * @param string $tag
     * @param mixed $callback
     * @return void
     */
    public function add_shortcode(string $tag, $callback): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        add_shortcode($tag, $callback);
    }
}
