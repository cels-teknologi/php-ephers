<?php

use Ephers\Encodings\Bech32;
use PHPUnit\Framework\TestCase;

class Bech32Test extends TestCase
{
    public function testPassBIP173TestVectors(): void
    {
        // Valid Bech32
        [$hrp, $data] = Bech32::decode('A12UEL5L');
        $this->assertEquals('a', $hrp);
        $this->assertCount(0, $data);

        [$hrp, $data] = Bech32::decode('a12uel5l');
        $this->assertEquals('a', $hrp);
        $this->assertCount(0, $data);

        [$hrp, $data] = Bech32::decode('an83characterlonghumanreadablepartthatcontainsthenumber1andtheexcludedcharactersbio1tt5tgs');
        $this->assertEquals('an83characterlonghumanreadablepartthatcontainsthenumber1andtheexcludedcharactersbio', $hrp);
        $this->assertCount(0, $data);

        [$hrp, $data] = Bech32::decode('abcdef1qpzry9x8gf2tvdw0s3jn54khce6mua7lmqqqxw');
        $this->assertEquals('abcdef', $hrp);
        $this->assertEquals(range(0, 31), $data);

        [$hrp, $data] = Bech32::decode('11qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqc8247j');
        $this->assertEquals('1', $hrp);
        $this->assertEquals(\array_map(fn ($_) => 0, range(1, 82)), $data);

        [$hrp, $data] = Bech32::decode('split1checkupstagehandshakeupstreamerranterredcaperred2y9e3w');
        $this->assertEquals('split', $hrp);
        $this->assertEquals([24, 23, 25, 24, 22, 28, 1, 16, 11, 29, 8, 25, 23, 29, 19, 13, 16, 23, 29, 22, 25, 28, 1, 16, 11, 3, 25, 29, 27, 25, 3, 3, 29, 19, 11, 25, 3, 3, 25, 13, 24, 29, 1, 25, 3, 3, 25, 13], $data);

        [$hrp, $data] = Bech32::decode('?1ezyfcl');
        $this->assertEquals('?', $hrp);
        $this->assertCount(0, $data);

        
    }
}
