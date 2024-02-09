<?php
/**
 * SigningKey.php
 * (c) PT Cels Teknologi Indonesia, 2024.
 * 
 * This is the base class that hold keys & do signings
 * for EVM-compatible networks
 */

namespace Ephers\Wallet;

use Ephers\Helpers\BinaryString;
use Ephers\Helpers\Secp256k1;
use Mdanter\Ecc\Curves\CurveFactory;
use Mdanter\Ecc\Curves\SecgCurve;

class SigningKey
{
    public function __construct(protected BinaryString $privateKey) { }

    public function privateKey(): BinaryString
    {
        return $this->privateKey;
    }

    public function publicKey(): BinaryString
    {
        return static::computePublicKey($this->privateKey);
    }

    public function compressedPublicKey(): BinaryString
    {
        return static::computePublicKey($this->privateKey, compress: true);
    }

    public static function computePublicKey(BinaryString $key, bool $compress = false): BinaryString
    {
        if ($key->len() === 32) {
            $g = CurveFactory::getGeneratorByName(SecgCurve::NAME_SECP_256K1);

            return Secp256k1::pointToHex(
                $g->mul($key->toGmp()),
                $compress,
            );
        }
        if ($key->len() === 64) {
            // Raw public key, modify prefix 0x -> 0x04
            $key = BinaryString::fromHex(\preg_replace(
                pattern: '/^0x/',
                replacement: '0x04',
                subject: $key->toHex(),
                limit: 1,
            ));
        }
        
        return Secp256k1::pointToHex(
            Secp256k1::hexToPoint($key->toHex()),
            $compress,
        );
    }
}
