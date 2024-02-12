<?php
/**
 * BinaryString.php
 * 
 * @copyright  PT Cels Teknologi Indonesia, 2024
 * @author  Stephen Dewanto
 */

namespace Ephers\Helpers;

/**
 * An implementation of `Uint8Array` as PHP `string`s.
 * 
 * This class is more or less the equivalent of
 * `Uint8Array` in Javascript, just that it is stored as
 * `string` and treated as `unsigned char`s.
 */
final readonly class BinaryString implements \JsonSerializable, \Stringable
{
    /**
     * The constructor
     * 
     * This constructor **MUST NOT** be called from external scripts.
     * To create a `BinaryString`, use the static function
     * `fromBytes`, `fromHex`, or `of`
     * 
     * @param  string  $str  `Uint8Array` representation as PHP binary string.
     */
    protected function __construct(protected string $str) { }

    /**
     * Gets the byte-representation at specified `$position`.
     * 
     * @param  int  $pos
     * @return  int
     * @throws  \InvalidArgumentException  When given `$pos` is invalid.
     */
    public function at(int $pos): int
    {
        if ($pos < 0 || $pos >= $this->len()) {
            throw new \InvalidArgumentException('Invalid position');
        }

        return \ord($this->str[$pos]);
    }

    /**
     * Compares the hex-representation of `BinaryString $this` and `$other`.
     * 
     * Under the hood, this calls PHP native `strcmp`. This function returns
     * -1 if `$this` < `$other`, 1 if `$this` > `$other`, 0 if both are equal.
     * 
     * @param  self  $other
     * @return  int
     */
    public function compare(BinaryString $other): int
    {
        $res = \strcmp($this->toHex(), $other->toHex());

        /**
         * Prior to PHP 8.2, `strcmp()` returns unbounded numbers.
         * 
         * So we clamp it within [-1, 1] by dividing the number with
         * absolute of itself **IF AND ONLY IF** it is not zero.
         */
        return $res === 0 ? 0 : \floor($res / \abs($res));
    }

    /**
     * Appends `$other` to the end of `BinaryString $this`.
     * 
     * @param  self  $other
     * @return  self
     */
    public function concat(BinaryString $other): self
    {
        return new self($this->str . $other->raw());
    }

    /**
     * Gets the length of string (aka. the length of `Uint8Array`).
     * 
     * This method is an alias of `len`.
     * 
     * @deprecated  Use `len()` instead.
     * @return  int
     */
    public function count(): int
    {
        return $this->len();
    }

    /**
     * Gets the length of string (aka. the length of `Uint8Array`).
     * 
     * @return  int
     */
    public function len(): int
    {
        return \strlen($this->str);
    }

    /**
     * Gets the raw binary `string`.
     * 
     * @return  string
     */
    public function raw(): string
    {
        return $this->str;
    }

    /**
     * Split string (eq. to `Uint8Array.prototype.splice`).
     * 
     * Under the hood, this calls PHP native `substr` to
     * get a substring of the internal string representation.
     * This method is an alias of `split`.
     * 
     * @deprecated  Use `split($offset, $length)` instead.
     * @param  int  $offset
     * @param  ?int  $length
     * @return  self
     */
    public function splice(int $offset, ?int $length = null): self
    {
        return new self(\substr($this->str, $offset, $length));
    }

    /**
     * Split string (eq. to `Uint8Array.prototype.splice`).
     * 
     * Under the hood, this calls PHP native `substr` to
     * get a substring of the internal string representation.
     * 
     * @param  int  $offset
     * @param  ?int  $length
     * @return  self
     */
    public function split(int $offset, ?int $length = null): self
    {
        return new self(\substr($this->str, $offset, $length));
    }

    /**
     * Gets the length of string (aka. the length of `Uint8Array`).
     * 
     * This method is an alias of `len`.
     * 
     * @deprecated  Use `len()` instead.
     * @return  int
     */
    public function size(): int
    {
        return $this->len();
    }

    /**
     * Representation in `int[]` bytes.
     * 
     * @return  int[]
     */
    public function toBytes(): array
    {
        return \array_map(
            fn ($h) => \ord($h),
            \str_split($this->str),
        );
    }

    /**
     * Representation in `\GMP`
     * 
     * @return  \GMP
     */
    public function toGmp(): \GMP
    {
        return \gmp_init(\bin2hex($this->str), 16);
    }

    /**
     * Representation in lowercase hexadecimal format `0x...`.
     * 
     * @return  string
     */
    public function toHex(): string
    {
        return '0x' . \bin2hex($this->str);
    }

    /**
     * Creates a new `BinaryString` from `array` of bytes.
     * 
     * @static
     * @param  int[]  $bytes  Array of bytes.
     * @return  self
     * @throws  \InvalidArgumentException  If `$bytes` argument contains integer
     *                                     with value not between 0x0 and 0xFF.
     */
    public static function fromBytes(array $bytes): self
    {
        $values = \array_values($bytes);
        if (\count(\array_filter($values, fn ($_) => !self::validByte($_))) > 0) {
            throw new \InvalidArgumentException(
                'Argument `$bytes` contains invalid byte.'
            );
        }

        return new self(\pack('C*', ...$values));
    }

    /**
     * Creates a new `BinaryString` from `\GMP` (big integer) value.
     * 
     * @static
     * @param  \GMP  $val
     * @return  self
     */
    public static function fromGMP(\GMP $val): self
    {
        return self::fromHex(\gmp_strval($val, 16));
    }

    /**
     * Creates a new `BinaryString` from `string` of hex.
     * 
     * @static
     * @param  string  $str  The binary string.
     * @return  self
     * @throws  \InvalidArgumentException  If `$hex` contains an invalid hex byte.
     */
    public static function fromHex(string $hex): self
    {
        $hex = \preg_replace(
            pattern: '/^0x/',
            replacement: '',
            subject: $hex,
            limit: 1,
        );
        if (\strlen($hex) % 2 === 1) {
            $hex = "0{$hex}";
        }
        $bin = \hex2bin($hex);
        if (!$bin) {
            throw new \InvalidArgumentException(
                'Argument `$hex` contains invalid hexadecimal byte: ' . $hex
            );
        }

        return new self($bin);
    }

    /**
     * Checks whether a given string is hexits.
     * 
     * @static
     * @param  string  $str
     * @return  bool
     */
    public static function isHex(string $str, bool|int $bytesLen = false): bool
    {
        $isHex = $str && (bool) \preg_match(
            pattern: '/^(0x)?[0-9A-Fa-f]*$/',
            subject: $str,
        );

        if (\is_bool($bytesLen) && !$bytesLen) {
            return $isHex;
        }

        return $isHex && match(\is_bool($bytesLen)) {
            true => \strlen($str) % 2 === 0,
            false => \strlen($str) / 2 === $bytesLen + (
                \str_starts_with($str, '0x')
                    ? 1
                    : 0
            ),
        };
    }

    /**
     * Creates a new `BinaryString` from native PHP binary `string`.
     * 
     * @static
     * @param  string  $str  The binary string.
     * @return  self
     */
    public static function of(string $str): self
    {
        return new self($str);
    }

    /**
     * Checks whether a given `int` is a valid byte.
     * 
     * @internal
     * @static
     * @param  int  $x
     * @return  bool
     */
    protected static function validByte(int $x): bool
    {
        return $x >= 0 && $x <= 0xFF;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): string
    {
        return $this->toHex();
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->toHex();
    }
}
