<?php

namespace Ephers\Ethereum\Contracts;

use Ephers\Ethereum\Abi\AbiInterface;
use Ephers\Helpers\BinaryString;
use Ephers\Providers\Provider;

final class Contract
{
    public function __construct(
        public readonly BinaryString $address,
        public readonly AbiInterface $interface,
        public readonly ?Provider $provider = null,
    ) { }

    public function connect(Provider $p): Contract
    {
        return new self(
            clone $this->address,
            clone $this->interface,
            $p,
        );
    }

    public function getContractMethod(string $name, array $args): ContractMethod
    {
        if (!$this->provider) {
            throw new \Exception('Not connected to a provider');
        }

        $fragment = $this->interface->getFunction($name);
        if (!$fragment) {
            throw new \BadMethodCallException();
        }

        return new ContractMethod(
            $this,
            $name,
            $fragment,
            $args ?? [],
        );
    }

    public function has(string $name)
    {

    }

    public function __call(string $name, array $args)
    {
        return $this->getContractMethod($name, $args ?? [])();
    }

    public function __get(string $name)
    {

    }
}
