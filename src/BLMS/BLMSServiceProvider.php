<?php

namespace BLMS;

use Illuminate\Support\ServiceProvider;

class BLMSServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishConfiguration();
    }

    /**
     * Configure config path.
     */
    protected function publishConfiguration()
    {
        $this->mergeConfigFrom($this->getConfigFileStub(), 'blms');
        $this->publishes([$this->getConfigFileStub() => $this->getConfigFile()], 'config');
    }

    protected function getConfigFile()
    {
        return function_exists('config_path')
            ? config_path('blms.php')
            : base_path('config/blms.php');
    }

    /**
     * Get the original config file.
     *
     * @return string
     */
    protected function getConfigFileStub()
    {
        return  __DIR__ . '/../../config/blms.php';
    }
}
