<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\BaseAccountController;
use Illuminate\Http\Request;

class AccountController extends BaseAccountController
{
    protected function getUserType(): string
    {
        return 'student';
    }

    protected function getAccountIndexRoute(): string
    {
        return 'student.account.index';
    }

    public function index()
    {
        $student = $this->getAuthenticatedUser();

        // Get completed councils for portfolio
        $completedCouncils = $student->councilOfficers()
            ->with(['council.department'])
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('student.account.index', compact('student', 'completedCouncils'));
    }

    public function edit()
    {
        $student = $this->getAuthenticatedUser();
        return view('student.account.edit', compact('student'));
    }

    public function update(Request $request)
    {
        return $this->updateProfile($request);
    }
}

