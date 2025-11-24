<?php

use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Middleware\CheckWorkspaceAccess;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Auth\GoogleController;

// ✅ Route Landing Page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('masuk');
});

// ✅ Authentication Routes
Route::get('/daftar', [AuthController::class, 'showRegister'])->name('daftar');
Route::post('/daftar', [AuthController::class, 'register'])->name('daftar.store');

Route::get('/masuk', [AuthController::class, 'showLogin'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'login'])->name('login');

// ✅ Google OAuth Routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ✅ Invitation Routes (Public)
Route::post('/invite/send', [InvitationController::class, 'send'])->name('invite.send');
Route::get('/invite/accept/{token}', [InvitationController::class, 'accept'])->name('invite.accept');

// ✅ Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // ✅ Dashboard & Company Routes
    Route::get('/dashboard', [CompanyController::class, 'dashboard'])->name('dashboard');
    Route::get('/member-removed', [CompanyController::class, 'memberRemoved'])->name('member.removed');

    Route::get('/dashboard-awal', function () {
        return view('dashboard-awal');
    })->name('dashboard-awal');

    Route::get('/dashboard-awal-kerja', function () {
        return view('dashboard-awal-kerja');
    })->name('dashboard-awal-kerja');

    // ✅ Company Management
    Route::get('/buat-perusahaan', [CompanyController::class, 'create'])->name('buat-perusahaan.create');
    Route::post('/buat-perusahaan', [CompanyController::class, 'store'])->name('buat-perusahaan');
    Route::get('/company/switch/{company}', [CompanyController::class, 'switchCompany'])->name('company.switch');
    Route::put('/company/{id}/update', [CompanyController::class, 'update'])->name('company.update');
    Route::delete('/company/{id}/delete', [CompanyController::class, 'destroy'])->name('company.destroy');

    // ✅ Member Management
    Route::get('/tambah-anggota', [CompanyController::class, 'showMembers'])->name('tambah-anggota');
    Route::delete('/members/{id}/delete', [CompanyController::class, 'deleteMember'])->name('member.delete');
    Route::delete('/invitation/{id}/delete', [InvitationController::class, 'delete'])->name('invitation.delete');

    // ✅ Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    // ✅ Workspace Routes
    Route::get('/workspace', function () {
        return view('workspace');
    })->name('workspace');

    Route::get('/kelola-workspace', [WorkspaceController::class, 'index'])->name('kelola-workspace');
    Route::post('/workspace', [WorkspaceController::class, 'store'])->name('workspace.store');
    Route::put('/workspace/{id}', [WorkspaceController::class, 'update'])->name('workspace.update');
    Route::delete('/workspace/{id}', [WorkspaceController::class, 'destroy'])->name('workspace.destroy');
    Route::post('/workspace/{workspaceId}/members', [WorkspaceController::class, 'manageMembers'])->name('workspace.manage-members');
    Route::get('/workspace/{workspaceId}/members', [WorkspaceController::class, 'getMembers'])->name('workspace.get-members');
    Route::get('/workspace-available-users', [WorkspaceController::class, 'getAvailableUsers'])->name('workspace.available-users');

    // ✅ Workspace Detail Route
    Route::get('/workspace/{workspace}', [WorkspaceController::class, 'show'])
        ->name('workspace.detail');

    // ✅ Task & Kanban Routes
    Route::get('/kanban-tugas/{workspace}', [TaskController::class, 'showKanban'])->name('kanban-tugas');

    // Task API Routes
    Route::prefix('tasks')->group(function () {
        Route::get('/board-columns/{workspaceId}', [TaskController::class, 'getBoardColumns']);
        Route::post('/board-columns', [TaskController::class, 'createBoardColumn']);
        Route::delete('/board-columns/{columnId}', [TaskController::class, 'deleteBoardColumn']);
        Route::put('/board-columns/positions', [TaskController::class, 'updateColumnPosition']);

        Route::get('/workspace/{workspaceId}/task-members', [TaskController::class, 'getWorkspaceMembers'])->name('workspace.task-members');
        Route::get('/{taskId}/assignments', [TaskController::class, 'getTaskAssignments'])->name('task.assignments');
        Route::post('/{taskId}/assignments', [TaskController::class, 'manageTaskAssignments'])->name('task.assignments.manage');
        Route::post('/create-with-assignments', [TaskController::class, 'storeWithAssignments'])->name('tasks.create.with.assignments');
        Route::get('/workspace/{workspaceId}/list', [TaskController::class, 'getWorkspaceTasks'])->name('tasks.workspace');
        Route::get('/workspace/{workspaceId}/tasks-with-access', [TaskController::class, 'getWorkspaceTasksWithAccess'])->name('tasks.workspace.with-access');
        Route::get('/debug-columns/{workspaceId}', [TaskController::class, 'debugBoardColumns']);

        Route::get('/workspace/{workspaceId}/labels', [TaskController::class, 'getLabels']);
        Route::get('/colors', [TaskController::class, 'getColors']);
        Route::post('/labels', [TaskController::class, 'createLabel']);
        Route::post('/{taskId}/labels', [TaskController::class, 'manageTaskLabels']);
        Route::get('/{taskId}/labels', [TaskController::class, 'getTaskLabels']);

        Route::get('/{taskId}/checklists', [TaskController::class, 'getTaskChecklists']);
        Route::post('/checklists', [TaskController::class, 'createChecklist']);
        Route::put('/checklists/{checklistId}', [TaskController::class, 'updateChecklist']);
        Route::delete('/checklists/{checklistId}', [TaskController::class, 'deleteChecklist']);
        Route::put('/checklists/positions/update', [TaskController::class, 'updateChecklistPositions']);

        Route::post('/attachments/upload', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');
        Route::get('/{taskId}/attachments', [TaskController::class, 'getTaskAttachments'])->name('tasks.attachments.get');
        Route::delete('/attachments/{attachmentId}', [TaskController::class, 'deleteAttachment'])->name('tasks.attachments.delete');
        Route::get('/attachments/{attachmentId}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');

        Route::get('/workspace/{workspaceId}/kanban-tasks', [TaskController::class, 'getKanbanTasks'])->name('tasks.kanban');

        Route::get('/{taskId}/detail', [TaskController::class, 'getTaskDetail'])->name('tasks.detail');
        Route::put('/{taskId}/update', [TaskController::class, 'updateTaskDetail'])->name('tasks.update');

        Route::post('/{taskId}/checklists', [TaskController::class, 'createChecklistForTask'])->name('tasks.checklists.create');
        Route::put('/checklists/{checklistId}', [TaskController::class, 'updateChecklistItem'])->name('tasks.checklists.update');
        Route::delete('/checklists/{checklistId}', [TaskController::class, 'deleteChecklist'])->name('tasks.checklists.delete');

        Route::post('/{taskId}/attachments', [TaskController::class, 'updateTaskAttachments'])->name('tasks.attachments.update');

        Route::put('/{taskId}/update-title', [TaskController::class, 'updateTaskTitle'])->name('tasks.update-title');
        Route::post('/{taskId}/attachments/add', [TaskController::class, 'addAttachmentToTask'])->name('tasks.attachments.add');
        Route::put('/tasks/{taskId}/labels/update', [TaskController::class, 'updateTaskLabels'])->name('tasks.labels.update');
        Route::put('/{taskId}/labels/update', [TaskController::class, 'updateTaskLabels'])->name('tasks.labels.update');

        Route::post('/update-column', [TaskController::class, 'updateTaskColumn'])->name('tasks.update-column');
        Route::post('/tasks/update-column', [TaskController::class, 'updateTaskColumn'])->name('tasks.update-column');

       // Comments
    Route::get('/{taskId}/comments', [TaskController::class, 'getTaskComments'])->name('tasks.comments.get');
    Route::post('/{taskId}/comments', [TaskController::class, 'storeTaskComment'])->name('tasks.comments.store');

    // Upload file/image for comment (used by custom toolbar buttons)
    // You can use a single endpoint for both: uploadCommentFile
    Route::post('/comments/upload', [TaskController::class, 'uploadCommentFile'])->name('tasks.comments.upload');

        Route::get('/workspace/{workspaceId}/timeline', [TaskController::class, 'getTimelineData'])->name('tasks.timeline');
        Route::get('/tasks/workspace/{workspaceId}/timeline', [TaskController::class, 'getTimelineData'])->name('tasks.timeline');
    });

    // ✅ JADWAL WORKSPACE (dengan prefix workspace/{workspaceId})
    Route::prefix('workspace/{workspaceId}')->group(function () {
        // Halaman utama jadwal workspace
        Route::get('/jadwal', [CalendarController::class, 'index'])->name('jadwal');

        // API untuk get events
        Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');

        // Create routes (harus di atas {id})
        Route::get('/jadwal/buat', [CalendarController::class, 'create'])->name('buatJadwal');
        Route::post('/jadwal/buat', [CalendarController::class, 'store'])->name('calendar.store');

        // Update participant status
        Route::post('/jadwal/{id}/participant-status', [CalendarController::class, 'updateParticipantStatus'])
            ->name('calendar.participant.status');

        // Edit & Update
        Route::get('/jadwal/{id}/edit', [CalendarController::class, 'edit'])->name('calendar.edit');
        Route::put('/jadwal/{id}', [CalendarController::class, 'update'])->name('calendar.update');

        // Delete
        Route::delete('/jadwal/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');

        // ✅ Notulensi Workspace (pindah ke dalam group)
        Route::get('/notulensi', [CalendarController::class, 'notulensi'])->name('notulensi');

        // Show (harus paling bawah)
        Route::get('/jadwal/{id}', [CalendarController::class, 'show'])->name('calendar.show');
    });

    // ✅ JADWAL UMUM (Company Level - tanpa workspace)
    Route::prefix('jadwal-umum')->group(function () {
        Route::get('/', [CalendarController::class, 'companyIndex'])->name('jadwal-umum');
        Route::get('/events', [CalendarController::class, 'getCompanyEvents'])->name('jadwal-umum.events');
        Route::get('/buat', [CalendarController::class, 'companyCreate'])->name('jadwal-umum.buat');
        Route::post('/buat', [CalendarController::class, 'companyStore'])->name('jadwal-umum.store');
        Route::get('/{id}/edit', [CalendarController::class, 'companyEdit'])->name('jadwal-umum.edit');
        Route::put('/{id}', [CalendarController::class, 'companyUpdate'])->name('jadwal-umum.update');
        Route::delete('/{id}', [CalendarController::class, 'companyDestroy'])->name('jadwal-umum.destroy');
        Route::get('/{id}', [CalendarController::class, 'companyShow'])->name('jadwal-umum.show');
    });

    // ✅ Notulensi Umum (di luar prefix jadwal-umum)
    Route::get('/notulensi-umum', [CalendarController::class, 'companyNotulensi'])->name('notulensi-umum');

    // ✅ Comments Routes
    Route::get('/comments/{commentableId}', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // ✅ Upload routes untuk attachments
    Route::post('/upload', [App\Http\Controllers\AttachmentController::class, 'upload'])->name('upload.file');
    Route::post('/upload-image', [App\Http\Controllers\AttachmentController::class, 'uploadImage'])->name('upload.image');

    // ✅ Announcement Routes
    Route::get('/pengumuman', function () {
        return view('pengumuman');
    })->name('pengumuman');

    Route::get('/isiPengunguman', function () {
        return view('isiPengunguman');
    })->name('isiPengunguman');

    // ✅ Statistics Routes
    Route::get('/statistik', function () {
        return view('statistik');
    })->name('statistik');

    Route::get('/statistikRuangKerja', function () {
        return view('statistikRuangKerja');
    })->name('statistikRuangKerja');

    // ✅ Documents & Files
    Route::get('/dokumen-dan-file', function () {
        return view('dokumen-dan-file');
    })->name('dokumen-dan-file');

    // ✅ Leave Management
    Route::get('/cutikaryawan', function () {
        return view('cutikaryawan');
    })->name('cutikaryawan');

    Route::get('/cutimanajer', function () {
        return view('cutimanajer');
    })->name('cutimanajer');

    // ✅ Communication Routes
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');

    // ✅ Insight & Mindmap Routes
    Route::get('/insight', function () {
        return view('insight');
    })->name('insight');

    Route::get('/isi-insight', function () {
        return view('isi-insight');
    })->name('isi-insight');

    Route::get('/mindmap', function () {
        return view('mindmap');
    })->name('mindmap');

    // ✅ Payment Route
    Route::get('/pembayaran', function () {
        return view('pembayaran');
    })->name('pembayaran');

    // ✅ Role Management Routes
    Route::get('/hak-akses', [UserController::class, 'hakAkses'])->name('hakAkses');
    Route::post('/update-user-roles', [UserController::class, 'updateUserRoles'])->name('user.updateRoles');
    Route::post('/workspace/{workspaceId}/update-user-roles', [WorkspaceController::class, 'updateUserRoles'])->name('workspace.updateUserRoles');
    Route::get('/workspace/{workspaceId}/user-role', [UserController::class, 'getWorkspaceUserRole']);

    // ✅ Events API
    Route::get('/events', function () {
        return response()->json([]);
    })->name('events');

    // ✅ Logout
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
});
