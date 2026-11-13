<?php

namespace App\Features\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Accept 'username' as an alias for 'login' (for backward compatibility)
        if ($this->has('username') && ! $this->has('login')) {
            $this->merge(['login' => $this->input('username')]);
        }

        $login = trim((string) $this->input('login'));

        $this->merge([
            'login' => filter_var($login, FILTER_VALIDATE_EMAIL)
                ? strtolower($login)
                : $login,
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
