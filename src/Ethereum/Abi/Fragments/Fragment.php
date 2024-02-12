<?php

namespace Ephers\Ethereum\Abi\Fragments;

use Ephers\Ethereum\Abi\Enums\FormatType;
use Ephers\Ethereum\Abi\Enums\FragmentType;

abstract class Fragment
{
    protected function __construct(
        public readonly FragmentType $type,
        public readonly array $inputs,
    ) { }

    protected function joinParams(
        FormatType $format,
        array $params = [],
    ): string {
        return '(' . \implode(
            $format === FormatType::Full ? ', ' : ',',
            \array_map(
                fn ($p) => $p->format($format),
                $params,
            ),
        ) . ')';
    }

    public abstract function format(FormatType $format): string;

    public static function from($fragment)
    {
        if (\is_string($fragment)) {
            $fragment = \json_decode($fragment, associative: true);
        }

        if (!\is_array($fragment)) {
            throw new \InvalidArgumentException();
        }

        if (!\array_key_exists('type', $fragment)) {
            throw new \InvalidArgumentException('Unsupported fragment object');
        }

        return match($fragment['type']) {
            // 'constructor' => ConstructorFragment::from($fragment),
            'function' => FunctionFragment::from($fragment),
            'constructor', 'error', 'event', 'fallback', 'struct' => null,
            default => throw new \InvalidArgumentException("Unsupported fragment type {$fragment['type']}"),
        };
    }
}
