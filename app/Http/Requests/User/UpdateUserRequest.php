<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class UpdateUserRequest extends BaseFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'email'        => ['sometimes', 'email', 'max:255', "unique:users,email,{$userId}"],
            'password'     => ['sometimes', 'string', 'min:6', 'max:255'],
            'role'         => ['sometimes', 'in:tm,expert,coordinator'],
            'fio'          => ['sometimes', 'string', 'max:255'],
            'cluster_name' => ['nullable', 'string', 'max:255'],
            'azs_count'    => ['sometimes', 'integer', 'min:0', 'max:1000'],
        ];
    }
}