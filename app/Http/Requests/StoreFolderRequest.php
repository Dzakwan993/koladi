<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // nanti bisa tambahkan policy kalau perlu
    }

    public function rules()
    {
        $workspaceId = $this->input('workspace_id');
        $parentId = $this->input('parent_id');

        return [
            'workspace_id' => ['required', 'exists:workspaces,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                // unique per workspace + same parent (including null)
                Rule::unique('folders')->where(function ($query) use ($workspaceId, $parentId) {
                    $query->where('workspace_id', $workspaceId);
                    if ($parentId === null || $parentId === '') {
                        $query->whereNull('parent_id');
                    } else {
                        $query->where('parent_id', $parentId);
                    }
                }),
            ],
            'is_private' => ['sometimes', 'boolean'],
            'parent_id' => ['nullable','exists:folders,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Nama folder sudah terpakai di lokasi ini. Silakan gunakan nama lain.',
        ];
    }
}
