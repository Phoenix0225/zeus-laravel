<?php

declare(strict_types=1);

namespace Zeus\Laravel\Http\Controllers;

use Illuminate\Routing\Controller;
use Zeus\Laravel\Rules\ActionRegistry;

class ActionController extends Controller
{
    public function index(ActionRegistry $registry)
    {
        return response()->json($registry->all());
    }
}
