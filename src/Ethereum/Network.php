<?php

namespace Ephers\Ethereum;

enum Network: int
{
    case Unknown = 0;
    case Mainnet = 1;
    case Ropsten = 3;
    case Rinkeby = 4;
    case Goerli = 5;
    case Kovan = 42;
    case Holesky = 17000;
    case Sepolia = 11155111;
    case Arbitrum = 42161;
    case ArbitrumGoerli = 421613;
    case ArbitrumSepolia = 421614;
    case Base = 8453;
    case BaseGoerli = 84531;
    case BaseSepolia = 84532;
    case BNB = 56;
    case BNBt = 97;

    public function matches($other): bool
    {
        if (!$other) {
            return false;
        }

        if ($other instanceof Network) {
            return $other === $this; //\gmp_cmp($this->chainId, $other->chainId) === 0;
        }

        if ($other instanceof \GMP) {
            return \gmp_cmp($this->asGmp(), $other) === 0;
        }

        if (\is_int($other)) {
            return $this->value === $other;
        }

        if (\is_string($other)) {
            try {
                return $this->matches(\gmp_init($other, 10));
            }
            catch (\Throwable $t) { }

            return $this->name === $other;
        }

        return false;
    }

    public function asGmp(): \GMP
    {
        return \gmp_init($this->value, 10);
    }

    public static function guess($network): self
    {
        if (!$network) {
            return Network::Mainnet;
        }

        if ($network instanceof \GMP) {
            $network = (int) \gmp_strval($network, 10);
        }
        
        if (\is_string($network)) {
            $network = \mb_strtolower($network);
        }

        return match ($network) {
            'mainnet', 1 => self::Mainnet,
            'ropsten', 3 => self::Ropsten,
            'rinkeby', 4 => self::Rinkeby,
            'goerli', 5 => self::Goerli,
            'kovan', 42 => self::Kovan,
            'holesky', 17000 => self::Holesky,
            'sepolia', 11155111 => self::Sepolia,
            'arbitrum', 42161 => self::Arbitrum,
            'arbitrum-goerli', 421613 => self::ArbitrumGoerli,
            'arbitrum-sepolia', 421614 => self::ArbitrumSepolia,
            'base', 8453 => self::Base,
            'base-goerli', 84531 => self::BaseGoerli,
            'base-sepolia', 84532 => self::BaseSepolia,
            'bnb', 56 => self::BNB,
            'bnbt', 97 => self::BNBt,

            default => ['unknown', \gmp_init(0, 10)],
        };
    }
}