<?php

declare(strict_types=1);

namespace Zeus\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Zeus\Core\Registry\UiRegistry;

class UiController
{
    public function __construct(
        private readonly UiRegistry $uiRegistry
    ) {
    }

    public function config(): JsonResponse
    {
        return response()->json([
            'menus' => $this->uiRegistry->getMenus()
        ]);
    }

    public function screen(string $id): JsonResponse
    {
        $screen = $this->uiRegistry->getScreen($id);

        if ($screen === null) {
            return response()->json(['message' => 'Screen not found'], 404);
        }

        return response()->json($screen);
    }
}
