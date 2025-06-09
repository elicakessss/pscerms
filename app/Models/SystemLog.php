<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'user_name',
        'action',
        'entity_type',
        'entity_id',
        'entity_name',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'performed_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action (polymorphic relationship)
     */
    public function user()
    {
        switch ($this->user_type) {
            case 'student':
                return $this->belongsTo(Student::class, 'user_id');
            case 'adviser':
                return $this->belongsTo(Adviser::class, 'user_id');
            case 'admin':
                return $this->belongsTo(Admin::class, 'user_id');
            default:
                return null;
        }
    }

    /**
     * Scope to filter by user type
     */
    public function scopeByUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope to filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by entity type
     */
    public function scopeByEntityType($query, $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted action name
     */
    public function getFormattedActionAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Get formatted user type
     */
    public function getFormattedUserTypeAttribute()
    {
        return ucfirst($this->user_type);
    }

    /**
     * Get action color for UI
     */
    public function getActionColorAttribute()
    {
        return match($this->action) {
            'login' => 'text-green-600',
            'logout' => 'text-gray-600',
            'create' => 'text-blue-600',
            'update' => 'text-yellow-600',
            'delete' => 'text-red-600',
            'view' => 'text-purple-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Get action icon for UI
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'create' => 'fas fa-plus',
            'update' => 'fas fa-edit',
            'delete' => 'fas fa-trash',
            'view' => 'fas fa-eye',
            default => 'fas fa-info-circle',
        };
    }
}
