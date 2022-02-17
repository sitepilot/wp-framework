<?php

namespace Sitepilot\WpFramework\Traits;

trait HasShortcodes
{
    /**
     * @param callable|string $callback
     */
    public function add_shortcode(string $tag, $callback): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        add_shortcode($tag, $callback);
    }
}
