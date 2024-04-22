<?php

namespace Ephers\Ethereum\Abi\Encoder;

use Ephers\Ethereum\Abi\ParamType;
use Ephers\Helpers\BinaryString;

final class Encoder
{
    public final const WORD_SIZE = 32;

    public static function encode(ParamType $type, $value): BinaryString
    {
        return match ($type->baseType) {
            'address' => static::_encodeAddress($value),
            'tuple' => static::_encodeTuple($type, $value),
            default => throw new \InvalidArgumentException("Unsupported type {type->baseType}"),
        };
    }

    private static function _encodeTuple(ParamType $type, array $rawValues): BinaryString
    {
        if (\count($type->components) !== \count($rawValues)) {
            throw new \InvalidArgumentException('Invalid tuple');
        }

        $values = [...$rawValues];
        \array_walk($values, fn (&$value, $key) => (
            $value = static::encode($type->components[$key], $value)->raw()
        ));
        return BinaryString::of(\implode('', $values));
    }

    private static function _encodeAddress(string $rawValue): BinaryString
    {
        if (BinaryString::isHex($rawValue)) {
            // @todo make address class
            $value = $rawValue;
            if (!\str_starts_with($value, '0x')) {
                $value = "0x{$rawValue}";
            }

            // @todo: check for checksum
            return static::_pad(BinaryString::fromHex($value));
        }
    }

    private static function _pad(BinaryString $raw)
    {
        return BinaryString::of(\str_pad($raw->raw(), static::WORD_SIZE, "\0", STR_PAD_LEFT));
    }
}