<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'abbreviation',
        'description',
    ];

    /**
     * Get the students that belong to the department.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the advisers that belong to the department.
     */
    public function advisers()
    {
        return $this->hasMany(Adviser::class);
    }

    /**
     * Get the councils that belong to the department.
     */
    public function councils()
    {
        return $this->hasMany(Council::class);
    }
}

