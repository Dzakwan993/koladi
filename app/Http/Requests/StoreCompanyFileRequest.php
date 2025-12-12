<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'folder_id' => ['nullable', 'uuid', 'exists:folders,id'],
            'file' => ['required', 'file', 'max:51200'], // 50 MB (sesuai dengan yang di controller)
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Silakan pilih file untuk diunggah.',
            'file.max' => 'Ukuran file maksimal 50 MB.',
            'company_id.required' => 'Company ID wajib diisi.',
            'company_id.exists' => 'Company tidak ditemukan.',
        ];
    }
}