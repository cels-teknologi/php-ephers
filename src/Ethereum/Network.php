<?php

namespace Ephers\Ethereum;

class Network
{
    public function __construct(
        public string $name,
        public \GMP $chainId,
    ) {}

    public function matches($other): bool
    {
        if (!$other) {
            return false;
        }

        if ($other instanceof Network) {
            return \gmp_cmp($this->chainId, $other->chainId) === 0;
        }

        if ($other instanceof \GMP) {
            return \gmp_cmp($this->chainId, $other) === 0;
        }

        if (\is_string($other)) {
            try {
                return \gmp_cmp(
                    $this->chainId,
                    \gmp_init($other, 10),
                ) === 0;
            }
            catch (\Throwable $t) { }

            return $this->name === $other;
        }

        return false;
    }

    public static function from($network): self
    {
        if (!$network) {
            return self::from('mainnet');
        }

        if ($network instanceof \GMP) {
            $network = (int) \gmp_strval($network, 10);
        }

        return new self(...(match ($network) {
            'mainnet', 1 => ['mainnet', \gmp_init(1, 10)],
            'ropsten', 3 => ['ropsten', \gmp_init(3, 10)],
            'rinkeby', 4 => ['rinkeby', \gmp_init(4, 10)],
            'goerli', 5 => ['goerli', \gmp_init(5, 10)],
            'kovan', 42 => ['kovan', \gmp_init(42, 10)],
            'holesky', 17000 => ['holesky', \gmp_init(17000, 10)],
            'sepolia', 11155111 => ['sepolia', \gmp_init(11155111, 10)],

            'arbitrum', 42161 => ['arbitrum', \gmp_init(42161, 10)],
            'arbitrum-goerli', 421613 => ['arbitrum-goerli', \gmp_init(421613, 10)],
            'arbitrum-sepolia', 421614 => ['arbitrum-sepolia', \gmp_init(421614, 10)],

            'base', 8453 => ['base', \gmp_init(8453, 10)],
            'base-goerli', 84531 => ['base-goerli', \gmp_init(84531, 10)],
            'base-sepolia', 84532 => ['base-sepolia', \gmp_init(84532, 10)],

            'bnb', 56 => ['bnb', \gmp_init(42161, 10)],
            'bnbt', 97 => ['arbitrum', \gmp_init(42161, 10)],

            default => ['unknown', \gmp_init(0, 10)],
        }));
    }
}