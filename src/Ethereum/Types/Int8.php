<?php

namespace Ephers\Ethereum\Types;

use Ephers\Helpers\BinaryString;

final readonly class Int8 extends Type implements \Stringable
{
    public const SIZE = 1; // in bytes

    protected int $min;
    protected int $max;

    public function __construct(
        protected int $value,
        protected bool $unsigned = false,
    ) {
        parent::__construct(self::SIZE);

        $bits = self::SIZE * 8;
        [$min, $max] = match ($unsigned) {
            true => [
                0,
                (2 ** $bits) - 1,
            ],
            false => [
                -(2 ** ($bits - 1)),
                (2 ** ($bits - 1)) - 1,
            ],
        };

        if ($this->value < $min || $this->value > $max) {
            // throw new \ArithmeticError('Value overflows');
        }

        $this->min = $min;
        $this->max = $max;
    }

    public function wrappedValue(): int
    {
        /**
         * Discards upper bits.
         * 
         * @todo  Is there a bitwise way to do this..?
         */
        $val = \ord(pack('C', $this->value));
        if (!$this->unsigned && $val > $this->max) {
            return $val - 0x100;
        }

        return $val;
    }

    public static function from($value): self
    {
        // TODO: 
        return new self((int) $value);
    }

    public function toHex(string $prefix = '0x'): string
    {
        return $prefix . \bin2hex(pack('C', $this->value));
    }

    public function __toString(): string
    {
        return $this->wrappedValue();
    }
}
