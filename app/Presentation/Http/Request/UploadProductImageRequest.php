<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class UploadProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }
}

