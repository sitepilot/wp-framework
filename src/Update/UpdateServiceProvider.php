<?php

namespace Sitepilot\WpFramework\Update;

use Sitepilot\WpFramework\Support\ServiceProvider;

class UpdateServiceProvider extends ServiceProvider
{
    /**
     * filter: <namespace>/update/<key>
     */
    protected array $attributes = [
        'repo' => 'https://wpupdate.sitepilot.cloud/v1'
    ];

    /**
     * Bootstrap service provider.
     */
    public function boot(): void
    {
        $this->add_action('init', 'build_update_checker', 99);
    }

    /**
     * Build application update checker.
     */
    public function build_update_checker(): void
    {
        $repo = trailingslashit($this->repo) . '?action=get_metadata&slug=' . $this->app->get_namespace();

        \Puc_v4_Factory::buildUpdateChecker(
            $repo,
            $this->app->file,
            $this->app->get_namespace()
        );
    }
}
