<?php

declare(strict_types=1);

namespace App\Http\Requests\Result;

use App\Http\Requests\BaseFormRequest;

class RejectResultRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Причина отклонения обязательна',
            'reason.min'      => 'Минимум 5 символов',
        ];
    }
}