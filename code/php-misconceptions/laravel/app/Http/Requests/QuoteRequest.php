<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class QuoteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'between:1,100'],
        ];
    }

    public function quantity(): int
    {
        return (int) $this->validated('quantity');
    }
}
