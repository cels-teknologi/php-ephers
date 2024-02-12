<?php

namespace Ephers\Test;

use Ephers\Wallet\DerivationPath;
use Ephers\Wallet\HDWallet;
use Ephers\Wallet\Mnemonic;
use PHPUnit\Framework\TestCase;

class BIP44Test extends TestCase
{
    public function testCanCreateDifferentWalletCoins(): void
    {
        $mnemonic = Mnemonic::fromEntropy(
            \array_map(fn ($_) => \random_int(0, 255), \range(1, 16))
        );
        $btcWallet = HDWallet::fromPhrase(
            $mnemonic->phrase,
            path: DerivationPath::BITCOIN,
        );
        $ethWallet = HDWallet::fromPhrase(
            $mnemonic->phrase,
            path: DerivationPath::ETHEREUM,
        );
        
        $this->assertEquals(DerivationPath::BITCOIN, $btcWallet->path);
        $this->assertEquals(DerivationPath::ETHEREUM, $ethWallet->path);
        $this->assertEquals($mnemonic->phrase, $btcWallet->mnemonic->phrase);
        $this->assertEquals($mnemonic->phrase, $ethWallet->mnemonic->phrase);
        $this->assertNotEquals($btcWallet->publicKey, $ethWallet->publicKey);
    }
}
