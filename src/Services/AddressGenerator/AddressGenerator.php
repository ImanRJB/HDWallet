<?php

namespace HDWallet\Src\Services\AddressGenerator;

use Illuminate\Support\Facades\Facade;

/**
 * @method static getNewAddress($type, $path)
 */

class AddressGenerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'addressGenerator';
    }
}
