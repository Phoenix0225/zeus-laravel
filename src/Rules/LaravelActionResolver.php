<?php

declare(strict_types=1);

namespace Zeus\Laravel\Rules;

use Zeus\Core\Rules\ActionInterface;
use Zeus\Core\Rules\ActionResolverInterface;

class LaravelActionResolver implements ActionResolverInterface
{
    public function resolve(string $actionClass): ActionInterface
    {
        return app()->make($actionClass);
    }
}
