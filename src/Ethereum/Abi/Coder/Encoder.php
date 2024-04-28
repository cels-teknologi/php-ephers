<?php

namespace Ephers\Ethereum\Abi\Coder;

use Ephers\Ethereum\Abi\ParamType;
use Ephers\Helpers\BinaryString;

final class Encoder
{
    public static function encode(Writer $writer, ParamType $type, $value) {
        if ($type->baseType === 'tuple') {
            return (new TupleCoder($type->name))->encode($writer, $type, $value);
        }

        return (match ($type->baseType) {
            'address', 'int', 'int256', 'uint', 'uint256' => new StaticCoder($type->name),
            default => throw new \InvalidArgumentException(
                "Type {$type->baseType} is invalid / not supported"
            ),
        })->encode($writer, $type, $value);
    }
}
