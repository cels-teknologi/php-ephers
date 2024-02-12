<?php

namespace Ephers\Ethereum\Abi\Fragments;

use Ephers\Ethereum\Abi\Enums\FragmentType;

abstract class NamedFragment extends Fragment
{
    public function __construct(
        protected readonly string $name,
        FragmentType $type,
        array $inputs
    ) {
        parent::__construct($type, $inputs);
    }
}