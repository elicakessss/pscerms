<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseAccountController;
use Illuminate\Http\Request;

class AccountController extends BaseAccountController
{
    protected function getUserType(): string
    {
        return 'admin';
    }

    protected function getAccountIndexRoute(): string
    {
        return 'admin.account.index';
    }

    public function index()
    {
        $admin = $this->getAuthenticatedUser();
        return view('admin.account.index', compact('admin'));
    }

    public function edit()
    {
        $admin = $this->getAuthenticatedUser();
        return view('admin.account.edit', compact('admin'));
    }

    public function update(Request $request)
    {
        return $this->updateProfile($request);
    }
}
