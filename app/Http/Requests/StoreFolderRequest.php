<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // nanti bisa tambahkan policy kalau perlu
    }

    public function rules(): array
    {
        return [
            'workspace_id' => 'required|exists:workspaces,id',
            'name' => 'required|string|max:255',
            'is_private' => 'boolean',
        ];
    }
}
