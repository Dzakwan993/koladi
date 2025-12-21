<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
                        // ✅ File non-video maksimal 100 MB
                        if ($sizeInMB > 100) {
                            return $fail('File maksimal 100 MB. Ukuran file Anda: ' . round($sizeInMB, 2) . ' MB.');
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
        ];
    }
}