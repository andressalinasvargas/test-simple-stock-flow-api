<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric', 'gt:0'],
            'stock' => ['required', 'integer', 'gte:0'],
            'categoryId' => ['required', 'string', 'uuid'],
            'sku' => ['nullable', 'string'],
        ];
    }
}
