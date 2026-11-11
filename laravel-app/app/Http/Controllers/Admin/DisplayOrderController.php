<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderDisplayOrderRequest;
use App\Support\AuditLogger;
use App\Support\DisplayOrderResources;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DisplayOrderController extends Controller
{
    public function update(ReorderDisplayOrderRequest $request, string $scope, AuditLogger $auditLogger): RedirectResponse
    {
        $resource = DisplayOrderResources::get($scope);
        $model = $resource['model'];
        $submittedIds = array_map('intval', $request->validated('ids'));
        abort_if(count($submittedIds) !== count(array_unique($submittedIds)), 422);

        $changed = DB::transaction(function () use ($model, $submittedIds): bool {
            $allIds = $model::query()
                ->orderBy('display_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            // Refuse unexpectedly large lists rather than generate an oversized CASE update.
            abort_if(count($allIds) > 5000, 422, __('ui.admin_content.reorder_list_too_large'));

            $wanted = array_fill_keys($submittedIds, true);
            $positions = [];
            foreach ($allIds as $position => $id) {
                if (isset($wanted[$id])) {
                    $positions[] = $position;
                    unset($wanted[$id]);
                }
            }

            // IDs must be live rows of exactly the selected resource model.
            abort_if($wanted !== [], 422, __('ui.admin_content.reorder_invalid_items'));

            $orderedIds = $allIds;
            foreach ($positions as $index => $position) {
                $orderedIds[$position] = $submittedIds[$index];
            }

            if ($orderedIds === $allIds) {
                return false;
            }

            $case = 'CASE id ';
            foreach ($orderedIds as $position => $id) {
                $case .= 'WHEN '.(int) $id.' THEN '.($position * 10).' ';
            }
            $case .= 'END';

            DB::table((new $model())->getTable())
                ->whereIn('id', $allIds)
                ->update(['display_order' => DB::raw($case)]);

            return true;
        });

        if ($changed) {
            $auditLogger->record($resource['module'].'.reordered', null, ['display_order']);
        }

        return back()->with('status', __('ui.admin_content.order_saved'));
    }
}
