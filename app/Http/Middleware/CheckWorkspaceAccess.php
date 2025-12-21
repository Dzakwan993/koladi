<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserWorkspace;
use Illuminate\Support\Facades\Auth;

class CheckWorkspaceAccess
{
    public function handle(Request $request, Closure $next)
    {
        $workspaceId = $request->route('workspaceId');
        $user = Auth::user();

        $activeCompanyId = session('active_company_id');
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $isAdmin = in_array(
            $userCompany?->role?->name ?? 'Member',
            ['SuperAdmin', 'Administrator', 'Admin', 'Manager']
        );

        if (!$isAdmin) {
            $hasAccess = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->where('status_active', true)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'Anda tidak memiliki akses ke workspace ini');
            }
        }

        return $next($request);
    }
}
