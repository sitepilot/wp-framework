<?php

namespace Sitepilot\WpFramework\Traits;

trait HasShortcodes
{
    /**
     * Merge shortcode attributes with defaults.
     *
     * @param array $atts
     */
    public function shortcode_atts($atts, array $defaults): array
    {
        return array_merge($defaults, is_array($atts) ? $atts : []);
    }

    /**
     * Add namespaced shortcode.
     *
     * @param callable|string $callback
     */
    public function add_shortcode(string $tag, $callback): void
    {
        if (is_string($callback)) {
            $callback = [$this, $callback];
        }

        add_shortcode($this->namespace($tag, '_'), $callback);
    }
}
