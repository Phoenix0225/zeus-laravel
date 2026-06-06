<?php

declare(strict_types=1);

namespace Zeus\Laravel\Rules;

class ActionRegistry
{
    private array $actions = [];

    public function register(string $class, string $name, string $description, array $expectedParams = []): void
    {
        $this->actions[] = [
            'class' => $class,
            'name' => $name,
            'description' => $description,
            'expected_params' => $expectedParams,
        ];
    }

    public function all(): array
    {
        return $this->actions;
    }
}
