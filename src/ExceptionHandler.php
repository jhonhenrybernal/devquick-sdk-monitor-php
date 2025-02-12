<?php

namespace DevQuick\ReportDevPhp;

use Throwable;

class ExceptionHandler
{
    protected static $monitor;

    public static function setClient(DevQuickMonitor $monitor)
    {
        self::$monitor = $monitor;
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleException(Throwable $exception)
    {
        if (self::$monitor) {
            self::$monitor->reportException($exception);
        }
    }
}
