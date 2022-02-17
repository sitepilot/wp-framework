<?php

namespace Sitepilot\WpFramework\Update;

use Sitepilot\WpFramework\Support\ServiceProvider;

class UpdateServiceProvider extends ServiceProvider
{
    /**
     * The available attributes.
     * 
     * Filter: <namespace>/update/<key>
     *
     * @var array
     */
    protected $attributes = [
        'repo' => 'https://wpupdate.sitepilot.cloud/v1'
    ];

    /**
     * Bootstrap service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->add_action('init', 'check_updates', 99);
    }

    /**
     * Check for theme / plugin updates.
     *
     * @return void
     */
    public function check_updates(): void
    {
        $repo = trailingslashit($this->repo) . '?action=get_metadata&slug=' . $this->app->get_namespace();

        \Puc_v4_Factory::buildUpdateChecker(
            $repo,
            $this->app->file,
            $this->app->get_namespace()
        );
    }
}
