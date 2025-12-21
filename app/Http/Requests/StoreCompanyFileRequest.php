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
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value) {
                        return $fail('Silakan pilih file untuk diunggah.');
                    }

                    $mimeType = $value->getMimeType();
                    $sizeInMB = $value->getSize() / 1024 / 1024;

                    // ✅ Video maksimal 100 MB
                    if (str_starts_with($mimeType, 'video/')) {
                        if ($sizeInMB > 100) {
                            return $fail('File video maksimal 100 MB. Ukuran file Anda: ' . round($sizeInMB, 2) . ' MB.');
                        }
                    } else {
                        // ✅ File non-video maksimal 50 MB (sesuai kebutuhan company)
                        if ($sizeInMB > 50) {
                            return $fail('File maksimal 50 MB. Ukuran file Anda: ' . round($sizeInMB, 2) . ' MB.');
                        }
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Silakan pilih file untuk diunggah.',
            'file.file' => 'File yang diunggah tidak valid.',
            'company_id.required' => 'Company ID wajib diisi.',
            'company_id.exists' => 'Company tidak ditemukan.',
        ];
    }
}