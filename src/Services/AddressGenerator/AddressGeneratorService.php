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
use Elliptic\EC;
use kornrunner\Keccak;
use Symfony\Component\HttpFoundation\Response;

class AddressGeneratorService
{
    private $network;
    private $adapter;
    private $slip132;
    private $bitcoin_prefixes;
    private $pubPrefix;
    private $config;
    private $serializer;

    public function __construct()
    {
        $this->network = NetworkFactory::bitcoin();
        $this->adapter = Bitcoin::getEcAdapter();
        $this->slip132 = new Slip132(new KeyToScriptHelper($this->adapter));
        $this->bitcoin_prefixes = new BitcoinRegistry();
        $this->pubPrefix = $this->slip132->p2pkh($this->bitcoin_prefixes);
        $this->config = new GlobalPrefixConfig([
            new NetworkConfig($this->network, [
                $this->pubPrefix,
            ])
        ]);
        $this->serializer = new Base58ExtendedKeySerializer(
            new ExtendedKeySerializer($this->adapter, $this->config)
        );
    }

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
        $key = $this->serializer->parse($this->network, $xpub);
        $child_key = $key->derivePath($path);
        $wallet_address = $child_key->getAddress(new AddressCreator())->getAddress();

        return response([
            'address' => $wallet_address,
        ], Response::HTTP_OK);
    }

    private function erc($path)
    {
        $xpub = config('hd-wallet.erc-xpub');
        $key = $this->serializer->parse($this->network, $xpub);
        $child_key = $key->derivePath($path);
        $public_key = $child_key->getPublicKey()->getHex();

        $ec = new EC('secp256k1');
        $public_key = $ec->keyFromPublic($public_key, 'hex');
        $public_key = json_decode(json_encode($public_key), true);
        $public_key = $public_key['pub'][0] . $public_key['pub'][1];

        $hash = hex2bin($public_key);
        $hash = Keccak::hash($hash, 256);
        $wallet_address = '0x' . substr($hash, -40);

        return response([
            'address' => $wallet_address,
        ], Response::HTTP_OK);
    }

    private function trc($path)
    {
        $xpub = config('hd-wallet.trc-xpub');
        $key = $this->serializer->parse($this->network, $xpub);
        $child_key = $key->derivePath($path);
        $public_key = $child_key->getPublicKey()->getHex();

        $ec = new EC('secp256k1');
        $public_key = $ec->keyFromPublic($public_key, 'hex');
        $public_key = json_decode(json_encode($public_key), true);
        $public_key = $public_key['pub'][0] . $public_key['pub'][1];

        $hash = hex2bin($public_key);
        $hash = Keccak::hash($hash, 256);
        $wallet_address = $this->hexToAddress('41' . substr($hash, -40));

        return response([
            'address' => $wallet_address,
        ], Response::HTTP_OK);
    }
}
