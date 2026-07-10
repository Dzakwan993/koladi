<?php

namespace App\Http\Controllers;

use App\Models\Workspace;

class BriefController extends Controller
{
    public function brief(Workspace $workspace)
    {
        return view('ai-brief', compact('workspace'));
    }

    public function uploadbrief(Workspace $workspace)
    {
        return view('upload-brief', compact('workspace'));
    }
}