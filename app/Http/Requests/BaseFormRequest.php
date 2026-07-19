<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

/**
 * Базовый Form Request: отдаёт JSON 422 вместо редиректа при ошибке валидации.
 */
abstract class BaseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // проверка роли делается в middleware
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => (new ValidationException($validator))->errors(),
                'code'    => 422,
            ], 422)
        );
    }
}