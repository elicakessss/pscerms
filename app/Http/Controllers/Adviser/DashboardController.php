<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the adviser dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the authenticated adviser
        $adviser = auth()->user();
        
        // You can add any data you want to pass to the view here
        // For example, count of students in the adviser's department
        
        return view('adviser.dashboard', compact('adviser'));
    }
}