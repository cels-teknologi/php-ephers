<?php

namespace Ephers\Ethereum\Abi\Coder;

use Ephers\Ethereum\Abi\ParamType;
use Ephers\Helpers\BinaryString;

class TupleCoder extends Coder
{
    public function encode(Writer $writer, ParamType $type, $value)
    {
        if (! \is_array($value)
            || ! \is_array($type->components)
            || \count($type->components) !== \count($value)
        ) {
            throw new \InvalidArgumentException('Invalid tuple');
        }

        $values = [...$value];
        \array_walk($values, fn ($value, $key) => (
            Encoder::encode($writer, $type->components[$key], $value)
        ));
    }

    public function decode(Reader $reader)
    {

    }

    // {
    //     match ($type->baseType) {
    //         'address' => Address::validate($value),
    //         'tuple' => static::_validateTuple($type, $value),
    //         default => true,
    //     };


    // }

    // private static function _validateTuple(ParamType $type, array $rawValues): BinaryString
    // {
    // }
}
