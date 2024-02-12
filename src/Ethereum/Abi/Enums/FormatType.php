<?php

namespace Ephers\Ethereum\Abi\Enums;

enum FormatType: string
{
    case Full = 'full';
    case Json = 'json';
    case Minimal = 'minimal';
    case SigHash = 'sighash';
}
