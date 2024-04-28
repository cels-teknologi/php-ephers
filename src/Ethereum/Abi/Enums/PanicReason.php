<?php

namespace Ephers\Ethereum\Abi\Enums;

enum PanicReason: int
{
    case GENERIC_PANIC = 0x0;
    case ASSERT_FALSE = 0x1;
    case OVERFLOW = 0x11;
    case DIVIDE_BY_ZERO = 0x12;
    case ENUM_RANGE_ERROR = 0x21;
    case BAD_STORAGE_DATA = 0x22;
    case STACK_UNDERFLOW = 0x31;
    case ARRAY_RANGE_ERROR = 0x32;
    case OUT_OF_MEMORY = 0x41;
    case UNINITIALIZED_FUNCTION_CALL = 0x51;
}
