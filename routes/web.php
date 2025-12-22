<?php

use App\Models\Company;
use App\Models\Workspace;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\MindmapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Middleware\CheckWorkspaceAccess;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CompanyChatController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DocumentCommentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanyDokumenController;
use App\Http\Controllers\CompanyDocumentCommentController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NotificationController;

// 🔥 Broadcasting Routes
Broadcast::routes(['middleware' => ['web', 'auth']]);

Route::get('/', [LandingPageController::class, 'index'])
    ->name('landingpage');

Route::post('/feedback', [FeedbackController::class, 'store'])
    ->name('feedback.store');

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

// ============================================
// 🔐 ROUTES OTP & PASSWORD RESET
// ============================================
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verify-otp.show');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp.verify');
Route::post('/verify-otp/resend', [AuthController::class, 'resendOtp'])->name('verify-otp.resend');
Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/lupa-password', [AuthController::class, 'sendResetOtp'])->name('forgot-password.send');
Route::get('/reset-password/verify', [AuthController::class, 'showResetPasswordVerifyOtp'])->name('reset-password.verify-otp');
Route::post('/reset-password/verify', [AuthController::class, 'verifyResetOtp'])->name('reset-password.verify-otp-submit');
Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password.form');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.submit');


// 🔥 ADMIN SISTEM ROUTES
Route::middleware(['auth', 'check.system.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/companies/{id}', [AdminController::class, 'showCompany'])->name('companies.show');
    Route::post('/companies/{id}/toggle-status', [AdminController::class, 'toggleCompanyStatus'])->name('companies.toggle-status');

    Route::post('/pembayaran/{id}/verify', [AdminController::class, 'verifyPayment'])->name('pembayaran.verify');
    // Edit Paket & Addon Routes
    Route::post('/plans/{id}/update', [AdminController::class, 'updatePlan'])->name('plans.update');
    Route::post('/addons/{id}/update', [AdminController::class, 'updateAddon'])->name('addons.update');
    // Di dalam: Route::middleware(['auth', 'check.system.admin'])->prefix('admin')->name('admin.')->group
    Route::delete('/feedbacks/{id}', [AdminController::class, 'deleteFeedback'])->name('feedbacks.delete');
    // Export Excel Route
    Route::get('/companies/export/excel', [AdminController::class, 'exportCompanies'])->name('companies.export');
});


// Webhook Midtrans (tanpa auth)
Route::post('/midtrans/callback', [SubscriptionController::class, 'callback'])->name('midtrans.callback');

