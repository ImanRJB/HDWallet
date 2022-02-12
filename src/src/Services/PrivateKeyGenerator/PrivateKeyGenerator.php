<?php

namespace HDWallet\Src\Services\PrivateKeyGenerator;

use Illuminate\Support\Facades\Facade;

/**
 * @method static getAddressWithPrivateKey($type, $path)
 */

class PrivateKeyGenerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'privateKeyGenerator';
    }
}
