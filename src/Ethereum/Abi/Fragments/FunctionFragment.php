<?php

namespace Ephers\Ethereum\Abi\Fragments;

use Ephers\Ethereum\Abi\Enums\FormatType;
use Ephers\Ethereum\Abi\Enums\FragmentType;
use Ephers\Ethereum\Abi\Enums\StateMutability;
use Ephers\Ethereum\Abi\ParamType;
use Ephers\Helpers\BinaryString;
use kornrunner\Keccak;

final class FunctionFragment extends NamedFragment
{
    public readonly bool $constant;
    public readonly bool $payable;

    public function __construct(
        string $name,
        public readonly StateMutability $stateMutability,
        array $inputs,
        public readonly array $outputs,
        public readonly ?\GMP $gas = null,
    ) {
        parent::__construct($name, FragmentType::Function, $inputs);

        $this->constant = (
            $stateMutability === StateMutability::View
            || $stateMutability === StateMutability::Pure
        );
        $this->payable = $stateMutability === StateMutability::Payable;
    }

    public function format(FormatType $format = FormatType::SigHash): string
    {
        if ($format === FormatType::Json) {
            return \json_encode([
                'type' => 'function',
                'name' => $this->name,
                'constant' => $this->constant,
                'stateMutability' => (
                    $this->stateMutability !== StateMutability::NonPayable
                        ? $this->stateMutability->value
                        : null
                ),
                'payable' => $this->payable,
                'gas' => ($this->gas ? \gmp_strval($this->gas, 10) : null),
                'inputs' => \array_map(
                    fn ($i) => \json_decode($i->format($format)),
                    $this->inputs,
                ),
                'outputs' => \array_map(
                    fn ($i) => \json_decode($i->format($format)),
                    $this->outputs,
                ),
            ]);
        }

        $result = [];

        if ($format !== FormatType::SigHash) {
            $result[] = 'function';
        }

        $result[] = $this->name . $this->joinParams($format, $this->inputs);

        if ($format !== FormatType::SigHash) {
            if ($this->stateMutability !== StateMutability::NonPayable) {
                $result[] = $this->stateMutability->value;
            }

            if ($this->outputs && \count($this->outputs) > 0) {
                $result[] = 'returns';
                $result[] = $this->joinParams($format, $this->outputs);
            }
            if ($this->gas) {
                $result[] = '@' . \gmp_strval($this->gas, 10);
            }
        }
        return \implode(' ', $result);
    }

    public function selector(): BinaryString
    {
        return BinaryString::of(Keccak::hash(
            $this->format(FormatType::SigHash),
            256,
            true,
        ))->split(0, 4);
    }

    public static function from($fragment)
    {
        if (!\is_array($fragment)) {
            throw new \InvalidArgumentException();
        }

        $stateMutability = StateMutability::Payable;
        if (\array_key_exists('stateMutability', $fragment)) {
            $stateMutability = StateMutability::from($fragment['stateMutability']);
        }
        else {
            // Use legacy Solidity ABI
            if (\array_key_exists('constant', $fragment)) {
                $stateMutability = StateMutability::View;
                if (!$fragment['constant']) {
                    $stateMutability = StateMutability::Payable;

                    if (\array_key_exists('payable', $fragment) && !$fragment['payable']) {
                        $stateMutability = StateMutability::NonPayable;
                    }
                }
            }
            else if (\array_key_exists('payable', $fragment) && !$fragment['payable']) {
                $stateMutability = StateMutability::NonPayable;
            }
        }

        return new FunctionFragment(
            $fragment['name'],
            $stateMutability,
            \array_map(
                fn ($_) => ParamType::from($_),
                \array_key_exists('inputs', $fragment) ? $fragment['inputs'] : [],
            ),
            \array_map(
                fn ($_) => ParamType::from($_),
                \array_key_exists('outputs', $fragment) ? $fragment['inputs'] : [],
            ),
            \array_key_exists('gas', $fragment)
                ? \gmp_init($fragment['gas'], 10)
                : null
        );
    }
}