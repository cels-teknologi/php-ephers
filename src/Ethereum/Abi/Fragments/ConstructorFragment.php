<?php

namespace Ephers\Ethereum\Abi\Fragments;

use Ephers\Ethereum\Abi\Enums\FormatType;
use Ephers\Ethereum\Abi\Enums\FragmentType;
use Ephers\Ethereum\Abi\ParamType;

abstract class ConstructorFragment extends Fragment
{
    public function __construct(
        array $inputs,
        public readonly bool $payable,
        public readonly ?\GMP $gas = null,
    ) {
        parent::__construct(FragmentType::Constructor, $inputs);
    }

    public function format(FormatType $format = FormatType::Json): string
    {
        if ($format === FormatType::SigHash) {
            throw new \InvalidArgumentException('Cannot format a constructor for sighash');
        }

        if ($format === FormatType::Json) {
            return \json_encode([
                'type' => 'constructor',
                'stateMutability' => $this->payable ? 'payable' : 'undefined',
                'payable' => $this->payable,
                'gas' => ($this->gas ? \gmp_strval($this->gas, 10) : null),
                'inputs' => \array_map(
                    fn ($i) => $i->format($format),
                    $this->inputs,
                ),
            ]);
        }

        return \implode(' ', [
            'constructor' . (new ParamType(
                name: '',
                type: 'tuple',
                baseType: 'tuple',
                components: $this->inputs,
            ))->format($format),
            
            ...($this->payable ? ['payable'] : []),
            ...($this->gas ? ['@' . \gmp_strval($this->gas, 10)] : []),
        ]);
    }
}