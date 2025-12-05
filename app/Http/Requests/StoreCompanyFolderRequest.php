<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $companyId = $this->input('company_id');
        $parentId = $this->input('parent_id');

        return [
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique per company + same parent (including null) + workspace_id NULL
                Rule::unique('folders')->where(function ($query) use ($companyId, $parentId) {
                    $query->where('company_id', $companyId)
                          ->whereNull('workspace_id'); // ← PENTING: hanya folder company-level
                    
                    if ($parentId === null || $parentId === '') {
                        $query->whereNull('parent_id');
                    } else {
                        $query->where('parent_id', $parentId);
                    }
                }),
            ],
            'is_private' => ['sometimes', 'boolean'],
            'parent_id' => ['nullable', 'uuid', 'exists:folders,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Nama folder sudah terpakai di lokasi ini. Silakan gunakan nama lain.',
            'company_id.required' => 'Company ID wajib diisi.',
            'company_id.exists' => 'Company tidak ditemukan.',
        ];
    }
}