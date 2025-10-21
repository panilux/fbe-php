<?php

declare(strict_types=1);


class State
{
	public const int UNKNOWN     = 0x00;
	public const int INVALID     = 0x01;
	public const int INITIALIZED = 0x02;
	public const int CALCULATED  = 0x04;
	public const int BROKEN      = 0x08;
	public const int GOOD        = self::INITIALIZED | self::CALCULATED;
	public const int BAD         = self::UNKNOWN | self::INVALID | self::BROKEN;
}
