<?php

namespace Ephers\Ethereum\Types;

use Ephers\Helpers\BinaryString;

final readonly class Int256 extends Type implements \Stringable
{
    public const SIZE = 32; // in bytes

    protected \GMP $min;
    protected \GMP $max;

    public function __construct(
        protected \GMP $value,
        protected bool $unsigned = false,
    ) {
        parent::__construct(self::SIZE);

        $bits = self::SIZE * 8;
        [$min, $max] = match ($unsigned) {
            true => [
                0,
                \gmp_sub(\gmp_pow(2, $bits), 1),
            ],
            false => [
                \gmp_neg(\gmp_pow(2, $bits - 1)),
                \gmp_sub(\gmp_pow(2, $bits - 1), 1),
            ],
        };

        if (\gmp_cmp($this->value, $min) < 0 || \gmp_cmp($this->value, $max) > 0) {
            // throw new \ArithmeticError('Value overflows');
        }

        $this->min = $min;
        $this->max = $max;
    }

    public function wrappedValue(): \GMP
    {
        /**
         * Discards upper bits.
         * 
         * @todo  Is there a bitwise way to do this..?
         */
        $gmp = BinaryString::fromGMP($this->value)->split(-self::SIZE)->toGmp();
        if (!$this->unsigned && \gmp_cmp($gmp, $this->max) > 0) {
            return \gmp_sub($gmp, \gmp_pow(2, self::SIZE * 8));
        }

        return $gmp;
    }

    public static function from($value): self
    {
        // TODO: 
        return new self(\gmp_init(0, 10));
    }

    public function toHex(string $prefix = '0x'): string
    {
        return $prefix . \str_pad(
            BinaryString::fromGMP($this->value)->split(-self::SIZE)->toHex(''),
            self::SIZE * 2,
            '0',
            STR_PAD_LEFT
        );
    }

    public function __toString(): string
    {
        return \gmp_strval($this->wrappedValue(), 10);
    }
}
