<?php

namespace Ephers\Ethereum\Address;

use Ephers\Helpers\BinaryString;
use Ephers\Wallet\HDWallet;
use kornrunner\Keccak;

class Address
{
    public static function compute(HDWallet $wallet): BinaryString
    {
        return BinaryString::of(
            Keccak::hash(
                $wallet->signingKey->publicKey()->split(1)->raw(),
                256,
                true
            ),
        )->split(-20);
    }

    public static function validate(BinaryString $address): bool
    {
        // TODO: 
        return true;
    }
}
