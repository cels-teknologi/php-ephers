<?php

namespace Ephers\Ethereum\Exception;

class JsonRpcException extends \RuntimeException
{
    public function __construct(
        protected array $error,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            \array_key_exists('message', $error) ? $error['message'] : 'Unknown error',
            \array_key_exists('code', $error) ? (int) $error['code'] : -1,
            $previous,
        );
    }
}