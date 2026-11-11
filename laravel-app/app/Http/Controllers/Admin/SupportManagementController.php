<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupportNoteRequest;
use App\Http\Requests\Admin\SupportAssignmentRequest;
use App\Http\Requests\Admin\SupportRequestUpdateRequest;
use App\Models\SupportNote;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Support\AuditLogger;

class SupportManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:'.implode(',', SupportRequest::STATUSES)],
            'category' => ['nullable', 'string', 'max:80'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'archived' => ['nullable', 'in:0,1'],
            'assigned_to' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $requests = SupportRequest::query()->with('assignee')
            ->when(! $user->can('support.assign'), fn ($query) => $query->where('assigned_to', $user->id))
            ->when(($filters['archived'] ?? '0') === '1', fn ($query) => $query->whereNotNull('archived_at'), fn ($query) => $query->whereNull('archived_at'))
            ->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($search) => $search
                ->where('reference_number', 'like', '%'.$term.'%')
                ->orWhere('full_name', 'like', '%'.$term.'%')
                ->orWhere('email', 'like', '%'.$term.'%')
                ->orWhere('subject', 'like', '%'.$term.'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('submitted_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('submitted_at', '<=', $date))
            ->when(($filters['assigned_to'] ?? null) && $user->can('support.assign'), fn ($query) => $query->where('assigned_to', $filters['assigned_to']))
            ->orderByDesc('submitted_at')->paginate(20)->withQueryString();

        $categories = SupportRequest::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        $assignees = $user->can('support.assign')
            ? User::permission('support.view')->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('admin.support.index', compact('requests', 'categories', 'assignees', 'filters'));
    }

    public function show(Request $request, SupportRequest $supportRequest): View
    {
        $this->authorizeRequestAccess($request, $supportRequest);
        $supportRequest->load(['assignee', 'notes.author']);
        $assignees = $request->user()->can('support.assign')
            ? User::permission('support.view')->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('admin.support.show', compact('supportRequest', 'assignees'));
    }

    public function update(SupportRequestUpdateRequest $request, SupportRequest $supportRequest, AuditLogger $audit): RedirectResponse
    {
        $this->authorizeRequestAccess($request, $supportRequest);
        $attributes = $request->validated();

        DB::transaction(function () use ($attributes, $supportRequest): void {
            $supportRequest->status = $attributes['status'];

            if (in_array($supportRequest->status, ['resolved', 'closed'], true)) {
                $supportRequest->resolved_at ??= now();
            } else {
                $supportRequest->resolved_at = null;
            }

            $supportRequest->save();
        });
        $audit->record('support.status_changed', $supportRequest, ['status']);

        return to_route('admin.support.show', $supportRequest)->with('status', __('ui.admin_content.saved'));
    }

    public function assign(SupportAssignmentRequest $request, SupportRequest $supportRequest, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->can('support.assign'), 403);
        $this->authorizeRequestAccess($request, $supportRequest);
        $assigneeId = $request->validated()['assigned_to'] ?? null;

        if ($assigneeId !== null && ! User::permission('support.view')->whereKey($assigneeId)->exists()) {
            throw ValidationException::withMessages(['assigned_to' => __('ui.admin_content.support_invalid_assignee')]);
        }

        DB::transaction(function () use ($supportRequest, $assigneeId): void {
            $supportRequest->assigned_to = $assigneeId;
            $supportRequest->save();
        });
        $audit->record('support.assigned', $supportRequest, ['assigned_to']);

        return to_route('admin.support.show', $supportRequest)->with('status', __('ui.admin_content.saved'));
    }

    public function storeNote(SupportNoteRequest $request, SupportRequest $supportRequest, AuditLogger $audit): RedirectResponse
    {
        $this->authorizeRequestAccess($request, $supportRequest);

        $supportRequest->notes()->create([
            'user_id' => $request->user()->id,
            'note' => trim($request->validated()['note']),
        ]);
        $audit->record('support.note_added', $supportRequest, ['notes']);

        return to_route('admin.support.show', $supportRequest)->with('status', __('ui.admin_content.note_added'));
    }

    public function archive(Request $request, SupportRequest $supportRequest): RedirectResponse
    {
        $this->authorizeRequestAccess($request, $supportRequest);
        $supportRequest->forceFill(['archived_at' => now()])->save();

        return to_route('admin.support.index')->with('status', __('ui.admin_content.archived'));
    }

    private function authorizeRequestAccess(Request $request, SupportRequest $supportRequest): void
    {
        abort_unless(
            $request->user()->can('support.assign') || (int) $supportRequest->assigned_to === (int) $request->user()->id,
            404,
        );
    }
}
