<?php

namespace Ephers\Ethereum\Abi\Coder;

use Ephers\Ethereum\Abi\ParamType;
use Ephers\Ethereum\Address\Address;
use Ephers\Helpers\BinaryString;

abstract class Coder
{
    public function __construct(public string $name)
    {
        //
    }

    abstract public function encode(Writer $writer, ParamType $type, $value);
    abstract public function decode(Reader $reader);

    // {
    //     match ($type->baseType) {
    //         'address' => Address::validate($value),
    //         'tuple' => static::_validateTuple($type, $value),
    //         default => true,
    //     };


    // }

    // private static function _validateTuple(ParamType $type, array $rawValues): BinaryString
    // {
    //     if (! \is_array($rawValues)
    //         || ! \is_array($type->components)
    //         || \count($type->components) !== \count($rawValues)
    //     ) {
    //         throw new \InvalidArgumentException('Invalid tuple');
    //     }

    //     $values = [...$rawValues];
    //     \array_walk($values, fn (&$value, $key) => (
    //         $value = static::encode($type->components[$key], $value)->raw()
    //     ));
    //     return BinaryString::of(\implode('', $values));
    // }
}
