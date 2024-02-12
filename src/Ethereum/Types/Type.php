<?php

namespace Ephers\Ethereum\Types;

abstract readonly class Type
{
    public function __construct(protected int $size) { }

    abstract public function toHex(string $prefix = '0x'): string;

    abstract public static function from($data): self;
}
