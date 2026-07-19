<?php

declare(strict_types=1);

namespace App\Http\Requests\Kpi;

use App\Http\Requests\BaseFormRequest;

class StoreIndicatorRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_code'  => ['required', 'in:ПМ,ОЭК,ЭКЛ,КБ'],
            'code'           => ['required', 'string', 'max:16', 'unique:kpi_indicators,code'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'unit'           => ['required', 'string', 'max:8'],
            'indicator_type' => ['required', 'in:base,extra,penalty'],

            // Условные правила: зависят от indicator_type
            'base_value'     => ['required_if:indicator_type,base', 'nullable', 'numeric', 'min:0'],
            'base_weight'    => ['required_if:indicator_type,base', 'nullable', 'integer', 'min:0'],
            'extra_weight'   => ['required_if:indicator_type,extra', 'nullable', 'integer'],
            'penalty_weight' => ['required_if:indicator_type,penalty', 'nullable', 'integer', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_code.in'           => 'Категория должна быть одной из: ПМ, ОЭК, ЭКЛ, КБ',
            'indicator_type.in'          => 'Тип должен быть: base, extra или penalty',
            'penalty_weight.max'         => 'Штрафной вес должен быть отрицательным',
            'base_value.required_if'     => 'Для base-показателя укажите base_value',
            'base_weight.required_if'    => 'Для base-показателя укажите base_weight',
            'extra_weight.required_if'   => 'Для extra-показателя укажите extra_weight',
            'penalty_weight.required_if' => 'Для penalty-показателя укажите penalty_weight',
        ];
    }
}