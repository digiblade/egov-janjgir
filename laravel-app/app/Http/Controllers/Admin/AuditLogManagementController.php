<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'module' => ['nullable', 'string', 'in:'.implode(',', AuditLogger::modules())],
            'action' => ['nullable', 'string', 'max:120'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'string', 'max:64'],
            'actor_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $logs = AuditLog::query()->with('actor:id,name')
            ->when($filters['module'] ?? null, fn ($query, $module) => $query->where('module', $module))
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($filters['subject_type'] ?? null, fn ($query, $type) => $query->where('subject_type', $type))
            ->when($filters['subject_id'] ?? null, fn ($query, $id) => $query->where('subject_id', $id))
            ->when($filters['actor_id'] ?? null, fn ($query, $id) => $query->where('actor_id', $id))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('occurred_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('occurred_at', '<=', $date))
            ->orderByDesc('occurred_at')
            ->paginate(25)
            ->withQueryString();

        $actions = AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');
        $subjectTypes = AuditLog::query()->whereNotNull('subject_type')->select('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type');

        $modules = AuditLogger::modules();

        return view('admin.audit-logs.index', compact('logs', 'filters', 'actions', 'subjectTypes', 'modules'));
    }
}
