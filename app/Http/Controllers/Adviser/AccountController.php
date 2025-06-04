<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\BaseAccountController;
use Illuminate\Support\Facades\Auth;

class AccountController extends BaseAccountController
{
    protected function getAuthenticatedUser()
    {
        return Auth::user();
    }

    protected function getAccountIndexRoute(): string
    {
        return 'adviser.account.index';
    }

    protected function getProfileValidationRules($user): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:advisers,email,' . $user->id,
            'profile_picture' => 'nullable|image|max:2048',
        ];
    }

    protected function getProfilePicturePath(): string
    {
        return 'profile_pictures/advisers';
    }

    protected function getEmailUniqueRule($user): string
    {
        return 'unique:advisers,email,' . $user->id;
    }

    protected function getIndexView(): string
    {
        return 'adviser.account.index';
    }

    protected function getEditView(): string
    {
        return 'adviser.account.edit';
    }

    protected function getIndexViewData($user): array
    {
        return ['adviser' => $user];
    }
}


