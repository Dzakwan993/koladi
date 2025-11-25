<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserWorkspacesController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PengumumanPerusahaanController;
use App\Http\Controllers\TaskController;
use App\Models\Workspace;

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

// Route::get('/{workspaceId}', [UserController::class, 'workspaceMember']);

// ✅ UBAH: Pindahkan route hak-akses ke dalam middleware auth
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

    // Halaman Workspace
    Route::get('/workspace/{id}', function ($id) {
        $workspace = Workspace::findOrFail($id);
        return view('workspace', compact('workspace'));
    })->name('workspace.show');

    // pengumuman
    Route::middleware(['auth'])->group(function () {
        Route::post('/workspace/{id}/pengumuman/store', [PengumumanController::class, 'store'])
            ->name('pengumuman.store');
        Route::get('/pengumuman/anggota/{workspaceId}', [App\Http\Controllers\PengumumanController::class, 'getAnggota'])->name('pengumuman.anggota');

        Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');

        Route::get('/comments/{pengumuman}', [CommentController::class, 'index'])->name('comments.index');
        Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::post('/comments/reply', [CommentController::class, 'reply'])->name('comments.reply');

        //uploadfile
        Route::post('/upload', [App\Http\Controllers\AttachmentController::class, 'upload'])->name('upload');

        //upload image
        Route::post('/upload-image', [AttachmentController::class, 'uploadImage'])->name('upload.image');

        //untuk edit pengumuman
        Route::get('/pengumuman/{id}/edit-data', [PengumumanController::class, 'getEditData'])->name('pengumuman.edit.data');
        Route::put('/pengumuman/{id}', [PengumumanController::class, 'update'])->name('pengumuman.update');

        Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])
            ->name('pengumuman.destroy');

        Route::get('/workspace/{id}/pengumuman', [PengumumanController::class, 'index'])->name('workspace.pengumuman');
    });

    Route::get('/workspace/{workspaceId}', [UserWorkspacesController::class, 'show'])->name('workspace.show');

    //role workspaces
    Route::get('/api/workspaces/{workspace_id}/members', [UserWorkspacesController::class, 'index'])
        ->name('api.workspace.members');

    // statistik
    Route::get('/statistik', function () {
        return view('statistik');
    })->name('statistik');

    // statistikRuangKerja
    Route::get('/statistikRuangKerja', function () {
        return view('statistikRuangKerja');
    })->name('statistikRuangKerja');

    // Events
    Route::get('/events', function () {
        return response()->json([]);
    })->name('events');

    // kanban-tugas
    Route::get('/kanban-tugas', function () {
        return view('kanban-tugas');
    })->name('kanban-tugas');

    // dokumen-dan-file
    Route::get('/dokumen-dan-file', function () {
        return view('dokumen-dan-file');
    })->name('dokumen-dan-file');

    // ✅ WORKSPACE ROUTES - DIPINDAHKAN KE DALAM AUTH GROUP
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
        // Board Columns
        Route::get('/board-columns/{workspaceId}', [TaskController::class, 'getBoardColumns']);
        Route::post('/board-columns', [TaskController::class, 'createBoardColumn']);
        Route::delete('/board-columns/{columnId}', [TaskController::class, 'deleteBoardColumn']);
        Route::put('/board-columns/positions', [TaskController::class, 'updateColumnPosition']);

        // ✅ Task Assignment Routes
        Route::get('/workspace/{workspaceId}/task-members', [TaskController::class, 'getWorkspaceMembers'])->name('workspace.task-members');
        Route::get('/{taskId}/assignments', [TaskController::class, 'getTaskAssignments'])->name('task.assignments');
        Route::post('/{taskId}/assignments', [TaskController::class, 'manageTaskAssignments'])->name('task.assignments.manage');
        Route::post('/create-with-assignments', [TaskController::class, 'storeWithAssignments'])->name('tasks.create.with.assignments');
        Route::get('/workspace/{workspaceId}/list', [TaskController::class, 'getWorkspaceTasks'])->name('tasks.workspace');


        Route::get('/workspace/{workspaceId}/tasks-with-access', [TaskController::class, 'getWorkspaceTasksWithAccess'])->name('tasks.workspace.with-access');

        // Debug Route
        Route::get('/debug-columns/{workspaceId}', [TaskController::class, 'debugBoardColumns']);


        // ✅ NEW: Label Routes
        Route::get('/workspace/{workspaceId}/labels', [TaskController::class, 'getLabels']);
        Route::get('/colors', [TaskController::class, 'getColors']);
        Route::post('/labels', [TaskController::class, 'createLabel']);
        Route::post('/{taskId}/labels', [TaskController::class, 'manageTaskLabels']);
        Route::get('/{taskId}/labels', [TaskController::class, 'getTaskLabels']);


        // Checklist Routes
        Route::get('/{taskId}/checklists', [TaskController::class, 'getTaskChecklists']);
        Route::post('/checklists', [TaskController::class, 'createChecklist']);
        Route::put('/checklists/{checklistId}', [TaskController::class, 'updateChecklist']);
        Route::delete('/checklists/{checklistId}', [TaskController::class, 'deleteChecklist']);
        Route::put('/checklists/positions/update', [TaskController::class, 'updateChecklistPositions']);


        // Routes untuk attachments
        Route::post('/attachments/upload', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');
        Route::get('/{taskId}/attachments', [TaskController::class, 'getTaskAttachments'])->name('tasks.attachments.get');
        Route::delete('/attachments/{attachmentId}', [TaskController::class, 'deleteAttachment'])->name('tasks.attachments.delete');
        Route::get('/attachments/{attachmentId}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');



        // untuk card kanban kolom
        Route::get('/workspace/{workspaceId}/kanban-tasks', [TaskController::class, 'getKanbanTasks'])->name('tasks.kanban');

        // ✅ Task Detail Routes
        Route::get('/{taskId}/detail', [TaskController::class, 'getTaskDetail'])->name('tasks.detail');
        Route::put('/{taskId}/update', [TaskController::class, 'updateTaskDetail'])->name('tasks.update');

        // ✅ Checklist Routes untuk detail
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
    });

    // ✅ Calendar & Schedule Routes
    Route::get('/jadwal', function () {
        return view('jadwal');
    })->name('jadwal');

    Route::get('/buatJadwal', function () {
        return view('buatJadwal');
    })->name('buatJadwal');

    Route::get('/isiJadwalOnline', function () {
        return view('isiJadwalOnline');
    })->name('isiJadwalOnline');

    Route::get('/isiJadwalOffline', function () {
        return view('isiJadwalOffline');
    })->name('isiJadwalOffline');

    Route::get('/isiJadwalTidakAdaRapat', function () {
        return view('isiJadwalTidakAdaRapat');
    })->name('isiJadwalTidakAdaRapat');

    Route::get('/notulensi', function () {
        return view('notulensi');
    })->name('notulensi');

    // // ✅ Announcement Routes
    // Route::get('/pengumuman', function () {
    //     return view('pengumuman');
    // })->name('pengumuman');

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


    // ✅ Communication Routes
    Route::get('/chat', function () {
        return view('chat');
    })->name('chat');


    Route::get('/mindmap', function () {
        return view('mindmap');
    })->name('mindmap');

    // ✅ Payment Rout
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

    Route::prefix('companies/{company_id}')->middleware('auth')->group(function () {

        Route::get('/pengumuman-perusahaan', [PengumumanPerusahaanController::class, 'index'])
            ->name('pengumuman-perusahaan.index');

        Route::post('/pengumuman-perusahaan', [PengumumanPerusahaanController::class, 'store'])
            ->name('pengumuman-perusahaan.store');

        Route::get('/pengumuman-perusahaan/{id}', [PengumumanPerusahaanController::class, 'show'])
            ->name('pengumuman-perusahaan.show');

        // ✅ PERBAIKAN: Tambahkan {company_id} di route edit
    Route::get('/pengumuman-perusahaan/{id}/edit', [PengumumanPerusahaanController::class, 'getEditData'])
        ->name('pengumuman-perusahaan.edit');

    // ✅ PERBAIKAN: Tambahkan {company_id} di route update
    Route::put('/pengumuman-perusahaan/{id}', [PengumumanPerusahaanController::class, 'update'])
        ->name('pengumuman-perusahaan.update');

        Route::delete('/pengumuman-perusahaan/{id}', [PengumumanPerusahaanController::class, 'destroy'])
    ->name('pengumuman-perusahaan.destroy');

    });
});
