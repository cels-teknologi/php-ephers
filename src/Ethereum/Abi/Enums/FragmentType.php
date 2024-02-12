<?php

namespace Ephers\Ethereum\Abi\Enums;

enum FragmentType: string
{
    case Constructor = 'constructor';
    case Error = 'error';
    case Event = 'event';
    case Fallback = 'fallback';
    case Function = 'function';
    case Struct = 'struct';
}
