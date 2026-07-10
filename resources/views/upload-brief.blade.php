@extends('layouts.app')

@section('title', 'Video Tutorial')

@section('content')

 <form
        action="{{ route('brief.upload') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="mb-3">
            <label class="form-label">
                Project Documents
            </label>

            <input
                type="file"
                name="documents[]"
                class="form-control"
                multiple
                required
            >
        </div>

        <button class="btn btn-primary ">
            Upload
        </button>

    </form>

    @if(isset($documents))

    <hr>

    <h3>Parser Result</h3>

    <pre style="white-space: pre-wrap; word-wrap: break-word;">
    {{ json_encode($documents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
</pre>

    @endif

@endsection