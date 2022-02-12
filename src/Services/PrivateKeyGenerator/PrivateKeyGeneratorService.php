<?php

namespace HdWallet\Src\Services\PrivateKeyGenerator;

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
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use CryptoHDWallet\Src\Base58\Base58Check;
use Elliptic\EC;
use kornrunner\Keccak;
use Symfony\Component\HttpFoundation\Response;

class PrivateKeyGeneratorService
{
    private $network;
    private $adapter;
    private $slip132;
    private $bitcoin_prefixes;
    private $pubPrefix;
    private $config;
    private $serializer;
    private $child_key;
    private $public_key;
    private $private_key;
    private $public_key_ec;
    private $public_key_hash;

    public function getAddressWithPrivateKey($type, $path)
    {
        if (!in_array($type, get_class_methods($this))) {
            return response(['message' => 'Type not found'], Response::HTTP_NOT_FOUND);
        }

        $xpub = config('hd-wallet.' . $type . '-xprv');
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
        $key = $this->serializer->parse($this->network, $xpub);
        $this->child_key = $key->derivePath($path);
        $this->public_key = $this->child_key->getPublicKey()->getHex();
        $this->private_key = $this->child_key->getPrivateKey()->getHex();

        $ec = new EC('secp256k1');
        $this->public_key_ec = $ec->keyFromPublic($this->public_key, 'hex');
        $this->public_key_ec = json_decode(json_encode($this->public_key_ec), true);
        $this->public_key_ec = $this->public_key_ec['pub'][0] . $this->public_key_ec['pub'][1];

        $this->public_key_hash = hex2bin($this->public_key_ec);
        $this->public_key_hash = Keccak::hash($this->public_key_hash, 256);

        $address_detail = $this->$type();
        return response([
            'address' => $address_detail['address'],
            'private_key' => $address_detail['private_key'],
            'path' => $path,
        ], Response::HTTP_OK);
    }

    private function btc()
    {
        $privKeyFactory = new PrivateKeyFactory();
        $privateKey = $privKeyFactory->fromHexCompressed($this->private_key);

        return [
            'address' => $this->child_key->getAddress(new AddressCreator())->getAddress(),
            'private_key' => $privateKey->toWif($this->network)
        ];
    }

    private function erc()
    {
        return [
            'address' => '0x' . substr($this->public_key_hash, -40),
            'private_key' => '0x' . $this->private_key
        ];
    }

    private function trc()
    {
        return [
            'address' => hexToAddress('41' . substr($this->public_key_hash, -40)),
            'private_key' => $this->private_key
        ];
    }
}
