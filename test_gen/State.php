<?php

declare(strict_types=1);

class State
{
    public const UNKNOWN = 0x00;
    public const INVALID = 0x01;
    public const INITIALIZED = 0x02;
    public const CALCULATED = 0x04;
    public const BROKEN = 0x08;
    public const GOOD = initialized | calculated;
    public const BAD = unknown | invalid | broken;
}
