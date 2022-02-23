<?php

namespace Sitepilot\WpFramework\Update;

use Sitepilot\WpFramework\Support\ServiceProvider;

class UpdateServiceProvider extends ServiceProvider
{
    protected Update $update;

    /**
     * Register application services.
     */
    public function register(): void
    {
        $this->app->alias(Update::class, 'update');
    }

    /**
     * Bootstrap application services and hooks.
     */
    public function boot(): void
    {
        $this->add_action('init', 'build_update_checker', 99);
    }

    /**
     * Build the update checker.
     */
    public function build_update_checker(): void
    {
        $repo = trailingslashit($this->update->repo()) . '?action=get_metadata&slug=' . $this->update->slug();

        \Puc_v4_Factory::buildUpdateChecker(
            $repo,
            $this->update->file(),
            $this->update->slug()
        );
    }
}
