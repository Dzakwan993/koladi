<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        // validasi minimal
        $request->validate([
            'message' => 'required',
            'email'   => 'nullable|email',
        ]);

        Feedback::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'message' => $request->message,
        ]);

        return redirect()->route('landingpage', [], 302)->with('success', 'Berhasil, Terima Kasih Atas Masukkanya... 🙌')->withFragment('feedback');


    }
}
