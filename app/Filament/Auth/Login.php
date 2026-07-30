<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username atau Email')
            ->placeholder('Masukkan username atau email')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = $data['email'];

        return [
            filter_var($login, FILTER_VALIDATE_EMAIL)
                ? 'email'
                : 'username' => $login,

            'password' => $data['password'],
        ];
    }
}