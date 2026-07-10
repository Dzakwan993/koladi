<?php

namespace App\Http\Controllers;

use App\Services\Document\DocumentParserService;
use Illuminate\Http\Request;

class BriefController extends Controller
{
     public function __construct(
        protected DocumentParserService $documentParser
    ) {}

    public function index() {
        return view('upload-brief');
    }
    
    public function upload(Request $request) {
        
        $request ->validate([ 
            'documents' => ['required','array'],
            'documents.*' => ['required','file'],
        ]);

        $document = $this->documentParser->parse(
             $request->file('documents')
        );
        

        // return view('brief-result', [
        //     documents->file('documents')
        // ]);

        return view('upload-brief', [
        'documents' => $document
        ]);
    }

}
