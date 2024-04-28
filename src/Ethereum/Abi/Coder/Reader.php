<?php

namespace Ephers\Ethereum\Abi\Coder;

use Ephers\Ethereum\Abi\ParamType;
use Ephers\Ethereum\Constants;
use Ephers\Helpers\BinaryString;

final class Reader
{
    private $bytesRead = 0;

    public function __construct(private BinaryString $data)
    {
        //
    }
}
