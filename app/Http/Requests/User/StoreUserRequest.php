<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class StoreUserRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:6', 'max:255'],
            'role'         => ['required', 'in:tm,expert,coordinator'],
            'fio'          => ['required', 'string', 'max:255'],
            'cluster_name' => ['nullable', 'string', 'max:255'],
            'azs_count'    => ['integer', 'min:0', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email обязателен',
            'email.unique'   => 'Пользователь с таким email уже существует',
            'password.min'   => 'Минимум 6 символов',
            'role.in'        => 'Роль должна быть одной из: tm, expert, coordinator',
            'fio.required'   => 'ФИО обязательно',
        ];
    }
}