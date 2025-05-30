<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Adviser;
use App\Models\Admin;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }
}