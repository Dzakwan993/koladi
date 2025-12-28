<?php

namespace App\Observers;

use App\Models\UserCompany;
use App\Models\UserWorkspace;
use Illuminate\Support\Facades\Log;

class UserCompanyObserver
{
    /**
     * Handle the UserCompany "updated" event.
     * 
     * Ketika status_active berubah menjadi false,
     * otomatis hapus user dari semua workspace di company tersebut
     */
    public function updated(UserCompany $userCompany)
    {
        // Cek apakah status_active berubah menjadi false
        if ($userCompany->isDirty('status_active') && $userCompany->status_active === false) {
            
            Log::info('🔄 UserCompany status changed to inactive', [
                'user_id' => $userCompany->user_id,
                'company_id' => $userCompany->company_id
            ]);

            // Ambil semua workspace di company ini
            $workspaceIds = \App\Models\Workspace::where('company_id', $userCompany->company_id)
                ->pluck('id');

            // Hitung berapa workspace yang akan dihapus
            $removedCount = UserWorkspace::whereIn('workspace_id', $workspaceIds)
                ->where('user_id', $userCompany->user_id)
                ->count();

            // Hapus user dari semua workspace di company ini
            UserWorkspace::whereIn('workspace_id', $workspaceIds)
                ->where('user_id', $userCompany->user_id)
                ->delete();

            Log::info('✅ User removed from workspaces', [
                'user_id' => $userCompany->user_id,
                'company_id' => $userCompany->company_id,
                'workspaces_removed' => $removedCount
            ]);
        }
    }

    /**
     * Handle the UserCompany "deleted" event.
     * 
     * Ketika user dihapus dari company,
     * otomatis hapus dari semua workspace di company tersebut
     */
    public function deleted(UserCompany $userCompany)
    {
        Log::info('🗑️ UserCompany deleted', [
            'user_id' => $userCompany->user_id,
            'company_id' => $userCompany->company_id
        ]);

        // Ambil semua workspace di company ini
        $workspaceIds = \App\Models\Workspace::where('company_id', $userCompany->company_id)
            ->pluck('id');

        // Hitung berapa workspace yang akan dihapus
        $removedCount = UserWorkspace::whereIn('workspace_id', $workspaceIds)
            ->where('user_id', $userCompany->user_id)
            ->count();

        // Hapus user dari semua workspace
        UserWorkspace::whereIn('workspace_id', $workspaceIds)
            ->where('user_id', $userCompany->user_id)
            ->delete();

        Log::info('✅ User removed from workspaces (via deletion)', [
            'user_id' => $userCompany->user_id,
            'company_id' => $userCompany->company_id,
            'workspaces_removed' => $removedCount
        ]);
    }
}