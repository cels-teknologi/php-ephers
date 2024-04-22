<?php

namespace Ephers\Ethereum\Abi;

use Ephers\Ethereum\Abi\Encoder\Encoder;
use Ephers\Ethereum\Abi\Enums\FormatType;
use Ephers\Ethereum\Abi\Enums\FragmentType;
use Ephers\Ethereum\Abi\Fragments\ConstructorFragment;
use Ephers\Ethereum\Abi\Fragments\Fragment;
use Ephers\Ethereum\Abi\Fragments\FunctionFragment;
use Ephers\Helpers\BinaryString;

final class AbiInterface
{
    protected array $functions = [];
    protected array $events = [];
    protected array $errors = [];
    protected array $fragments = [];
    protected ?ConstructorFragment $deploy;

    public function __construct(array|string $rawFragments)
    {
        if (\is_string($rawFragments)) {
            $rawFragments = \json_decode($rawFragments, associative: true);
        };

        foreach ($rawFragments as $rawFragment) {
            $fragment = Fragment::from($rawFragment);
            if (!$fragment) {
                continue;
            }

            $this->fragments[] = $fragment;

            $bucket = match ($fragment->type) {
                FragmentType::Function => 'functions',
                default => null,
            };

            if ($bucket) {
                $signature = $fragment->format();
                if (\array_key_exists($signature, $this->{$bucket})) {
                    continue;
                }

                $this->{$bucket}[$signature] = $fragment;
            }
        }
    }

    public function getFunction(string $name, bool $unique = true): ?FunctionFragment
    {
        $matches = [];
        if (BinaryString::isHex($name)) {
            // Function selector: slice(keccak256bytes(sighash), 0, 4)
            $matches = \array_filter(
                \array_values($this->functions),
                fn ($f) => (
                    $f->selector()->compare(
                        BinaryString::fromHex($name)
                    ) === 0
                ),
            );
        }
        else if (\preg_match(
            pattern: '/^[A-Za-z][0-9A-Za-z]*$/',
            subject: $name,
        )) {
            // Bare function name...
            $matches = \array_values(\array_filter(
                $this->functions,
                fn ($f, $n) => (\explode('(', $n)[0] === $name),
                ARRAY_FILTER_USE_BOTH,
            ));
            /**
             * @todo: Check for argument(s) type, if any.
             */
        }

        if (\count($matches) > 1 && $unique) {
            $matchStr = \implode(', ', \array_map(
                fn ($f) => $f->format(FormatType::SigHash),
                $matches,
            ));
            throw new \InvalidArgumentException("Ambiguous function selector, matches {$matchStr}");
        }

        return \count($matches) > 0 ? $matches[0] : null;
    }

    /**
     * @todo
     *  Decodes the %%data%% from a transaction ``tx.data`` for
     *  the function specified (see [[getFunction]] for valid values
     *  for %%fragment%%).
     *
     *  Most developers should prefer the [[parseTransaction]] method
     *  instead, which will automatically detect the fragment.
     */
    public function decodeFunctionData(
        FunctionFragment $fragment,
        BinaryString $data,
    ) {
    //     if (typeof(fragment) === "string") {
    //         const f = this.getFunction(fragment);
    //         assertArgument(f, "unknown function", "fragment", fragment);
    //         fragment = f;
    //     }

    //     assertArgument(dataSlice(data, 0, 4) === fragment.selector,
    //         `data signature does not match function ${ fragment.name }.`, "data", data);

    //     return this._decodeParams(fragment.inputs, dataSlice(data, 4));
    }

    /**
     *  @todo
     */
    public function encodeFunctionData(
        FunctionFragment $fragment,
        array $values
    ): string {
        return $fragment->selector()->concat(
            $this->encodeParams($fragment->inputs, $values),
        );
    }

    protected function encodeParams(array $types, array $values): BinaryString
    {
        if (\count($types) !== \count($values)) {
            throw new \InvalidArgumentException('Types & values mismatch');
        }

        return Encoder::encode(
            new ParamType('_', 'tuple', 'tuple', components: $types),
            $values,
        );
        // return \implode(\array_map(
        //     fn
        // ))
    }

    public static function from($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (\is_array($value) || \is_string($value)) {
            return new self($value);
        }
    }
}
