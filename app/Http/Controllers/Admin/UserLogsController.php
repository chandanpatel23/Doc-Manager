<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLog;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserLogsController extends Controller
{
    public function index(Request $request)
    {
        $q = UserLog::with('user')->orderBy('id', 'desc');

        if ($request->filled('user')) {
            $user = User::where('username', $request->input('user'))->orWhere('email', $request->input('user'))->first();
            if ($user) $q->where('user_id', $user->id);
        }

        if ($request->filled('event')) {
            $q->where('event', $request->input('event'));
        }

        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->input('to'));
        }

        // Export CSV
        if ($request->input('export') === 'csv') {
            $filename = 'user-logs-' . now()->format('Ymd_His') . '.csv';
            $response = new StreamedResponse(function () use ($q) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['id', 'user', 'event', 'ip_address', 'meta', 'created_at']);
                foreach ($q->cursor() as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->user->username ?? $log->user->name ?? 'Guest',
                        $log->event,
                        $log->ip_address,
                        json_encode($log->meta),
                        $log->created_at,
                    ]);
                }
                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            return $response;
        }

        $logs = $q->paginate(50)->withQueryString();
        return view('admin.user-logs.index', compact('logs'));
    }
}
