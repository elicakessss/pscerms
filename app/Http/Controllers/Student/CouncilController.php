<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Council;
use App\Models\CouncilOfficer;
use Illuminate\Support\Facades\Auth;

class CouncilController extends Controller
{
    /**
     * Display a listing of the student's councils.
     */
    public function index()
    {
        $student = Auth::user();

        // Get councils where the student is an officer
        $councils = Council::whereHas('councilOfficers', function($query) use ($student) {
            $query->where('student_id', $student->id);
        })
        ->with(['department', 'councilOfficers.student', 'adviser'])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('student.my_councils.index', compact('councils'));
    }

    /**
     * Display the specified council.
     */
    public function show(Council $council)
    {
        $student = Auth::user();

        // Check if student is an officer in this council
        $studentOfficer = CouncilOfficer::where('council_id', $council->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$studentOfficer) {
            abort(403, 'You are not a member of this council.');
        }

        // Load council with all officers and their students
        $council->load([
            'department',
            'adviser',
            'councilOfficers.student.department'
        ]);

        // Get all officers ordered by position level
        $officers = $council->councilOfficers()
            ->with('student.department')
            ->orderBy('position_level')
            ->orderBy('position_title')
            ->get();

        return view('student.my_councils.show', compact('council', 'officers', 'studentOfficer'));
    }
}
