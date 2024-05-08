<?php

namespace Spryng\SpryngRestApi;

use Illuminate\Support\ServiceProvider;
use Spryng\SpryngRestApi\Exceptions\InvalidConfiguration;
use Spryng\SpryngRestApi\Resources\MessageClient;

class SpryngServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(Spryng::class)
            ->give(function () {
                $config = config('services.spryng');

                if (is_null($config)) {
                    throw InvalidConfiguration::configurationNotSet();
                }

                return new MessageClient(new Spryng(), $config['access_key']);
            });
    }
}
