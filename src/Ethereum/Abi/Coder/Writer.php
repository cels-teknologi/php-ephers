<?php

namespace Ephers\Ethereum\Abi\Coder;

use Ephers\Ethereum\Abi\ParamType;
use Ephers\Ethereum\Constants;
use Ephers\Helpers\BinaryString;

final class Writer
{
    public array $data = [];

    public function write(BinaryString $bytes)
    {
        $this->data[] = BinaryString::of(\str_pad(
            $bytes->raw(),
            Constants::WORD_SIZE,
            "\0",
            STR_PAD_LEFT,
        ));

        return $this;
    }

    public function append(Writer $other)
    {
        $this->data = [
            ...$this->data,
            ...$other->data,
        ];

        return $this;
    }

    public function data(): BinaryString
    {
        return BinaryString::of(\implode(
            \array_map(fn ($_) => $_->raw(), $this->data)
        ));
    }

    public function len()
    {
        return \count($this->data);
    }
}
