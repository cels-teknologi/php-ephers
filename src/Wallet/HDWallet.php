<?php
/**
 * HDWallet.php
 * 
 * This file implements Hierarchical Deterministic (HD) Wallet class
 * in accordance to BIP-44 specification.
 * 
 * @see  https://github.com/bitcoin/bips/blob/master/bip-0044.mediawiki
 * @copyright  PT Cels Teknologi Indonesia
 * @author  Stephen Dewanto
 */

namespace Ephers\Wallet;

use Ephers\Helpers\BinaryString;
use Ephers\Wordlists\English;
use Ephers\Wordlists\Wordlist;

/**
 * @author  Richard Moore from ethers
 * @return  BinaryString[]
 */
function ser_I(
    int $index,
    BinaryString $chainCode,
    BinaryString $publicKey,
    ?BinaryString $privateKey = null
): array {
    $data = [];

    if ($index & HDWallet::HARDENED_BIT) {
        if (!$privateKey) {
            throw new \InvalidArgumentException(
                'Cannot derive child of neutered node'
        );
        }

        // Data = 0x00 || ser_256(k_par)
        $data = [0, ...$privateKey->toBytes()];
    } else {
        $data = [...$publicKey->toBytes()];
    }

    if (\count($data) < 37) {
        $data = [...$data, ...\array_map(
            fn ($_) => 0,
            range(1, 37 - \count($data)),
        )];
    }

    for ($i = 24; $i >= 0; $i -= 8) {
        $data[33 + ($i >> 3)] = (($index >> (24 - $i)) & 0xff);
    }

    $I = BinaryString::of(
        \hash_hmac('sha512', \pack('C*', ...$data), $chainCode->raw(), true),
    );

    return [
        'IL' => $I->split(0, 32),
        'IR' => $I->split(32),
    ];
}

/**
 * Hierarchical Deterministic Wallet class implementation for Ethereum.
 * 
 */
class HDWallet extends Wallet
{
    public const DEFAULT_DERIVATION_PATH = "m/44'/60'/0'/0/0";
    protected const MASTER_SECRET = 'Bitcoin seed';
    public const HARDENED_BIT = 0x80000000;
    public const SECP256K1_N = '0xfffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';

    protected BinaryString $privateKey;
    public readonly BinaryString $publicKey;
    public BinaryString $fingerprint;

    private function __construct(
        public SigningKey $signingKey,
        public BinaryString $parentFingerprint,
        public BinaryString $chainCode,
        public string $path,
        public int $index,
        public int $depth,
        public Mnemonic $mnemonic,
    ) {
        $this->privateKey = $signingKey->privateKey();
        $this->publicKey = $signingKey->compressedPublicKey();
        $this->fingerprint = BinaryString::of(
            \hash(
                'ripemd160',
                \hash('sha256', $this->publicKey->raw(), true),
                true,
            ),
        )->split(0, 4);
    }

    /**
     *  Creates a new random HDNode.
     */
    public static function createRandom(
        string $password = '',
        string $path = self::DEFAULT_DERIVATION_PATH,
        Wordlist $wordlist = new English,
    ): self {
        $mnemonic = Mnemonic::fromEntropy(
            \array_map(fn ($_) => \random_int(0, 255), \range(1, 16)),
            $password,
            $wordlist,
        );

        return self::fromSeed(
            $mnemonic->computeSeed(),
            $mnemonic,
        )->derivePath($path);
    }

    public static function fromPhrase(
        string $phrase,
        string $password = '',
        string $path = self::DEFAULT_DERIVATION_PATH,
        Wordlist $wordlist = new English
    ): self {
        $mnemonic = Mnemonic::fromPhrase($phrase, $password, $wordlist);
        
        return self::fromSeed(
            $mnemonic->computeSeed(),
            $mnemonic,
        )->derivePath($path);
    }

    public static function fromSeed(BinaryString $seed, Mnemonic $mnemonic): self
    {
        $I = BinaryString::of(
            \hash_hmac('sha512', $seed->raw(), 'Bitcoin seed', true)
        );

        $signingKey = new SigningKey($I->split(0, 32));
        $chainCode = $I->split(32);
        return new self(
            $signingKey,
            BinaryString::of("\0"),
            $chainCode,
            'm',
            0,
            0,
            $mnemonic,
        );
    }

    public function deriveChild(int $index): self
    {
        if ($index > 0xFFFFFFFF) {
            throw new \InvalidArgumentException('Invalid index');
        }

        $path = $this->path;
        if ($path) {
            $path .= '/' . ($index & ~self::HARDENED_BIT);
            if ($index & self::HARDENED_BIT) {
                $path .= '\'';
            }
        }

        ['IL' => $IL, 'IR' => $IR] = ser_I(
            $index,
            $this->chainCode,
            $this->publicKey,
            $this->privateKey,
        );

        $ki = new SigningKey(BinaryString::fromGMP(
            \gmp_mod(
                \gmp_add($IL->toGmp(), $this->privateKey->toGmp()),
                \gmp_init(self::SECP256K1_N, 16),
            ),
        ));

        return new self($ki, $this->fingerprint, $IR, $path, $index, $this->depth + 1, $this->mnemonic);
    }

    public function derivePath(string $path): self
    {
        $components = \explode('/', $path);
        if (\count($components) <= 0 || ($components[0] !== 'm' && $this->depth <= 0)) {
            throw new \InvalidArgumentException('Invalid path');
        }
        if ($components[0] === 'm') {
            \array_shift($components);
        }

        $result = $this;
        foreach ($components as $i => $component) {
            if (\preg_match(
                pattern: '/^[0-9]+\'$/',
                subject: $component,
            )) {
                $index = (int) \substr($component, 0, \strlen($component) - 1);
                if ($index >= self::HARDENED_BIT) {
                    throw new \InvalidArgumentException('Invalid path index');
                }

                $result = $result->deriveChild(
                    self::HARDENED_BIT + $index,
                );
            }
            else if (\preg_match(
                pattern: '/^[0-9]+$/',
                subject: $component,
            )) {
                $index = (int) $component;
                if ($index >= self::HARDENED_BIT) {
                    throw new \InvalidArgumentException('Invalid path index');
                }
                $result = $result->deriveChild($index);
            }
            else {
                throw new \InvalidArgumentException('Invalid path component');
            }
        }
        return $result;
    }

    /**
     * @todo
     */
    public function jsonSerialize(): mixed
    {
        return [
            'publicKey' => $this->publicKey,
        ];
    }
}
