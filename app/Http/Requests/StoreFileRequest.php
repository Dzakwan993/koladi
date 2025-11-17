<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'workspace_id' => ['required', 'exists:workspaces,id'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'file' => ['required', 'file', 'max:20480'], // 20 MB
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Silakan pilih file untuk diunggah.',
        ];
    }
}
