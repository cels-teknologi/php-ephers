<?php

namespace Ephers\Providers;

abstract class Provider
{
    public function __construct(
        protected $network,
        protected array $options = [],
    ) { }

    public function send(string $method, array $params = [])
    {
        return $this->_send([
            'method' => $method,
            'params' => $params,
        ]);
    }

    #[\ReturnTypeWillChange]
    protected abstract function _send(array $payload);
}