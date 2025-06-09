<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemLogService
{
    /**
     * Log user activity
     */
    public static function log(
        string $action,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $entityName = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null
    ) {
        $request = $request ?: request();
        
        // Get current authenticated user
        $user = null;
        $userType = null;
        $userName = null;

        // Check all guards for authenticated user
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $userType = 'admin';
            $userName = $user->first_name . ' ' . $user->last_name;
        } elseif (Auth::guard('adviser')->check()) {
            $user = Auth::guard('adviser')->user();
            $userType = 'adviser';
            $userName = $user->first_name . ' ' . $user->last_name;
        } elseif (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
            $userType = 'student';
            $userName = $user->first_name . ' ' . $user->last_name;
        }

        SystemLog::create([
            'user_id' => $user?->id,
            'user_type' => $userType,
            'user_name' => $userName,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_name' => $entityName,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
        ]);
    }

    /**
     * Log login activity
     */
    public static function logLogin(string $userType, $user, Request $request)
    {
        self::log(
            action: 'login',
            description: "User {$user->first_name} {$user->last_name} ({$userType}) logged in",
            entityType: $userType,
            entityId: $user->id,
            entityName: $user->first_name . ' ' . $user->last_name,
            request: $request
        );
    }

    /**
     * Log logout activity
     */
    public static function logLogout(string $userType, $user, Request $request)
    {
        self::log(
            action: 'logout',
            description: "User {$user->first_name} {$user->last_name} ({$userType}) logged out",
            entityType: $userType,
            entityId: $user->id,
            entityName: $user->first_name . ' ' . $user->last_name,
            request: $request
        );
    }

    /**
     * Log create activity
     */
    public static function logCreate(string $entityType, $entity, array $newValues = [], Request $request = null)
    {
        $entityName = self::getEntityName($entityType, $entity);
        
        self::log(
            action: 'create',
            description: "Created new {$entityType}: {$entityName}",
            entityType: $entityType,
            entityId: $entity->id,
            entityName: $entityName,
            newValues: $newValues,
            request: $request
        );
    }

    /**
     * Log update activity
     */
    public static function logUpdate(string $entityType, $entity, array $oldValues = [], array $newValues = [], Request $request = null)
    {
        $entityName = self::getEntityName($entityType, $entity);
        
        self::log(
            action: 'update',
            description: "Updated {$entityType}: {$entityName}",
            entityType: $entityType,
            entityId: $entity->id,
            entityName: $entityName,
            oldValues: $oldValues,
            newValues: $newValues,
            request: $request
        );
    }

    /**
     * Log delete activity
     */
    public static function logDelete(string $entityType, $entity, array $oldValues = [], Request $request = null)
    {
        $entityName = self::getEntityName($entityType, $entity);
        
        self::log(
            action: 'delete',
            description: "Deleted {$entityType}: {$entityName}",
            entityType: $entityType,
            entityId: $entity->id,
            entityName: $entityName,
            oldValues: $oldValues,
            request: $request
        );
    }

    /**
     * Log view activity
     */
    public static function logView(string $entityType, $entity, Request $request = null)
    {
        $entityName = self::getEntityName($entityType, $entity);
        
        self::log(
            action: 'view',
            description: "Viewed {$entityType}: {$entityName}",
            entityType: $entityType,
            entityId: $entity->id,
            entityName: $entityName,
            request: $request
        );
    }

    /**
     * Get entity name based on type
     */
    private static function getEntityName(string $entityType, $entity): string
    {
        return match($entityType) {
            'student', 'adviser', 'admin' => $entity->first_name . ' ' . $entity->last_name,
            'council' => $entity->name,
            'department' => $entity->name,
            'evaluation' => "Evaluation for {$entity->evaluatedStudent->first_name} {$entity->evaluatedStudent->last_name}",
            default => $entity->name ?? $entity->title ?? "ID: {$entity->id}",
        };
    }
}
