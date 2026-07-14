<?php

namespace App\Http\Controllers;

use App\Models\Workspace;

class ActivityLogController extends Controller
{
    public function index(Workspace $workspace)
    {
        return view('activity-log', compact('workspace'));
    }
}
