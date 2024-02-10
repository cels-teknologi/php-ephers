<?php

namespace Ephers\Helpers\Tron;

use Ephers\Helpers\Base58;
use Ephers\Helpers\BinaryString;
use Ephers\Wallet\HDWallet;
use kornrunner\Keccak;

final class Address
{
    public static function of(BinaryString $extendedPublicKey): string
    {
        $address = BinaryString::of(
            "\x41" . \substr(Keccak::hash(
                $extendedPublicKey->split(1)->raw(),
                256,
                true,
            ), 12)
        );
        $sha256_0 = \hash('sha256', $address->raw(), true);
        $sha256_1 = BinaryString::of(\hash('sha256', $sha256_0, true));
        $checksum = $sha256_1->split(0, 4);

        return Base58::encode(
            BinaryString::of($address->raw() . $checksum->raw())
        );
    }
}
