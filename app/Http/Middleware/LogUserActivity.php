<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SystemLogService;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users and successful responses
        if ($this->shouldLog($request, $response)) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request should be logged
     */
    private function shouldLog(Request $request, Response $response): bool
    {
        // Don't log if response is not successful
        if (!$response->isSuccessful()) {
            return false;
        }

        // Don't log AJAX requests for real-time features
        if ($request->ajax() && !$this->isImportantAjaxRequest($request)) {
            return false;
        }

        // Don't log asset requests
        if ($this->isAssetRequest($request)) {
            return false;
        }

        // Don't log system log pages to avoid infinite loops
        if (str_contains($request->path(), 'system-logs')) {
            return false;
        }

        // Only log if user is authenticated
        return auth()->guard('admin')->check() || 
               auth()->guard('adviser')->check() || 
               auth()->guard('student')->check();
    }

    /**
     * Log the activity
     */
    private function logActivity(Request $request, Response $response): void
    {
        $method = $request->method();
        $path = $request->path();
        $action = $this->determineAction($method, $path);

        // Skip logging for simple view actions unless it's important
        if ($action === 'view' && !$this->isImportantViewAction($path)) {
            return;
        }

        $description = $this->generateDescription($method, $path, $request);

        SystemLogService::log(
            action: $action,
            description: $description,
            request: $request
        );
    }

    /**
     * Determine the action type based on HTTP method and path
     */
    private function determineAction(string $method, string $path): string
    {
        // Login/logout actions
        if (str_contains($path, 'login')) {
            return 'login';
        }
        if (str_contains($path, 'logout')) {
            return 'logout';
        }

        // CRUD actions based on HTTP method
        return match($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view',
        };
    }

    /**
     * Generate human-readable description
     */
    private function generateDescription(string $method, string $path, Request $request): string
    {
        $segments = explode('/', trim($path, '/'));
        
        // Handle specific routes
        if (str_contains($path, 'dashboard')) {
            return 'Accessed dashboard';
        }
        
        if (str_contains($path, 'account')) {
            return match($method) {
                'GET' => 'Viewed account settings',
                'PUT', 'PATCH' => 'Updated account information',
                default => 'Accessed account page',
            };
        }

        // Handle management pages
        if (str_contains($path, 'management') || str_contains($path, 'users') || str_contains($path, 'councils')) {
            $entity = $this->extractEntityFromPath($path);
            return match($method) {
                'GET' => "Viewed {$entity} management page",
                'POST' => "Created new {$entity}",
                'PUT', 'PATCH' => "Updated {$entity}",
                'DELETE' => "Deleted {$entity}",
                default => "Accessed {$entity} page",
            };
        }

        // Handle evaluation pages
        if (str_contains($path, 'evaluation')) {
            return match($method) {
                'GET' => 'Viewed evaluation page',
                'POST' => 'Submitted evaluation',
                'PUT', 'PATCH' => 'Updated evaluation',
                'DELETE' => 'Deleted evaluation',
                default => 'Accessed evaluation system',
            };
        }

        // Generic description
        return match($method) {
            'GET' => "Viewed page: /{$path}",
            'POST' => "Created resource on: /{$path}",
            'PUT', 'PATCH' => "Updated resource on: /{$path}",
            'DELETE' => "Deleted resource on: /{$path}",
            default => "Accessed: /{$path}",
        };
    }

    /**
     * Extract entity type from path
     */
    private function extractEntityFromPath(string $path): string
    {
        if (str_contains($path, 'user')) return 'user';
        if (str_contains($path, 'student')) return 'student';
        if (str_contains($path, 'adviser')) return 'adviser';
        if (str_contains($path, 'admin')) return 'admin';
        if (str_contains($path, 'council')) return 'council';
        if (str_contains($path, 'department')) return 'department';
        if (str_contains($path, 'evaluation')) return 'evaluation';
        
        return 'resource';
    }

    /**
     * Check if this is an important AJAX request that should be logged
     */
    private function isImportantAjaxRequest(Request $request): bool
    {
        $path = $request->path();
        
        // Log important AJAX actions
        return str_contains($path, 'evaluation') ||
               str_contains($path, 'council') ||
               str_contains($path, 'user') ||
               $request->method() !== 'GET';
    }

    /**
     * Check if this is an asset request
     */
    private function isAssetRequest(Request $request): bool
    {
        $path = $request->path();
        
        return str_contains($path, '.css') ||
               str_contains($path, '.js') ||
               str_contains($path, '.png') ||
               str_contains($path, '.jpg') ||
               str_contains($path, '.jpeg') ||
               str_contains($path, '.gif') ||
               str_contains($path, '.svg') ||
               str_contains($path, '.ico') ||
               str_contains($path, '/images/') ||
               str_contains($path, '/css/') ||
               str_contains($path, '/js/');
    }

    /**
     * Check if this is an important view action that should be logged
     */
    private function isImportantViewAction(string $path): bool
    {
        return str_contains($path, 'dashboard') ||
               str_contains($path, 'account') ||
               str_contains($path, 'management') ||
               str_contains($path, 'evaluation');
    }
}
