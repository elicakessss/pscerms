<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\BaseAccountController;
use Illuminate\Http\Request;

class AccountController extends BaseAccountController
{
    protected function getUserType(): string
    {
        return 'adviser';
    }

    protected function getAccountIndexRoute(): string
    {
        return 'adviser.account.index';
    }

    public function index()
    {
        $adviser = $this->getAuthenticatedUser();
        return view('adviser.account.index', compact('adviser'));
    }

    public function edit()
    {
        $adviser = $this->getAuthenticatedUser();
        return view('adviser.account.edit', compact('adviser'));
    }

    public function update(Request $request)
    {
        return $this->updateProfile($request);
    }
}


