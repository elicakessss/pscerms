<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SystemLogsController extends Controller
{
    /**
     * Display system logs
     */
    public function index(Request $request)
    {
        $query = SystemLog::query()->orderBy('performed_at', 'desc');

        // Apply filters
        if ($request->filled('user_type')) {
            $query->byUserType($request->user_type);
        }

        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        if ($request->filled('entity_type')) {
            $query->byEntityType($request->entity_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('entity_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->paginate(50)->withQueryString();

        // Get filter options
        $userTypes = SystemLog::distinct()->pluck('user_type')->filter()->sort();
        $actions = SystemLog::distinct()->pluck('action')->filter()->sort();
        $entityTypes = SystemLog::distinct()->pluck('entity_type')->filter()->sort();

        // Get statistics
        $stats = [
            'total_logs' => SystemLog::count(),
            'today_logs' => SystemLog::whereDate('performed_at', today())->count(),
            'this_week_logs' => SystemLog::whereBetween('performed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_logs' => SystemLog::whereMonth('performed_at', now()->month)->count(),
        ];

        return view('admin.system_logs.index', compact(
            'logs',
            'userTypes',
            'actions',
            'entityTypes',
            'stats'
        ));
    }

    /**
     * Show detailed log entry
     */
    public function show(SystemLog $systemLog)
    {
        return view('admin.system_logs.show', compact('systemLog'));
    }

    /**
     * Export logs to CSV
     */
    public function export(Request $request)
    {
        $query = SystemLog::query()->orderBy('performed_at', 'desc');

        // Apply same filters as index
        if ($request->filled('user_type')) {
            $query->byUserType($request->user_type);
        }

        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        if ($request->filled('entity_type')) {
            $query->byEntityType($request->entity_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('entity_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->get();

        $filename = 'system_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'User Type',
                'Action',
                'Entity Type',
                'Entity Name',
                'Description',
                'IP Address',
                'User Agent'
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->performed_at->format('Y-m-d H:i:s'),
                    $log->user_name ?? 'System',
                    $log->user_type ?? 'system',
                    $log->formatted_action,
                    $log->entity_type ?? '',
                    $log->entity_name ?? '',
                    $log->description,
                    $log->ip_address ?? '',
                    $log->user_agent ?? ''
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Clear old logs
     */
    public function clearOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        $cutoffDate = now()->subDays($request->days);
        $deletedCount = SystemLog::where('performed_at', '<', $cutoffDate)->delete();

        return redirect()->route('admin.system_logs.index')
            ->with('success', "Successfully deleted {$deletedCount} log entries older than {$request->days} days.");
    }

    /**
     * Get activity statistics for dashboard
     */
    public function getActivityStats()
    {
        $stats = [
            'recent_logins' => SystemLog::where('action', 'login')
                ->whereDate('performed_at', today())
                ->count(),
            'recent_activities' => SystemLog::whereDate('performed_at', today())
                ->count(),
            'top_users' => SystemLog::whereDate('performed_at', today())
                ->whereNotNull('user_name')
                ->selectRaw('user_name, user_type, COUNT(*) as activity_count')
                ->groupBy('user_name', 'user_type')
                ->orderBy('activity_count', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }
}
