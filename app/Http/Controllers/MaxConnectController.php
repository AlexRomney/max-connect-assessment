<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Actions\GetAdMetricsAction;

final class MaxConnectController extends Controller
{
    public function __invoke(Request $request, GetAdMetricsAction $action)
    {
        $refresh = $request->isMethod('post');

        return Inertia::render('Home', $action->handle($refresh));
    }
}
