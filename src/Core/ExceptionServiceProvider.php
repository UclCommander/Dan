<?php

namespace Dan\Core;

use Dan\Events\Event;
use Illuminate\Support\ServiceProvider;

class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        events()->subscribe('console.exception')
            ->priority(Event::VeryHigh)
            ->handler(function (\Throwable $exception) {
                $to = formatLocation(config('dan.network_console'));

                $file = relativePath($exception->getFile());

                if (empty($to)) {
                    return;
                }

                $to['channel']->message("Exception was thrown. {$exception->getMessage()} - On line {$exception->getLine()} of {$file} - See the latest error log for more details.");
            });
    }
}
