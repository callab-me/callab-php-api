<?php


namespace Callab\Api;

use Illuminate\Support\ServiceProvider;

/**
 * Class BaseServiceProvider
 * @package Callab\Api
 */
class BaseServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->singleton(CallabClient::class, function () {
            return new CallabClient(
                env('CALLAB_API_URL', 'https://app.callab.me'),
                env('CALLAB_KEY'),
                env('CALLAB_SECRET')
            );
        });
    }
}