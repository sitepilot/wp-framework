<?php

namespace Sitepilot\WpFramework\Admin;

use Sitepilot\WpFramework\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Autoloaded type aliases.
     */
    protected array $aliases = [
        'admin' => Admin::class
    ];
}
