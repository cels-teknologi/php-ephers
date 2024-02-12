<?php

namespace Ephers\Ethereum\Types;

use Ephers\Helpers\BinaryString;
use kornrunner\Keccak;

final readonly class Address extends Type implements \Stringable
{
    public const SIZE = 20; // in bytes

    public function __construct(protected BinaryString $address)
    {
        parent::__construct(self::SIZE);
    }

    public function checksummed(string $prefix = '0x'): string
    {
        $bytes = \array_map(
            fn ($ch) => \ord($ch),
            \array_values(\str_split(\strtolower($this->address->toHex('')))),
        );
        $hashed = BinaryString::of(Keccak::hash(
            BinaryString::fromBytes($bytes)->raw(),
            256,
            true,
        ))->toBytes();

        for ($i = 0; $i < 40; $i += 2) {
            if (($hashed[$i >> 1] >> 4) >=8) {
                $bytes[$i] -= (
                    $bytes[$i] >= 97 && $bytes[$i] <= 122
                        ? 32
                        : 0
                ); // to uppercase byte
            }
            if (($hashed[$i >> 1] & 0x0F) >= 8) {
                $bytes[$i + 1] -= (
                    $bytes[$i + 1] >= 97 && $bytes[$i + 1] <= 122
                        ? 32
                        : 0
                );
            }
        }

        return $prefix . \implode('', \array_map(
            fn ($b) => \chr($b),
            $bytes,
        ));
    }

    public function toHex(string $prefix = '0x'): string
    {
        return $prefix . \str_pad(
            $this->checksummed(''),
            self::SIZE * 2,
            '0',
            STR_PAD_LEFT
        );
    }

    public static function from($address): self
    {
        return new self(BinaryString::fromHex($address));
    }

    public function __toString(): string
    {
        return $this->toHex();
    }
}
