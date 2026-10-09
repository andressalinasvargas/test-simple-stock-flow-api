<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class PlaceSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.productId' => ['required', 'string', 'uuid'],
            'lines.*.quantity' => ['required', 'integer', 'gt:0'],
        ];
    }
}
