<?php

namespace Ephers\Ethereum\Abi\Coder;

use Ephers\Ethereum\Abi\ParamType;
use Ephers\Helpers\BinaryString;

class StaticCoder extends Coder
{
    public function encode(Writer $writer, ParamType $type, $value)
    {
        if ($value instanceof BinaryString) {
            $writer->write($value);
            return;
        }

        if (\is_int($value)) {
            $writer->write(BinaryString::fromHex('0x0'));
            return;
        }

        $writer->write(match ($type->baseType) {
            'address' => BinaryString::fromHex($value),
        });
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
