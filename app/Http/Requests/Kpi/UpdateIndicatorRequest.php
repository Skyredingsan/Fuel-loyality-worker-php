<?php

declare(strict_types=1);

namespace App\Http\Requests\Kpi;

use App\Http\Requests\BaseFormRequest;

class UpdateIndicatorRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $indicatorId = $this->route('id');

        return [
            'category_code'  => ['sometimes', 'required', 'in:ПМ,ОЭК,ЭКЛ,КБ'],
            'code'           => ['sometimes', 'required', 'string', 'max:16', "unique:kpi_indicators,code,{$indicatorId}"],
            'name'           => ['sometimes', 'required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'unit'           => ['sometimes', 'required', 'string', 'max:8'],
            'indicator_type' => ['sometimes', 'required', 'in:base,extra,penalty'],
            'base_value'     => ['nullable', 'numeric', 'min:0'],
            'base_weight'    => ['nullable', 'integer', 'min:0'],
            'extra_weight'   => ['nullable', 'integer'],
            'penalty_weight' => ['nullable', 'integer', 'max:0'],
        ];
    }
}