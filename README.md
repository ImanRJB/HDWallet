# Cryptocurrency HD Wallet for multiple networks

### Put below parameters in <code>.env</code> file
##### (XPRV is only used in private key generation)

```dotenv
BTC_XPUB=
ERC_XPUB=
TRC_XPUB=

BTC_XPRV=
ERC_XPRV=
TRC_XPRV=

```

### Generate address using network and HD path

```php
return AddressGenerator::getAddressWithPrivateKey('btc', '0/0');
```

### Generate private key and address using network and HD path

```php
return PrivateKeyGenerator::getAddressWithPrivateKey('btc', '0/0');
```

#### Supported networks:

Network | Symbol
---------------------|--------
Bitcoin              |btc
Ethereum             |eth
Tron                 |trc