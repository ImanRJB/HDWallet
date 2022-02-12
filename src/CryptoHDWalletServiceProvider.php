<?php

namespace HDWallet\Src;

use Illuminate\Support\ServiceProvider;

class HDWalletServiceProvider extends ServiceProvider
{
    public function register()
    {
        // For load config files
        if (file_exists(__DIR__ . '/../config/hd-wallet.php')) {
            $this->mergeConfigFrom(__DIR__ . '/../config/hd-wallet.php', 'hd-wallet');
        }

        $this->app->alias(\CryptoHDWallet\Src\Services\HDWallet\HDWallet::class, 'HDWallet');
        $this->app->register(\CryptoHDWallet\Src\Services\HDWallet\HDWalletServiceProvider::class);
    }
}
