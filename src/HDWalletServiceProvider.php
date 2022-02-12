<?php

namespace HDWallet\Src;

use Illuminate\Support\ServiceProvider;

class HDWalletServiceProvider extends ServiceProvider
{
    public function register()
    {
        // For load config files
        if (file_exists(__DIR__ . '/../src/config/hd-wallet.php')) {
            $this->mergeConfigFrom(__DIR__ . '/../src/config/hd-wallet.php', 'hd-wallet');
        }

        $this->app->alias(\HDWallet\Src\Services\AddressGenerator\AddressGenerator::class, 'AddressGenerator');
        $this->app->register(\HDWallet\Src\Services\AddressGenerator\AddressGeneratorServiceProvider::class);
    }
}
