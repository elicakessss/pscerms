<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Adviser extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'id_number',
        'first_name',
        'last_name',
        'email',
        'password',
        'department_id',
        'profile_picture',
        'description',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Department relationship
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the councils that belong to the adviser.
     */
    public function councils()
    {
        return $this->hasMany(Council::class);
    }
}
