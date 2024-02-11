<?php

use Ephers\Encodings\Base58;
use Ephers\Helpers\BinaryString;
use PHPUnit\Framework\TestCase;

class Base58Test extends TestCase
{
    public function testCanDecode(): void
    {
        $res = Base58::decode('2NEpo7TZRRrLZSi2U');
        $this->assertEquals('Hello World!', $res->raw());

        $res = Base58::decode('USm3fpXnKG5EUBx2ndxBDMPVciP5hGey2Jh4NDv6gmeo1LkMeiKrLJUUBk6Z');
        $this->assertEquals('The quick brown fox jumps over the lazy dog.', $res->raw());

        $res = Base58::decode('11233QC4');
        $this->assertEquals('0x0000287fb4cd', $res->toHex());
    }

    public function testCanEncode(): void
    {
        $res = Base58::encode(BinaryString::of('Hello World!'));
        $this->assertEquals('2NEpo7TZRRrLZSi2U', $res);

        $res = Base58::encode(BinaryString::of('The quick brown fox jumps over the lazy dog.'));
        $this->assertEquals('USm3fpXnKG5EUBx2ndxBDMPVciP5hGey2Jh4NDv6gmeo1LkMeiKrLJUUBk6Z', $res);

        $res = Base58::encode(BinaryString::fromHex('0x0000287fb4cd'));
        $this->assertEquals('11233QC4', $res);
    }
}
