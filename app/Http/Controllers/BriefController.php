<?php

namespace App\Http\Controllers;

class BriefController extends Controller
{
    public function brief()
    {
        return view('ai-brief');
    }

    public function uploadbrief()
    {
        return view('upload-brief');
    }
}
