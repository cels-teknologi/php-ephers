<?php

namespace Ephers\Wordlists;

abstract class Wordlist
{
    //
    public function split(string $mnemonic): array
    {
        return \preg_split(
            pattern: '/\s+/',
            subject: $mnemonic,
        );
    }

    public function join(array $words): string
    {
        return \implode(' ', $words);
    }

    abstract public function get(int $index): string;
    abstract public function getWordIndex(string $word): int;
}
