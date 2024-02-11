<?php

namespace Ephers\Encodings;

use Ephers\Helpers\BinaryString;

final class Bech32
{
    public const BASE = 32;
    public const CHARSET = 'qpzry9x8gf2tvdw0s3jn54khce6mua7l';
    public const CHARSET_MAP = ['0' => 15, '2' => 10, '3' => 17, '4' => 21, '5' => 20, '6' => 26, '7' => 30, '8' => 7, '9' => 5, 'q' => 0, 'p' => 1, 'z' => 2, 'r' => 3, 'y' => 4, 'x' => 6, 'g' => 8, 'f' => 9, 't' => 11, 'v' => 12, 'd' => 13, 'w' => 14, 's' => 16, 'j' => 18, 'n' => 19, 'k' => 22, 'h' => 23, 'c' => 24, 'e' => 25, 'm' => 27, 'u' => 28, 'a' => 29, 'l' => 31];
    public const GENERATOR = [0x3B6A57B2, 0x26508E6D, 0x1EA119FA, 0x3D4233DD, 0x2A1462B3];

    public static function encode(BinaryString $str): string
    {
        return '';
    }

    public static function decode(string $str): array
    {
        $hasLower = false;
        $hasUpper = false;
        $lastPosOfOne = -1;

        $chars = \str_split($str);
        \array_walk(
            $chars,
            function (&$ch, $i) use (&$hasLower, &$hasUpper, &$lastPosOfOne) {
                if (\ord($ch) < 33 || \ord($ch) > 126) {
                    throw new \InvalidArgumentException('Invalid character in bech32');
                }

                if ($ch >= 'a' && $ch <= 'z') {
                    $hasLower = true;
                }

                if ($ch >= 'A' && $ch <= 'Z') {
                    $hasUpper = true;
                    $ch = \chr(\ord($ch) + 32);
                }

                if ($ch === '1') {
                    $lastPosOfOne = $i;
                }
            },
        );

        if ($hasLower && $hasUpper) {
            throw new \InvalidArgumentException('Bech32 contains mixed-case characters');
        }
        if ($lastPosOfOne <= -1) {
            throw new \InvalidArgumentException('No separator character');
        }
        if ($lastPosOfOne < 1) {
            throw new \InvalidArgumentException('Empty HRP');
        }
        if ($lastPosOfOne + 7 > \strlen($str)) {
            throw new \InvalidArgumentException('Too short checksum');
        }

        $hrp = \implode('', \array_splice($chars, 0, $lastPosOfOne));
        \array_shift($chars);
        $data = \array_map(
            fn ($ch) => \array_key_exists($ch, self::CHARSET_MAP)
                ? self::CHARSET_MAP[$ch]
                : -1,
            $chars,
        );

        if (!self::verifyChecksum($hrp, $data)) {
          throw new \InvalidArgumentException('Invalid checksum');
        }
        return [$hrp, \array_slice($data, 0, -6)];
    }

    protected static function polymod(array $values)
    {
        $chk = 1;
        for ($i = 0; $i < \count($values); ++$i) {
            $top = $chk >> 25;
            $chk = ($chk & 0x01FFFFFF) << 5 ^ $values[$i];
            for ($j = 0; $j < 5; ++$j) {
                if (($top >> $j) & 1) {
                    $chk = $chk ^ self::GENERATOR[$j];
                }
            }
        }
        return $chk;
    }
      
    protected static function hrpExpand(string $hrp): array
    {
        $expand_1 = [];
        $expand_2 = [];

        for ($i = 0; $i < \strlen($hrp); $i++) {
            $ch = \ord($hrp[$i]);
            $expand_1[] = $ch >> 5;
            $expand_2[] = $ch & 31;
        }

        return [...$expand_1, 0, ...$expand_2];
    }
      
    protected static function verifyChecksum(string $hrp, array $data): bool
    {
        return self::polymod([
            ...self::hrpExpand($hrp),
            ...$data,
        ]) === 1;
    }
}
