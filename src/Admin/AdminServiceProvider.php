<?php

namespace Sitepilot\WpFramework\Admin;

use WP_Admin_Bar;
use Sitepilot\WpFramework\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Add admin bar node.
     *
     * @see https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/
     */
    public function add_admin_bar_node(array $node): void
    {
        add_action('admin_bar_menu', function (WP_Admin_Bar $admin_bar) use ($node) {
            $admin_bar->add_node($node);
        });
    }

    /**
     * Add admin notice.
     *
     * @see https://developer.wordpress.org/reference/hooks/admin_notices/
     */
    public function add_notice(string $message, string $type = 'info', bool $dismissable = false): void
    {
        add_action('admin_notices', function () use ($message, $type, $dismissable) {
            $class = "notice notice-$type" . ($dismissable ? 'is-dismissible' : '');
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        });
    }
}
