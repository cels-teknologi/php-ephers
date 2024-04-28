<?php

namespace Ephers\Ethereum\Abi\Coder;

use Ephers\Ethereum\Abi\ParamType;
use Ephers\Helpers\BinaryString;

class DynamicCoder extends Coder
{
    public function encode(Writer $writer, ParamType $type, $value): BinaryString
    {
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
