<?php

namespace HdWallet\Src\Services\AddressGenerator;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AddressGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('addressGenerator', function() {
            return App::make('HdWallet\Src\Services\AddressGenerator\AddressGeneratorService');
        });
    }
}