// ============================================
// 🔐 AUTHENTICATED ROUTES
// ============================================
Route::middleware(['auth'])->group(function () {

    // ============================================
    // ✅ ROUTES TANPA CheckSubscription (Always Accessible)
    // ============================================

    // Dashboard (Bisa diakses, tapi kasih warning kalau expired)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/mark-onboarding-seen', [DashboardController::class, 'markOnboardingSeen'])->name('mark-onboarding-seen');
    Route::post('/update-onboarding-step', [DashboardController::class, 'updateOnboardingStep'])->name('update-onboarding-step');
    Route::post('/complete-onboarding', [DashboardController::class, 'completeOnboarding'])
        ->name('complete-onboarding');

    // Company Creation Routes
    Route::get('/buat-perusahaan', [CompanyController::class, 'create'])->name('buat-perusahaan.create');
    Route::post('/buat-perusahaan', [CompanyController::class, 'store'])->name('buat-perusahaan');

    // Payment Routes
    Route::get('/pembayaran', [SubscriptionController::class, 'index'])->name('pembayaran');
    Route::get('/api/plans', [SubscriptionController::class, 'getPlans'])->name('api.plans');
    Route::post('/subscription/create', [SubscriptionController::class, 'createSubscription'])->name('subscription.create');
    Route::get('/api/subscription/status', [SubscriptionController::class, 'checkTrialStatus'])->name('api.subscription.status');

    // 🔥 BARU - Manual Payment Routes
    Route::post('/subscription/upload-proof', [SubscriptionController::class, 'uploadProof'])->name('subscription.upload-proof');

    // 🔥 BARU - Admin Payment Verification (dengan middleware admin)
    Route::middleware(['check.system.admin'])->group(function () {
        Route::post('/subscription/verify-payment', [SubscriptionController::class, 'verifyManualPayment'])->name('subscription.verify-payment');
    });

    // ========================================
    // 🔥 NEW: API ROUTES UNTUK DOKUMEN
    // (Letakkan DI AWAL setelah Route::middleware(['auth'])->group)
    // ========================================

    // API untuk get user workspaces
    Route::get('/api/user/workspaces', [DokumenController::class, 'getUserWorkspaces']);

    // Route untuk move documents
    Route::post('/documents/move', [DokumenController::class, 'moveDocuments']);

    // ✅ BARU - Get subfolders dari folder tertentu
    Route::get('/api/folders/{folder}/subfolders', [DokumenController::class, 'getFolderSubfolders']);

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    // Access Blocked Page
    Route::get('/akses-terblokir', [SubscriptionController::class, 'showAccessBlocked'])->name('access.blocked');

    // Logout
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

    // Get modal full data
    Route::get('/statistik/modal-data', [StatistikController::class, 'getModalData']);

    // ============================================
    // 🔒 ROUTES DENGAN CheckSubscription
    // ============================================
    Route::middleware(['check.subscription'])->group(function () {

        // Router Statistik
        Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');

        // API Routes untuk AJAX (tambahan baru)
        Route::prefix('api/statistik')->name('api.statistik.')->group(function () {

            // Get data workspace (ketika ganti workspace)
            Route::get('/workspace/{workspaceId}', [StatistikController::class, 'getWorkspaceData'])
                ->name('workspace');

            // Get data member (ketika klik member tertentu)
            Route::get('/member/{memberId}', [StatistikController::class, 'getMemberData'])
                ->name('member');

            // Get tasks by filter (ketika ganti filter status)
            Route::get('/tasks', [StatistikController::class, 'getTasksByFilter'])
                ->name('tasks');

            // Get data by periode (ketika ganti periode)
            Route::get('/periode', [StatistikController::class, 'getPeriodeData'])
                ->name('periode');

            Route::get('/modal-data', [StatistikController::class, 'getModalData']); // ✅ TAMBAH INI
        });

        // ✅ DSS (Decision Support System) Routes
        Route::prefix('statistik')->group(function () {

            // Get DSS suggestions
            Route::get('/suggestions', [StatistikController::class, 'getSuggestions']);

            // Get full modal data
            Route::get('/modal-data', [StatistikController::class, 'getModalData']);

            // Refresh snapshot (force recalculate)
            Route::post('/refresh-snapshot', [StatistikController::class, 'refreshSnapshot']);
        });


        // Tambahkan di routes/web.php dalam group middleware 'auth' dan 'check.subscription'
        // Dashboard - All Events (Company + Workspace)
        Route::get('/dashboard/all-events', [DashboardController::class, 'getAllEvents'])
            ->name('dashboard.all-events');

        // Dashboard - Schedules by Date
        Route::get('/dashboard/schedules/{date}', [DashboardController::class, 'getSchedulesByDate'])
            ->name('dashboard.schedules.by-date');

        // Dashboard Awal
        Route::get('/member-removed', [CompanyController::class, 'memberRemoved'])->name('member.removed');
        Route::get('/dashboard-awal', function () {
            return view('dashboard-awal');
        })->name('dashboard-awal');
        Route::get('/dashboard-awal-kerja', function () {
            return view('dashboard-awal-kerja');
        })->name('dashboard-awal-kerja');

        // Company Management (Update & Delete)
        Route::get('/company/switch/{company}', [CompanyController::class, 'switchCompany'])->name('company.switch');
        Route::put('/company/{id}/update', [CompanyController::class, 'update'])->name('company.update');
        Route::delete('/company/{id}/delete', [CompanyController::class, 'destroy'])->name('company.destroy');

        // Member Management
        Route::get('/tambah-anggota', [CompanyController::class, 'showMembers'])->name('tambah-anggota');
        Route::delete('/members/{id}/delete', [CompanyController::class, 'deleteMember'])->name('member.delete');
        Route::delete('/invitation/{id}/delete', [InvitationController::class, 'delete'])->name('invitation.delete');

        // ========================================
        // 🔥 WORKSPACE ROUTES
        // ========================================
        Route::get('/kelola-workspace', [WorkspaceController::class, 'index'])->name('kelola-workspace');
        Route::post('/workspace', [WorkspaceController::class, 'store'])->name('workspace.store');
        Route::put('/workspace/{id}', [WorkspaceController::class, 'update'])->name('workspace.update');
        Route::delete('/workspace/{id}', [WorkspaceController::class, 'destroy'])->name('workspace.destroy');
        Route::post('/workspace/{workspaceId}/members', [WorkspaceController::class, 'manageMembers'])->name('workspace.manage-members');
        Route::get('/workspace/{workspaceId}/members', [WorkspaceController::class, 'getMembers'])->name('workspace.get-members');
        Route::get('/workspace-available-users', [WorkspaceController::class, 'getAvailableUsers'])->name('workspace.available-users');

        Route::get('/workspace', function () {
            $userId = Auth::id();
            $activeCompanyId = session('active_company_id');
            $currentWorkspaceId = session('current_workspace_id');

            if ($currentWorkspaceId) {
                $workspace = Workspace::find($currentWorkspaceId);
                if ($workspace) {
                    return view('workspace', ['workspace' => $workspace]);
                }
            }

            $workspace = Workspace::where('company_id', $activeCompanyId)
                ->whereHas('users', fn($q) => $q->where('users.id', $userId))
                ->first();

            if (!$workspace) {
                return redirect()->route('kelola-workspace')
                    ->with('error', 'Silakan pilih atau buat workspace terlebih dahulu');
            }

            return view('workspace', ['workspace' => $workspace]);
        })->name('workspace');

        Route::get('/workspace/{workspace}', [WorkspaceController::class, 'show'])
            ->name('workspace.show');

        // ✅ Alias untuk backward compatibility (jika diperlukan)
        Route::redirect('/workspace-detail/{workspace}', '/workspace/{workspace}')
            ->name('workspace.detail');


        // ========================================
        // 🔥 CHAT ROUTES
        // ========================================
        Route::get('/company/{company}/chat', [CompanyChatController::class, 'index'])->name('company.chat');
        Route::get('/workspace/{workspace}/chat', [ChatController::class, 'index'])->name('chat');

        Route::prefix('api')->name('api.')->group(function () {
            // Workspace Chat
            Route::prefix('workspace')->group(function () {
                Route::get('/{workspaceId}/chat-data', [ChatController::class, 'getChatData']);
            });

            // General Chat
            Route::prefix('chat')->group(function () {
                Route::get('/{conversationId}/messages', [ChatController::class, 'showMessages'])->name('chat.messages');
                Route::post('/send', [ChatController::class, 'store'])->name('chat.store');
                Route::post('/create', [ChatController::class, 'createConversation'])->name('chat.create');
                Route::put('/message/{message}', [ChatController::class, 'editMessage']);
                Route::delete('/message/{message}', [ChatController::class, 'deleteMessage']);
                Route::post('/{conversationId}/mark-as-read', [ChatController::class, 'markAsRead'])->name('chat.markAsRead');
            });

            // Company Chat
            Route::prefix('company')->group(function () {
                Route::get('/{companyId}/chat-data', [CompanyChatController::class, 'getChatData']);
                Route::get('/chat/{conversationId}/messages', [CompanyChatController::class, 'showMessages']);
                Route::post('/chat/send', [CompanyChatController::class, 'store']);
                Route::put('/chat/message/{message}', [CompanyChatController::class, 'editMessage']);
                Route::delete('/chat/message/{message}', [CompanyChatController::class, 'deleteMessage']);
                Route::post('/chat/create', [CompanyChatController::class, 'createConversation']);
                Route::post('/chat/{conversationId}/mark-as-read', [CompanyChatController::class, 'markAsRead']);
            });
        });

        // ========================================
        // 🔥 TASK & KANBAN ROUTES
        // ========================================
        Route::get('/kanban-tugas/{workspace}', [TaskController::class, 'showKanban'])->name('kanban-tugas');

        Route::prefix('tasks')->group(function () {
            // Board & Columns
            Route::get('/board-columns/{workspaceId}', [TaskController::class, 'getBoardColumns']);
            Route::post('/board-columns', [TaskController::class, 'createBoardColumn']);
            Route::delete('/board-columns/{columnId}', [TaskController::class, 'deleteBoardColumn']);
            Route::put('/board-columns/positions', [TaskController::class, 'updateColumnPosition']);
            Route::get('/debug-columns/{workspaceId}', [TaskController::class, 'debugBoardColumns']);

            // Task Management
            Route::get('/workspace/{workspaceId}/task-members', [TaskController::class, 'getWorkspaceMembers'])->name('workspace.task-members');
            Route::get('/{taskId}/assignments', [TaskController::class, 'getTaskAssignments'])->name('task.assignments');
            Route::post('/{taskId}/assignments', [TaskController::class, 'manageTaskAssignments'])->name('task.assignments.manage');
            Route::post('/create-with-assignments', [TaskController::class, 'storeWithAssignments'])->name('tasks.create.with.assignments');
            Route::get('/workspace/{workspaceId}/list', [TaskController::class, 'getWorkspaceTasks'])->name('tasks.workspace');
            Route::get('/workspace/{workspaceId}/tasks-with-access', [TaskController::class, 'getWorkspaceTasksWithAccess'])->name('tasks.workspace.with-access');
            Route::get('/workspace/{workspaceId}/kanban-tasks', [TaskController::class, 'getKanbanTasks'])->name('tasks.kanban');
            Route::get('/{taskId}/detail', [TaskController::class, 'getTaskDetail'])->name('tasks.detail');
            Route::put('/{taskId}/update', [TaskController::class, 'updateTaskDetail'])->name('tasks.update');
            Route::put('/{taskId}/update-title', [TaskController::class, 'updateTaskTitle'])->name('tasks.update-title');
            Route::post('/update-column', [TaskController::class, 'updateTaskColumn'])->name('tasks.update-column');

            // Labels
            Route::get('/workspace/{workspaceId}/labels', [TaskController::class, 'getLabels']);
            Route::get('/colors', [TaskController::class, 'getColors']);
            Route::post('/labels', [TaskController::class, 'createLabel']);
            Route::post('/{taskId}/labels', [TaskController::class, 'manageTaskLabels']);
            Route::get('/{taskId}/labels', [TaskController::class, 'getTaskLabels']);
            Route::put('/{taskId}/labels/update', [TaskController::class, 'updateTaskLabels'])->name('tasks.labels.update');

            // Checklists
            Route::get('/{taskId}/checklists', [TaskController::class, 'getTaskChecklists']);
            Route::post('/checklists', [TaskController::class, 'createChecklist']);
            Route::post('/{taskId}/checklists', [TaskController::class, 'createChecklistForTask'])->name('tasks.checklists.create');
            Route::put('/checklists/{checklistId}', [TaskController::class, 'updateChecklist'])->name('tasks.checklists.update');
            Route::delete('/checklists/{checklistId}', [TaskController::class, 'deleteChecklist'])->name('tasks.checklists.delete');
            Route::put('/checklists/positions/update', [TaskController::class, 'updateChecklistPositions']);

            //yang baru
            Route::get('/tasks/{taskId}/checklists/all', [TaskController::class, 'getAllChecklists'])->name('tasks.checklists.all');
            // routes/web.php
            Route::get('/tasks/{workspace}/cleanup-phases', [TaskController::class, 'cleanupPhases'])
                ->middleware('auth')
                ->name('tasks.cleanup-phases');


            // routes/web.php - untuk testing
            Route::get('/tasks/{workspace}/debug-phases', [TaskController::class, 'debugPhases'])
                ->middleware('auth')
                ->name('tasks.debug-phases');

            // Attachments
            Route::post('/attachments/upload', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');
            Route::get('/{taskId}/attachments', [TaskController::class, 'getTaskAttachments'])->name('tasks.attachments.get');
            Route::post('/{taskId}/attachments', [TaskController::class, 'updateTaskAttachments'])->name('tasks.attachments.update');
            Route::post('/{taskId}/attachments/add', [TaskController::class, 'addAttachmentToTask'])->name('tasks.attachments.add');
            Route::delete('/attachments/{attachmentId}', [TaskController::class, 'deleteAttachment'])->name('tasks.attachments.delete');
            Route::get('/attachments/{attachmentId}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');

            // Comments
            Route::get('/{taskId}/comments', [TaskController::class, 'getTaskComments'])->name('tasks.comments.get');
            Route::post('/{taskId}/comments', [TaskController::class, 'storeTaskComment'])->name('tasks.comments.store');
            Route::post('/comments/upload', [TaskController::class, 'uploadCommentFile'])->name('tasks.comments.upload');

            // Timeline
            Route::get('/workspace/{workspaceId}/timeline', [TaskController::class, 'getTimelineData'])->name('tasks.timeline');

            // Task deletion routes
            Route::delete('/{taskId}', [TaskController::class, 'deleteTask'])->name('tasks.delete');
            Route::delete('/{taskId}/force', [TaskController::class, 'forceDeleteTask'])->name('tasks.force-delete');
            Route::post('/{taskId}/restore', [TaskController::class, 'restoreTask'])->name('tasks.restore');

            Route::delete('/custom-columns/{columnId}', [TaskController::class, 'deleteCustomColumn'])
                ->name('tasks.custom-columns.delete');
        });

        Route::post('/calendar/event/{eventId}/attendance', [CalendarController::class, 'recordAttendance'])
            ->name('calendar.attendance');
        Route::get('/calendar/event/{eventId}/attendance-stats', [CalendarController::class, 'getAttendanceStats'])
            ->name('calendar.attendance.stats');
        // ========================================
        // 🔥 CALENDAR & SCHEDULE ROUTES
        // ========================================
        Route::prefix('workspace/{workspaceId}')->group(function () {
            Route::get('/jadwal', [CalendarController::class, 'index'])->name('jadwal');
            Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');
            Route::get('/jadwal/buat', [CalendarController::class, 'create'])->name('buatJadwal');
            Route::post('/jadwal/buat', [CalendarController::class, 'store'])->name('calendar.store');
            Route::post('/jadwal/{id}/participant-status', [CalendarController::class, 'updateParticipantStatus'])->name('calendar.participant.status');
            Route::get('/jadwal/{id}/edit', [CalendarController::class, 'edit'])->name('calendar.edit');
            Route::put('/jadwal/{id}', [CalendarController::class, 'update'])->name('calendar.update');
            Route::delete('/jadwal/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
            Route::get('/notulensi', [CalendarController::class, 'notulensi'])->name('notulensi');
            Route::get('/jadwal/{id}', [CalendarController::class, 'show'])->name('calendar.show');
            Route::post('/calendar/check-conflicts', [CalendarController::class, 'checkConflicts'])
                ->name('calendar.check-conflicts');
        });

        Route::prefix('jadwal-umum')->group(function () {
            Route::get('/', [CalendarController::class, 'companyIndex'])->name('jadwal-umum');
            Route::get('/events', [CalendarController::class, 'getCompanyEvents'])->name('jadwal-umum.events');
            Route::get('/buat', [CalendarController::class, 'companyCreate'])->name('jadwal-umum.buat');
            Route::post('/buat', [CalendarController::class, 'companyStore'])->name('jadwal-umum.store');
            Route::get('/{id}/edit', [CalendarController::class, 'companyEdit'])->name('jadwal-umum.edit');
            Route::put('/{id}', [CalendarController::class, 'companyUpdate'])->name('jadwal-umum.update');
            Route::delete('/{id}', [CalendarController::class, 'companyDestroy'])->name('jadwal-umum.destroy');
            Route::get('/{id}', [CalendarController::class, 'companyShow'])->name('jadwal-umum.show');
            Route::post('/check-conflicts', [CalendarController::class, 'checkCompanyConflicts'])
                ->name('jadwal-umum.check-conflicts');
        });
        Route::get('/notulensi-umum', [CalendarController::class, 'companyNotulensi'])->name('notulensi-umum');

        // ========================================
        // 🔥 DOCUMENTS & FILES ROUTES
        // ========================================
        Route::get('/dokumen-dan-file/{workspace}', [DokumenController::class, 'index'])->name('dokumen-dan-file');
        Route::post('/folder', [DokumenController::class, 'store'])->name('folder.store');
        Route::post('/file', [DokumenController::class, 'storeFile'])->name('file.store');
        Route::post('/folders/{id}/update', [DokumenController::class, 'updateFolder'])->name('folder.update');
        Route::put('/files/{id}/update', [DokumenController::class, 'updateFile'])->name('files.update');
        Route::delete('/files/{id}/delete', [DokumenController::class, 'destroy'])->name('files.destroy');
        Route::delete('/folders/{folder}/delete', [DokumenController::class, 'destroyFolder'])->name('folders.destroy');
        Route::post('/documents/delete-multiple', [DokumenController::class, 'deleteMultiple'])->name('documents.deleteMultiple');
        Route::get('/workspaces/{workspace}/members', [DokumenController::class, 'getWorkspaceMembers'])->name('workspace.members');
        Route::post('/documents/recipients', [DokumenController::class, 'recipientsStore'])->name('document.recipients.store');
        Route::get('/documents/{document}/recipients', [DokumenController::class, 'getRecipients']);

        // Document Comments
        Route::prefix('documents')->group(function () {
            Route::get('/{file}/comments', [DocumentCommentController::class, 'index']);
            Route::post('/comments', [DocumentCommentController::class, 'store'])->name('document.comments.store');
        });

        // ========================================
        // 🔥 COMPANY DOCUMENTS (NEW) - TAMBAHKAN INI
        // ========================================
        Route::prefix('company-documents')->name('company-documents.')->group(function () {
            Route::get('/', [CompanyDokumenController::class, 'index'])->name('index');
            Route::post('/folder', [CompanyDokumenController::class, 'storeFolder'])->name('folder.store');
            Route::post('/file', [CompanyDokumenController::class, 'storeFile'])->name('file.store');
            Route::post('/folders/{id}/update', [CompanyDokumenController::class, 'updateFolder'])->name('folder.update');
            Route::put('/files/{id}/update', [CompanyDokumenController::class, 'updateFile'])->name('files.update');
            Route::delete('/files/{id}/delete', [CompanyDokumenController::class, 'destroyFile'])->name('files.destroy');
            // ✅ BENAR - tanpa /company-documents lagi
            Route::post('/delete-multiple', [CompanyDokumenController::class, 'deleteMultiple'])->name('deleteMultiple');
            Route::delete('/folders/{folder}/delete', [CompanyDokumenController::class, 'destroyFolder'])->name('folders.destroy');
            Route::get('/members', [CompanyDokumenController::class, 'getCompanyMembers'])->name('members');
            Route::post('/recipients', [CompanyDokumenController::class, 'recipientsStore'])->name('recipients.store');
            Route::get('/{document}/recipients', [CompanyDokumenController::class, 'getRecipients'])->name('recipients.get');

            // ✅ Route untuk move documents dari company ke workspace
            Route::post('/move', [CompanyDokumenController::class, 'moveDocuments'])->name('move');
            Route::get('/workspaces', [CompanyDokumenController::class, 'getAvailableWorkspaces'])->name('workspaces');
        });

        // Comments untuk company documents
        Route::prefix('company-documents')->group(function () {
            Route::get('/{file}/comments', [CompanyDocumentCommentController::class, 'index']);
            Route::post('/comments', [CompanyDocumentCommentController::class, 'store'])->name('company.document.comments.store');
        });

        // ========================================
        // 🔥 COMMENTS ROUTES (UNIVERSAL)
        // ========================================
        Route::get('/comments/{commentableId}', [CommentController::class, 'index']);
        Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::put('/comments/{id}', [CommentController::class, 'update']);
        Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

        // ========================================
        // 🔥 UPLOAD ROUTES (UNIVERSAL)
        // ========================================
        Route::post('/upload', [AttachmentController::class, 'upload'])->name('upload.file');
        Route::post('/upload-image', [AttachmentController::class, 'uploadImage'])->name('upload.image');
        Route::get('/attachments', [AttachmentController::class, 'index'])->name('attachments.index');
        Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

        // ========================================
        // 🔥 ANNOUNCEMENT ROUTES (PENGUMUMAN)
        // ========================================
        // Pengumuman per Workspace
        Route::prefix('workspace/{workspace}')->group(function () {
            Route::get('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'index'])
                ->name('workspace.pengumuman');
            Route::post('/pengumuman/store', [\App\Http\Controllers\PengumumanController::class, 'store'])
                ->name('pengumuman.store');
            Route::get('/pengumuman/anggota/{workspaceId}', [\App\Http\Controllers\PengumumanController::class, 'getAnggota'])
                ->name('pengumuman.anggota');
            Route::get('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'show'])
                ->name('pengumuman.show');
            Route::get('/pengumuman/{pengumuman}/edit-data', [\App\Http\Controllers\PengumumanController::class, 'getEditData'])
                ->name('pengumuman.edit.data');
            Route::put('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'update'])
                ->name('pengumuman.update');
            Route::delete('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'destroy'])
                ->name('pengumuman.destroy');
        });

        // Pengumuman Company Level
        Route::prefix('companies/{company_id}')->group(function () {
            Route::get('/pengumuman-perusahaan', [\App\Http\Controllers\PengumumanPerusahaanController::class, 'index'])->name('pengumuman-perusahaan.index');
            Route::post('/pengumuman-perusahaan', [\App\Http\Controllers\PengumumanPerusahaanController::class, 'store'])->name('pengumuman-perusahaan.store');
            Route::get('/pengumuman-perusahaan/{id}', [\App\Http\Controllers\PengumumanPerusahaanController::class, 'show'])->name('pengumuman-perusahaan.show');
            Route::get('/pengumuman-perusahaan/{id}/edit-data', [\App\Http\Controllers\PengumumanPerusahaanController::class, 'getEditData'])->name('pengumuman-perusahaan.edit'); // ✅ BENAR
            Route::put('/pengumuman-perusahaan/{id}', [\App\Http\Controllers\PengumumanPerusahaanController::class, 'update'])->name('pengumuman-perusahaan.update');
            Route::delete('/pengumuman-perusahaan/{id}', [\App\Http\Controllers\PengumumanPerusahaanController::class, 'destroy'])->name('pengumuman-perusahaan.destroy');
        });


        // ========================================
        // 🔥 ROLE MANAGEMENT ROUTES
        // ========================================
        Route::get('/hak-akses', [UserController::class, 'hakAkses'])->name('hakAkses');
        Route::post('/update-user-roles', [UserController::class, 'updateUserRoles'])->name('user.updateRoles');
        Route::post('/workspace/{workspaceId}/update-user-roles', [WorkspaceController::class, 'updateUserRoles'])->name('workspace.updateUserRoles');
        Route::get('/workspace/{workspaceId}/user-role', [UserController::class, 'getWorkspaceUserRole']);
        // ✅ TAMBAHKAN ROUTE INI
        Route::get('/workspace/available-roles', [WorkspaceController::class, 'getAvailableRolesForWorkspace'])
            ->middleware('auth')
            ->name('workspace.available-roles');

        // ========================================
        // 🔥 EVENTS API
        // ========================================
        Route::get('/events', function () {
            return response()->json([]);
        })->name('events');

        // ========================================
        // 🔥 MINDMAP ROUTES
        // ========================================
        Route::get('/workspace/{workspace}/mindmap', [MindmapController::class, 'show'])->name('workspace.mindmap');
        Route::get('/mindmap', [MindmapController::class, 'index'])->name('mindmap');
        Route::get('/mindmap/{id}/data', [MindmapController::class, 'getData']);
        Route::post('/mindmap/{id}/save', [MindmapController::class, 'save']);
        Route::get('/mindmap/{mindmap}/data', [MindmapController::class, 'getMindmapData'])->name('mindmap.data');
        Route::post('/mindmap/{mindmap}/save', [MindmapController::class, 'saveNode'])->name('mindmap.save');
        Route::post('/mindmap/{mindmap}/nodes', [MindmapController::class, 'addNode'])->name('mindmap.nodes.add');
        Route::put('/mindmap/nodes/{node}', [MindmapController::class, 'updateNode'])->name('mindmap.nodes.update');
        Route::delete('/mindmap/nodes/{node}', [MindmapController::class, 'deleteNode'])->name('mindmap.nodes.delete');
    });




       Route::prefix('notifications')->group(function () {
    // Get all notifications
    Route::get('/', [NotificationController::class, 'index']);

    // Get unread count
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);

    // Mark single notification as read
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);

    // Mark all notifications as read
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);

    // Delete notification
    Route::delete('/{id}', [NotificationController::class, 'destroy']);

    // Clear all read notifications
    Route::post('/clear-read', [NotificationController::class, 'clearRead']);
});
});
