<?php

namespace HDWallet\Src\Services\AddressGenerator;

use App\Models\Address;
use App\Services\BitcoinHD\BitcoinHD;
use App\Services\EthereumHD\EthereumHD;
use App\Services\TronHD\TronHD;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\GlobalPrefixConfig;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig;
use BitWasp\Bitcoin\Key\Deterministic\Slip132\Slip132;
use BitWasp\Bitcoin\Key\KeyToScript\KeyToScriptHelper;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Network\Slip132\BitcoinRegistry;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;
use Symfony\Component\HttpFoundation\Response;

class AddressGeneratorService
{
    public function getNewAddress($type, $path)
    {
        if (!in_array($type, get_class_methods($this))) {
            return response(['message' => 'Type not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->$type($path);
    }

    private function btc($path)
    {
        $xpub = config('hd-wallet.btc-xpub');

        $network = NetworkFactory::bitcoin();

        $adapter = Bitcoin::getEcAdapter();
        $slip132 = new Slip132(new KeyToScriptHelper($adapter));
        $bitcoin_prefixes = new BitcoinRegistry();

        $pubPrefix = $slip132->p2pkh($bitcoin_prefixes);

        $config = new GlobalPrefixConfig([
            new NetworkConfig($network, [
                $pubPrefix,
            ])
        ]);

        $serializer = new Base58ExtendedKeySerializer(
            new ExtendedKeySerializer($adapter, $config)
        );

        $key = $serializer->parse($network, $xpub);
        $child_key = $key->derivePath($path);

        $wallet_address = $child_key->getAddress(new AddressCreator())->getAddress();

        return response([
            'address' => $wallet_address,
        ], Response::HTTP_OK);
    }

    private function erc20($path)
    {
        return response(EthereumHD::getAddressWithHdPath($path), Response::HTTP_OK);
    }

    private function trc20($path)
    {
        return response(TronHD::getAddressWithHdPath($path), Response::HTTP_OK);
    }
}
