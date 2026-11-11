<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

class DeploymentMigrationController extends Controller
{
    public function create(): View
    {
        abort_unless(is_string(config('portal.migration_key_hash')), 404);
        abort_if($this->hasRun((string) config('portal.migration_key_hash')), 404);

        return view('deployment.migrate');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(is_string(config('portal.migration_key_hash')), 404);
        $validator = Validator::make($request->all(), [
            'key' => ['required', 'string', 'size:64', 'regex:/\A[a-fA-F0-9]{64}\z/'],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $keyHash = hash('sha256', $validator->validated()['key']);
        abort_unless(hash_equals((string) config('portal.migration_key_hash'), $keyHash), 404);

        $lockPath = storage_path('framework/portal-migration.lock');
        $lock = fopen($lockPath, 'c');
        abort_unless(is_resource($lock), 503);

        try {
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                abort(409, __('ui.deployment.migration_running'));
            }

            if ($this->hasRun($keyHash)) {
                abort(404);
            }

            $migrationExit = Artisan::call('migrate', ['--force' => true]);
            abort_if($migrationExit !== 0, 500, __('ui.deployment.migration_failed'));

            $seederExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\RolePermissionSeeder',
                '--force' => true,
            ]);
            abort_if($seederExit !== 0, 500, __('ui.deployment.migration_failed'));

            DB::table('deployment_migration_runs')->insert([
                'key_hash' => $keyHash,
                'ran_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::error('The fixed deployment migration operation failed.', [
                'exception_class' => $exception::class,
            ]);

            throw $exception;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return to_route('admin.initialize')->with('status', __('ui.deployment.migration_complete'));
    }

    private function hasRun(string $keyHash): bool
    {
        return Schema::hasTable('deployment_migration_runs')
            && DB::table('deployment_migration_runs')->where('key_hash', $keyHash)->exists();
    }
}
