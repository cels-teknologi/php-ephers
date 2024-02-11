<?php

namespace Ephers\Encodings;

use Ephers\Helpers\BinaryString;

final class Base58
{
    public const BASE = 58;
    public const CHARSET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    public const CHARSET_MAP = ['1' => 0, '2' => 1, '3' => 2, '4' => 3, '5' => 4, '6' => 5, '7' => 6, '8' => 7, '9' => 8, 'A' => 9, 'B' => 10, 'C' => 11, 'D' => 12, 'E' => 13, 'F' => 14, 'G' => 15, 'H' => 16, 'J' => 17, 'K' => 18, 'L' => 19, 'M' => 20, 'N' => 21, 'P' => 22, 'Q' => 23, 'R' => 24, 'S' => 25, 'T' => 26, 'U' => 27, 'V' => 28, 'W' => 29, 'X' => 30, 'Y' => 31, 'Z' => 32, 'a' => 33, 'b' => 34, 'c' => 35, 'd' => 36, 'e' => 37, 'f' => 38, 'g' => 39, 'h' => 40, 'i' => 41, 'j' => 42, 'k' => 43, 'm' => 44, 'n' => 45, 'o' => 46, 'p' => 47, 'q' => 48, 'r' => 49, 's' => 50, 't' => 51, 'u' => 52, 'v' => 53, 'w' => 54, 'x' => 55, 'y' => 56, 'z' => 57];

    public static function encode(BinaryString $str): string
    {
        if ($str->len() <= 0) {
            return '';
        }

        $buffer = $str->toBytes();
        $digits = [0];

        for ($i = 0; $i < \count($buffer); $i++) {
            $digits = \array_map(fn ($_) => $_ << 8, $digits);
            $digits[0] += $buffer[$i];
            $carry = 0;
    
            for ($j = 0; $j < \count($digits); ++$j) {
                $digits[$j] += $carry;
                $carry = (int) (\floor($digits[$j] / self::BASE));
                $digits[$j] = $digits[$j] % self::BASE;
            }
    
            while ($carry) {
                $digits[] = $carry % self::BASE;
                $carry = (int) (\floor($carry / self::BASE));
            }
        }
    
        for ($i = 0; $buffer[$i] === 0 && $i < \count($buffer) - 1; $i++) {
            $digits[] = 0;
        }

        return \implode('', \array_map(
            fn ($d) => self::CHARSET[$d],
            \array_reverse(\array_values($digits)),
        ));
    }

    public static function decode(string $str): BinaryString
    {
        if (\strlen($str) <= 0) {
            return BinaryString::of("");
        }

        $bytes = [0];

        foreach (\str_split($str) as $c) {
            if (!\array_key_exists($c, self::CHARSET_MAP)) {
                throw new \InvalidArgumentException('Non-base58 character');
            }

            $bytes = \array_map(fn ($b) => $b * self::BASE, $bytes);

            $bytes[0] += self::CHARSET_MAP[$c];
            $carry = 0;

            for ($j = 0; $j < \count($bytes); ++$j) {
                $bytes[$j] += $carry;
                $carry = $bytes[$j] >> 8;
                $bytes[$j] = $bytes[$j] & 0xFF;
            }

            while ($carry) {
                $bytes[] = $carry & 0xFF;
                $carry = $carry >> 8;
            }
        }
    
        for ($i = 0; $str[$i] === '1' && $i < \strlen($str) - 1; $i++) {
            $bytes[] = 0;
        }

        return BinaryString::fromBytes(\array_reverse($bytes));
    }
}
