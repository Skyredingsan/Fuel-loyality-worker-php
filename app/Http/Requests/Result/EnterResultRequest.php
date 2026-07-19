<?php

declare(strict_types=1);

namespace App\Http\Requests\Result;

use App\Http\Requests\BaseFormRequest;

/**
 * Запрос на ввод результатов экспертом.
 *
 * Полная аналогия Go EnterResultRequest:
 *   user_id   : int
 *   period    : YYYY-MM
 *   results   : [{ indicator_code, fact_value?, document_url? }]
 */
class EnterResultRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id'                  => ['required', 'integer', 'exists:users,id'],
            'period'                   => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'results'                  => ['required', 'array', 'min:1'],
            'results.*.indicator_code' => ['required', 'string', 'exists:kpi_indicators,code'],
            'results.*.fact_value'     => ['nullable', 'numeric', 'min:0'],
            'results.*.document_url'   => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.exists'                  => 'Указанный пользователь не существует',
            'period.regex'                    => 'Период должен быть в формате YYYY-MM (например, 2026-07)',
            'results.min'                     => 'Должен быть хотя бы один результат',
            'results.*.indicator_code.exists' => 'Показатель с таким кодом не найден',
        ];
    }
}