<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendSkeletonController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Route identifiers only make preview navigation possible in Tahap 3.
        // Resolve authorized records in the module controllers in later stages.
        return view($request->route('view'));
    }
}
