<?php

namespace Ephers\Ethereum\Abi\Enums;

enum StateMutability: string
{
    case Payable = 'payable';
    case NonPayable = 'nonpayable';
    case View = 'view';
    case Pure = 'pure';
}
