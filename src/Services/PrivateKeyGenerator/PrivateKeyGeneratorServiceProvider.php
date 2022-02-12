<?php

namespace HDWallet\Src\Services\PrivateKeyGenerator;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class PrivateKeyGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('privateKeyGenerator', function() {
            return App::make('HDWallet\Src\Services\PrivateKeyGenerator\PrivateKeyGeneratorService');
        });
    }
}
