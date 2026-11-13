<?php

namespace App\Features\Auth\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    public function execute(
        string $login,
        string $password,
        bool $remember = false
    ): bool {
        $login = trim($login);

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        $success = Auth::guard('web')->attempt([
            $field => $login,
            'password' => $password,
        ], $remember);

        if (! $success) {
            throw ValidationException::withMessages([
                'login' => 'Username/email atau password salah.',
            ]);
        }

        return true;
    }
}
