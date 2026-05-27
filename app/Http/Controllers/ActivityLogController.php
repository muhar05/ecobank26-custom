<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        // Search event type
        if ($request->filled('search')) {
            $query->where('event_type', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
        }

        // Filter severity
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        // Filter user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->latest('id')->paginate(15)->withQueryString();
        
        $users = User::orderBy('name')->get(['id', 'name']);
        
        return view('admin.audit-logs', compact('logs', 'users'));
    }
}
