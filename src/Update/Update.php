<?php

namespace Sitepilot\WpFramework\Update;

use Sitepilot\WpFramework\Foundation\Application;

class Update
{
    private Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function repo()
    {
        return $this->app->filter('update/repo', 'https://wpupdate.sitepilot.cloud/v1');
    }

    public function slug()
    {
        return $this->app->filter('update/slug', $this->app->namespace());
    }

    public function file()
    {
        return $this->app->filter('update/file', $this->app->file());
    }
}
