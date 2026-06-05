<?php

declare(strict_types=1);

namespace Zeus\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Zeus extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'zeus.manager';
    }
}
