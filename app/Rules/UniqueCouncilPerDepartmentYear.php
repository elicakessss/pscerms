<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Council;

class UniqueCouncilPerDepartmentYear implements ValidationRule
{
    protected $departmentId;
    protected $excludeCouncilId;

    public function __construct($departmentId, $excludeCouncilId = null)
    {
        $this->departmentId = $departmentId;
        $this->excludeCouncilId = $excludeCouncilId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Council::where('department_id', $this->departmentId)
            ->where('academic_year', $value);

        // Exclude current council if updating
        if ($this->excludeCouncilId) {
            $query->where('id', '!=', $this->excludeCouncilId);
        }

        if ($query->exists()) {
            $existingCouncil = $query->first();
            $fail("A council already exists for this department and academic year (Council ID: {$existingCouncil->id}, Status: {$existingCouncil->status}).");
        }
    }
}
