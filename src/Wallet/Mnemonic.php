<?php
/**
 * Mnemonic.php
 * (c) PT Cels Teknologi Indonesia, 2024.
 * 
 * This class implements mnemonics according to BIP-39.
 */

namespace Ephers\Wallet;

use Ephers\Helpers\BinaryString;
use Ephers\Wordlists\English;
use Ephers\Wordlists\Wordlist;
use JsonSerializable;
use Normalizer;

// Returns a byte with the MSB bits set
function getUpperMask(int $bits): int {
    return ((1 << $bits) - 1) << (8 - $bits) & 0xff;
}
 
// Returns a byte with the LSB bits set
function getLowerMask(int $bits): int {
    return ((1 << $bits) - 1) & 0xff;
}
 
class Mnemonic implements JsonSerializable
{
    private function __construct(
        public readonly BinaryString $entropy,
        public readonly string $phrase,
        public readonly string $password = '',
        public readonly Wordlist $wordlist = new English,
    ) { }

    public function computeSeed(): BinaryString
    {
        return BinaryString::of(\hash_pbkdf2(
            'sha512',
            Normalizer::normalize($this->phrase, Normalizer::FORM_KD),
            "mnemonic{$this->password}",
            2048,
            64,
            true,
        ));
    }

    public static function fromEntropy(
        array $entropy,
        string $password = '',
        Wordlist $wordlist = new English,
    ): Mnemonic {
        $mnemonic = self::toMnemonic(BinaryString::fromBytes($entropy), $wordlist);
        $entropy = self::toEntropy($mnemonic, $wordlist);
        return new self($entropy, $mnemonic, $password, $wordlist);
    }

    public static function fromPhrase(
        string $phrase,
        string $password = '',
        Wordlist $wordlist = new English,
    ): Mnemonic {
        $entropy = self::toEntropy($phrase, $wordlist);
        $mnemonic = self::toMnemonic($entropy, $wordlist);
        return new self($entropy, $mnemonic, $password, $wordlist);
    }
    
    private static function toEntropy(
        string $mnemonic,
        ?Wordlist $wl = null,
    ): BinaryString {
        $wordlist = $wl ?? new English;
        $words = $wordlist->split($mnemonic);

        if (\count($words) % 3 > 0 || \count($words) < 12 || \count($words) > 24) {
            throw new \InvalidArgumentException('Invalid mnemonic length');
        }

        $entropy = \array_map(
            fn ($_) => 0,
            range(1, \ceil(11 * \count($words) / 8)),
        );
        $offset = 0;
        for ($i = 0; $i < \count($words); $i++) {
            $index = $wordlist->getWordIndex(Normalizer::normalize(
                $words[$i],
                Normalizer::FORM_KD,
            ));

            if (!$index) {
                throw new \InvalidArgumentException("Invalid mnemonic word at index {$i}");
            }

            for ($bit = 0; $bit < 11; $bit++) {
                if ($index & (1 << (10 - $bit))) {
                    $j = $offset >> 3;
                    $entropy[$j] = (\array_key_exists($j, $entropy)
                        ? $entropy[$j]
                        : 0
                    ) | (1 << (7 - ($offset % 8)));
                }
                $offset++;
            }
        }
        $entropyBits = 32 * \count($words) / 3;
        $checksumBits = \count($words) / 3;
        $checksumMask = getUpperMask($checksumBits);

        $hash = \hash('sha256', pack('C*', ...\array_slice(
            $entropy,
            0,
            $entropyBits / 8,
        )), true);
        $checksum = \array_values(\unpack('C*', $hash))[0] & $checksumMask;

        if ($checksum !== $entropy[\count($entropy) - 1]) {
            throw new \InvalidArgumentException('Invalid mnemonic checksum');
        }

        return BinaryString::fromBytes(\array_slice($entropy, 0, $entropyBits / 8));
    }

    private static function toMnemonic(
        BinaryString $bEntropy,
        ?Wordlist $wl = null,
    ): string {
        $entropy = $bEntropy->toBytes();

        if (\count($entropy) % 4 > 0
            || \count($entropy) < 16
            || \count($entropy) > 32
        ) {
            throw new \InvalidArgumentException('Invalid entropy size');
        }

        $wordlist = $wl ?? new English;
        $indices = [0];
        $remainingBits = 11;
        for ($i = 0; $i < \count($entropy); $i++) {
            $min = \min($remainingBits, 8);
            $indices[\count($indices) - 1] <<= $min;
            $indices[\count($indices) - 1] |= $entropy[$i] >> (8 - $min);

            if ($remainingBits > 8) {
                $remainingBits -= 8;
            }
            else {
                $indices[] = $entropy[$i] & getLowerMask(8 - $min);
                $remainingBits += 3;
            }

        }

        $checksumBits = \count($entropy) / 4;
        $hash = \hash('sha256', pack('C*', ...$entropy));
        $checksum = ((int) \base_convert(\substr($hash, 0, 2), 16, 10))
            & getUpperMask($checksumBits);

        $indices[\count($indices) - 1] <<= $checksumBits;
        $indices[\count($indices) - 1] |= (
            $checksum >> (8 - $checksumBits)
        );

        return $wordlist->join(\array_map(
            fn ($i) => $wordlist->get($i),
            $indices,
        ));
    }

    public function jsonSerialize(): mixed
    {
        // @TODO: Add this method
        return [];
    }
}
