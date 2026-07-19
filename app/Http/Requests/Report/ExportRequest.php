<?php

declare(strict_types=1);

namespace App\Http\Requests\Report;

use App\Http\Requests\BaseFormRequest;

class ExportRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'period' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ];
    }
}