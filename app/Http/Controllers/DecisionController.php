<?php

namespace App\Http\Controllers;

use App\Models\Decision;
use App\Models\Workspace;

class DecisionController extends Controller
{
    public function index(Workspace $workspace)
    {
        $decisions = Decision::with('evidenceFile')
            ->where('workspace_id', $workspace->id)
            ->orderByDesc('decision_date')
            ->get();

        return view('activity-log', compact('workspace', 'decisions'));
    }
}
