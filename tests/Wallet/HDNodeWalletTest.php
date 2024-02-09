<?php

use Ephers\Wallet\HDNodeWallet;
use Ephers\Wallet\Mnemonic;
use PHPUnit\Framework\TestCase;

class HDNodeWalletTest extends TestCase
{
    public function testCanCreateRandomWallet(): void
    {
        // $m = Mnemonic::fromPhrase('garbage number drama office bring raw organ artefact myself verify monitor medal');
        // throw new Exception($m->computeSeed());
        //e86523a027bb1774fa53a9839d67fac4c910522cf0e934fa1f4f092a921b0b1628454b6aa59703d9d6258950ad1174589dfab0f29bb157be343602b22aa9105c
        //e86523a027bb1774fa53a9839d67fac4c910522cf0e934fa1f4f092a921b0b16

        $wallet = HDNodeWallet::fromPhrase('garbage number drama office bring raw organ artefact myself verify monitor medal');
        throw new Exception($wallet->mnemonic->phrase . '---' . $wallet->publicKey);
        
        // $this->assertInstanceOf(HDNodeWallet::class, $wallet);
    }
}