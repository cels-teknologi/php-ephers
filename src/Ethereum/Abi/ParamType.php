<?php

namespace Ephers\Ethereum\Abi;

use Ephers\Ethereum\Abi\Enums\FormatType;
use InvalidArgumentException;

final class ParamType
{
    public const RE_TYPE = '/^(address|bool|bytes([0-9]*)|string|u?int([0-9]*))$/';
    public const RE_TYPE_ARRAY = '/^(.*)\[([0-9]*)\]$/';

    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $baseType,
        public readonly ?bool $indexed = null,
        public readonly ?array $components = null,
        public readonly ?int $length = null,
        public readonly ?ParamType $children = null,
    ) {
        if ($baseType === 'array') {
            if ($length === null || !$children) {
                throw new \InvalidArgumentException('Array types must specify length & children');
            }
        }
        else if ($length !== null || $children) {
            throw new \InvalidArgumentException('Non-array types must not have length & children');
        }

        if ($baseType === 'tuple') {
            if (! \is_array($components)) {
                throw new \InvalidArgumentException();
            }
        }
        else if ($components) {
            throw new \InvalidArgumentException();
        }
    }

    public function format(FormatType $format = FormatType::SigHash): string
    {
        $name = $this->name || '';
        if ($format === FormatType::Json) {
            if ($this->isArray()) {
                $result = \json_decode(
                    $this->children->format($format),
                    associative: true,
                );
                $result['name'] = $name;
                $result['type'] .= ($this->length < 0
                    ? '[]'
                    : "[{$this->length}]"
                );

                return \json_encode($result);
            }

            $result = [
                'type' => $this->baseType === 'tuple' ? 'tuple' : $this->type,
                'name' => $name,
            ];

            if ($this->indexed !== null) {
                $result['indexed'] = $this->indexed;
            }
            if ($this->isTuple()) {
                $result['components'] = \array_map(
                    fn ($comp) => \json_decode(
                        $comp->format($format),
                        associative: true,
                    ),
                    $this->components,
                );
            }
            return \json_encode($result);
        }

        $result = '';
        
        if ($this->isArray()) {
            $result .= $this->children->format($format);
            $result .= ($this->length < 0
                ? '[]'
                : "[{$this->length}]"
            );
        }
        else if ($this->isTuple()) {
            $result .= '(';
            $result .= \implode(
                $format === FormatType::Full ? ', ' : ',',
                \array_map(
                    fn ($comp) => $comp->format($format),
                    $this->components,
                ),
            );
            $result .= ')';
        }
        else {
            $result .= $this->type;
        }

        return $result;
    }

    public function isArray(): bool
    {
        return $this->baseType === 'array'
            && $this->children
            && $this->length !== null;
    }

    public function isTuple(): bool
    {
        return $this->baseType === 'tuple'
            && $this->components;
    }

    public function isIndexable(): bool
    {
        return $this->indexed != null;
    }

    public static function from($obj, ?bool $allowIndexed = null): self
    {
        if ($obj instanceof self) {
            return $obj;
        }
        
        $name = \array_key_exists('name', $obj) ? $obj['name'] : '';
        if ($name && !\preg_match(
            pattern: '/^([a-zA-Z$_][a-zA-Z0-9$_]*)$/',
            subject: $obj['name'],
        )) {
            throw new InvalidArgumentException('Invalid parameter name');
        }

        $indexed = \array_key_exists('indexed', $obj) ? $obj['indexed'] : null;
        if ($indexed !== null && !$allowIndexed) {
            throw new InvalidArgumentException('Parameter cannot be indexed');
        }
        $indexed = (bool) $indexed;

        // Check if type is an array
        $matches = [];
        if (\preg_match(
            pattern: self::RE_TYPE_ARRAY,
            subject: $obj['type'],
            matches: $matches,
        )) {
            $length = (int) $matches[2] || -1;
            $children = self::from([
                'type' => $matches[1],
                'components' => $obj['components'],
            ]);

            return new self(
                $name,
                $obj['type'],
                'array',
                $indexed,
                length: $length,
                children: $children,
            );
        }

        // Check if type is a tuple
        if ($obj['type'] === 'tuple'
            || \str_starts_with($obj['type'], 'tuple(')
            || \str_starts_with($obj['type'], '(')
        ) {
            $components = \array_key_exists('components', $obj)
                ? \array_map(
                    fn ($c) => ParamType::from($c),
                    $obj['components'],
                )
                : null;

            return new self($name, $obj['type'], 'tuple', $indexed, $components);
        }

        $type = $obj['type'];
        if (!\preg_match(
            pattern: self::RE_TYPE,
            subject: $type,
            matches: $matches,
        )) {
            throw new \InvalidArgumentException("Invalid type {$obj['type']}");
        }

        if (\count($matches) >= 3 && $matches[2]) {
            // bytesXX
        }
        else if (\count($matches) >= 4 && $matches[3]) {
            // intXX or uintXX
        }

        $type = match($type) {
            'uint' => 'uint256',
            'int' => 'int256',
            default => $type,
        };

        return new self($name, $type, $type, $indexed);
    }
}
