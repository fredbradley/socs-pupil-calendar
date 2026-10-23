<?php

namespace FredBradley\SocsPupilCalendar\Exceptions;

use Exception;

class CalendarNotAvailableException extends Exception
{
    public static function forUrl(string $url): self
    {
        return new self("SOCS did not return a calendar for {$url}");
    }
}
