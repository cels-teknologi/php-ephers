<?php

namespace Ephers\Helpers\Tron;

use Ephers\Encodings\Base58;
use Ephers\Helpers\BinaryString;
use kornrunner\Keccak;

final class Address
{
    public static function of(BinaryString $publicKey): string
    {
        $address = BinaryString::of("\x41" . $publicKey->raw());

        if ($publicKey->len() === 65 && $publicKey->at(0) === 4) {
            // This is an uncompressed public key
            $address = BinaryString::of(
                "\x41" . \substr(Keccak::hash(
                    $publicKey->split(1)->raw(),
                    256,
                    true,
                ), 12)
            );
        }
        else if ($publicKey->len() === 33
            && ($evenY = $publicKey->at(0))
            && ($evenY === 2 || $evenY === 3)
        ) {
            // This is a compressed public key
            $address = BinaryString::of(
                "\x41" . \substr(Keccak::hash(
                    $publicKey->split(1)->raw(),
                    256,
                    true,
                ), 12)
            );
        }
        else if ($publicKey->len() === 21 && $publicKey->at(0) === 0x41) {
            // This is a valid tron address already
            $address = (clone $publicKey);
        }
        $sha256 = BinaryString::of(
            \hash('sha256', \hash('sha256', $address->raw(), true), true)
        );
        $checksum = $sha256->split(0, 4);

        return Base58::encode(
            BinaryString::of($address->raw() . $checksum->raw())
        );
    }

    public static function fromTron(string $publicKey): BinaryString
    {
        $checkedAddress = BinaryString::fromHex(Base58::decode($publicKey));
        $address = $checkedAddress->split(0, $checkedAddress->len() - 4);
        $checksum = $checkedAddress->split($checkedAddress->len() - 4);
        
        $sha256 = BinaryString::of(
            \hash('sha256', \hash('sha256', $address->raw(), true), true)
        );
        $expectedChecksum = $sha256->split(0, 4);
        
        if ($expectedChecksum->compare($checksum) === 0) {
            return $address->split(1);
        }
        
        throw new \InvalidArgumentException('Invalid address');
    }
}
