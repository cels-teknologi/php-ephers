<?php

namespace Ephers\Ethereum\Providers;

use Ephers\Ephers;
use Ephers\Ethereum\Exception\JsonRpcException;
use Ephers\Ethereum\Network;
use Ephers\Providers\Provider;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class JsonRpcProvider extends Provider
{
    protected Client $client;

    public function __construct(
        protected string $url,
        string|\GMP|Network $network = null,
        array $options = [],
    ) {
        parent::__construct($network, $options);

        $ephersVersion = Ephers::VERSION;
        $guzzleVersion = Client::MAJOR_VERSION;
        $this->client = new Client([
            'headers' => [
                'User-Agent' => "Ephers/{$ephersVersion} Guzzle/{$guzzleVersion}",
            ],
        ]);
    }

    protected function _send(array $payload)
    {
        $body = \json_encode([
            ...$payload,
            'jsonrpc' => '2.0',
            'id' => '0x' . \str_pad(\gmp_strval(\gmp_mul(
                \random_int(0, PHP_INT_MAX),
                \random_int(0, PHP_INT_MAX),
            ), 16), 32, '0', STR_PAD_LEFT),
        ]);

        $request = new Request('POST', $this->url, body: $body);
        $response = \json_decode(
            (clone $this->client)->send($request)->getBody()->getContents(),
            flags: JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR,
        );

        if (\property_exists($response, 'error')) {
            throw new JsonRpcException($response->error);
        }

        return \property_exists($response, 'result') ? $response->result : null;
    }
}