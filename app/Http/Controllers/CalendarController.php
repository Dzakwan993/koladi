<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\Workspace;
use App\Models\UserCompany;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\UserWorkspace;
use Illuminate\Support\Facades\DB;
use App\Models\CalendarParticipant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{

    /**
     * ✅ Helper: Check if user is SuperAdmin/Admin in company
     */
    private function isCompanyAdmin()
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) return false;

        $userCompany = UserCompany::where('user_id', $user->id)
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $roleName = $userCompany?->role?->name ?? 'Member';

        return in_array($roleName, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
    }

    private function getAvatarUrl($user)
    {
        if (!$user) {
            return 'https://ui-avatars.com/api/?name=User&background=3B82F6&color=fff&bold=true&size=128';
        }

        if ($user->avatar && Str::startsWith($user->avatar, ['http://', 'https://'])) {
            return $user->avatar;
        }

        if ($user->avatar) {
            return asset('storage/' . $user->avatar);
        }

        $name = $user->full_name ?? $user->name ?? 'User';
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=3B82F6&color=fff&bold=true&size=128';
    }

    // ✅ Helper: Check permission untuk buat jadwal COMPANY (Jadwal Umum)
    private function canCreateCompanySchedule()
    {
        $user = Auth::user();
        $companyId = session('active_company_id');

        if (!$companyId) return false;

        $userCompany = UserCompany::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->with('role')
            ->first();

        $companyRole = $userCompany?->role?->name ?? 'Member';

        // ✅ SuperAdmin, Admin, Manager bisa buat jadwal umum
        return in_array($companyRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
    }

    // ✅ Helper: Check permission untuk buat jadwal
    private function canCreateSchedule($workspaceId)
    {
        $user = Auth::user();
        $workspace = Workspace::findOrFail($workspaceId);
        $companyId = $workspace->company_id;

        $userCompany = UserCompany::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->with('role')
            ->first();

        $companyRole = $userCompany?->role?->name ?? 'Member';

        // ✅ SuperAdmin, Admin, Manager di COMPANY = bisa buat jadwal di SEMUA workspace
        if (in_array($companyRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return true;
        }

        // ✅ Jika role di company adalah Member, CEK ROLE DI WORKSPACE
        if ($companyRole === 'Member') {
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->where('status_active', true)
                ->with('role')
                ->first();

            if (!$userWorkspace) {
                return false;
            }

            $workspaceRole = $userWorkspace->role?->name ?? 'Member';

            // ✅ Hanya Manager di workspace yang bisa buat jadwal
            return $workspaceRole === 'Manager';
        }

        return false;
    }

    /**
     * ✅ BARU: Halaman Notulensi Rapat
     */
    public function notulensi($workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $userCompany = UserCompany::where('user_id', $user->id)
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $isCompanyAdmin = in_array($userCompany?->role?->name ?? 'Member', ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);

        if (!$isCompanyAdmin) {
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->where('status_active', true)
                ->firstOrFail();
        }

        // ✅ Ambil jadwal yang:
        // 1. Rapat Online (is_online_meeting = true)
        // 2. Ada komentar (whereHas comments)
        $notulensis = CalendarEvent::where('workspace_id', $workspaceId)
            ->whereNull('deleted_at')
            ->where('is_online_meeting', true) // Hanya rapat online
            ->whereHas('comments') // ✅ FIX: Hanya yang ada komentar
            ->withCount('comments') // Hitung jumlah komentar
            ->with(['creator', 'participants.user'])
            ->orderBy('start_datetime', 'desc')
            ->get();

        // Set avatar URL untuk setiap notulensi
        $notulensis->each(function ($notulensi) {
            $notulensi->creator->avatar_url = $this->getAvatarUrl($notulensi->creator);
            $notulensi->participants->each(function ($participant) {
                $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
            });
        });

        return view('jadwal.workspace.notulensi', [
            'active' => 'jadwal',
            'pageTitle' => 'Notulensi Rapat',
            'workspaceId' => $workspaceId,
            'workspace' => $workspace,
            'notulensis' => $notulensis
        ]);
    }

    /**
     * ✅ BARU: Halaman Jadwal Umum (Company Level)
     */
    public function companyIndex()
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return redirect()->route('dashboard')
                ->with('error', 'Silakan pilih perusahaan terlebih dahulu.');
        }

        $company = Company::findOrFail($activeCompanyId);

        // ✅ Check permission untuk buat jadwal
        $canCreateSchedule = $this->canCreateCompanySchedule();

        return view('jadwal.umum.jadwal-umum', [
            'company' => $company,
            'canCreateSchedule' => $canCreateSchedule
        ]);
    }

    /**
     * ✅ BARU: Form Buat Jadwal Umum
     */
    public function companyCreate()
    {
        // ✅ Check permission
        if (!$this->canCreateCompanySchedule()) {
            return redirect()->route('jadwal-umum')
                ->with('error', 'Anda tidak memiliki izin untuk membuat jadwal.');
        }

        $activeCompanyId = session('active_company_id');
        $company = Company::findOrFail($activeCompanyId);

        // Ambil semua user di company
        $members = User::whereHas('userCompanies', function ($query) use ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId)
                ->whereNull('deleted_at');
        })->get()->map(function ($member) {
            $member->avatar_url = $this->getAvatarUrl($member);
            return $member;
        });

        return view('jadwal.umum.buatJadwalUmum', [

            'company' => $company,
            'members' => $members
        ]);
    }

    /**
     * ✅ BARU: Simpan Jadwal Umum
     */
    public function companyStore(Request $request)
    {
        // ✅ Check permission
        if (!$this->canCreateCompanySchedule()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk membuat jadwal.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'recurrence' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'is_online_meeting' => 'nullable|boolean',
            'meeting_link' => 'nullable|url',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = filter_var($validated['is_online_meeting'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $recurrence = $validated['recurrence'] ?? 'Jangan Ulangi';
            if ($recurrence === 'Jangan Ulangi') {
                $recurrence = null;
            }

            $startDatetime = Carbon::parse($validated['start_datetime'], 'Asia/Jakarta');
            $endDatetime = Carbon::parse($validated['end_datetime'], 'Asia/Jakarta');

            // ✅ workspace_id = NULL untuk jadwal company
            $event = CalendarEvent::create([
                'workspace_id' => null, // NULL = Jadwal Company
                'company_id' => session('active_company_id'),
                'created_by' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $validated['meeting_link'] ?? null,
            ]);

            // Creator langsung accepted
            CalendarParticipant::create([
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'status' => 'accepted',
            ]);

            // Semua peserta langsung accepted
            if (!empty($validated['participants'])) {
                foreach ($validated['participants'] as $userId) {
                    if ($userId !== Auth::id()) {
                        CalendarParticipant::create([
                            'event_id' => $event->id,
                            'user_id' => $userId,
                            'status' => 'accepted',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('jadwal-umum')
                ->with('success', 'Jadwal berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating company calendar event: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat jadwal: ' . $e->getMessage());
        }
    }

    /**
     * ✅ BARU: Get Events untuk Jadwal Umum
     */
    public function getCompanyEvents(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            $user = Auth::user();
            $activeCompanyId = session('active_company_id');

            $startDate = null;
            $endDate = null;

            if ($start) {
                try {
                    $startDate = Carbon::parse($start)->toDateTimeString();
                } catch (\Exception $e) {
                    Log::warning('Failed to parse start date: ' . $start);
                }
            }

            if ($end) {
                try {
                    $endDate = Carbon::parse($end)->toDateTimeString();
                } catch (\Exception $e) {
                    Log::warning('Failed to parse end date: ' . $end);
                }
            }

            // ✅ Query untuk jadwal company (workspace_id = NULL)
            $query = CalendarEvent::whereNull('workspace_id')
                ->where('company_id', $activeCompanyId)
                ->whereNull('deleted_at');

            if ($startDate && $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_datetime', [$startDate, $endDate])
                        ->orWhereBetween('end_datetime', [$startDate, $endDate])
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_datetime', '<=', $startDate)
                                ->where('end_datetime', '>=', $endDate);
                        });
                });
            }

            // Filter based on user access to company
            $query->whereHas('participants', function ($q) use ($user, $activeCompanyId) {
                $q->where('user_id', $user->id)
                    ->whereHas('user.userCompanies', function ($q2) use ($activeCompanyId) {
                        $q2->where('company_id', $activeCompanyId);
                    });
            });

            $events = $query->with(['creator', 'participants.user'])
                ->withCount('comments')
                ->orderBy('start_datetime', 'asc')
                ->get();

            if ($events->isEmpty()) {
                return response()->json([]);
            }

            $formattedEvents = $events->map(function ($event) use ($user) {
                try {
                    $startDate = $event->start_datetime instanceof Carbon
                        ? $event->start_datetime->setTimezone('Asia/Jakarta')
                        : Carbon::parse($event->start_datetime)->setTimezone('Asia/Jakarta');

                    $endDate = $event->end_datetime instanceof Carbon
                        ? $event->end_datetime->setTimezone('Asia/Jakarta')
                        : Carbon::parse($event->end_datetime)->setTimezone('Asia/Jakarta');

                    $participants = $event->participants->take(3)->map(function ($participant) {
                        return [
                            'id' => $participant->user_id,
                            'name' => $participant->user->full_name ?? 'Unknown',
                            'avatar' => $this->getAvatarUrl($participant->user),
                            'status' => $participant->status
                        ];
                    });

                    return [
                        'id' => $event->id,
                        'title' => $event->title ?? 'Untitled Event',
                        'start' => $startDate->toIso8601String(),
                        'end' => $endDate->toIso8601String(),
                        'backgroundColor' => $event->is_private ? '#ef4444' : '#2563eb',
                        'borderColor' => $event->is_private ? '#ef4444' : '#2563eb',
                        'extendedProps' => [
                            'description' => $event->description ?? '',
                            'is_online' => $event->is_online_meeting ?? false,
                            'meeting_link' => $event->meeting_link ?? '',
                            'participants_count' => $event->participants->count(),
                            'participants' => $participants,
                            'creator_name' => $event->creator->full_name ?? 'Unknown',
                            'creator_avatar' => $this->getAvatarUrl($event->creator),
                            'is_creator' => $event->created_by === $user->id,
                            'start_date' => $startDate->format('Y-m-d'),
                            'end_date' => $endDate->format('Y-m-d'),
                            'comments_count' => $event->comments_count ?? 0,
                        ]
                    ];
                } catch (\Exception $e) {
                    Log::error('Error formatting event', [
                        'event_id' => $event->id ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter();

            return response()->json($formattedEvents->values()->all());
        } catch (\Exception $e) {
            Log::error('Error fetching company events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load events',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ BARU: Show Detail Jadwal Umum
     */
    public function companyShow($id)
    {
        $activeCompanyId = session('active_company_id');

        $event = CalendarEvent::with(['creator', 'participants.user'])
            ->whereNull('workspace_id')
            ->where('company_id', $activeCompanyId)
            ->findOrFail($id);

        $user = Auth::user();
        $isParticipant = $event->participants->where('user_id', $user->id)->isNotEmpty();
        $isCreator = $event->created_by === $user->id;

        // ✅ FIXED: Cek apakah SuperAdmin/Admin di company
        $isCompanyAdmin = $this->isCompanyAdmin();

        // ✅ Cek akses untuk jadwal rahasia
        // SuperAdmin/Admin/Manager bisa akses SEMUA jadwal termasuk rahasia
        if ($event->is_private && !$isCreator && !$isParticipant && !$isCompanyAdmin) {
            return redirect()->route('jadwal-umum')
                ->with('access_denied', [
                    'title' => 'Akses Ditolak',
                    'message' => 'Jadwal ini bersifat rahasia. Silakan hubungi pembuat jadwal untuk mendapatkan akses.',
                    'creator_name' => $event->creator->full_name
                ]);
        }

        // Set avatar URL
        $event->participants->each(function ($participant) {
            $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
        });
        $event->creator->avatar_url = $this->getAvatarUrl($event->creator);

        return view('jadwal.umum.detailJadwalUmum', compact('event', 'isParticipant', 'isCreator'));
    }

    /**
     * ✅ BARU: Edit Jadwal Umum
     */
    public function companyEdit($id)
    {
        $activeCompanyId = session('active_company_id');

        $event = CalendarEvent::with('participants.user')
            ->whereNull('workspace_id')
            ->where('company_id', $activeCompanyId) // ✅ Validasi company
            ->findOrFail($id);

        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit jadwal ini');
        }

        $activeCompanyId = session('active_company_id');
        $company = Company::findOrFail($activeCompanyId);

        $members = User::whereHas('userCompanies', function ($query) use ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId)
                ->whereNull('deleted_at');
        })->get()->map(function ($member) {
            $member->avatar_url = $this->getAvatarUrl($member);
            return $member;
        });

        $event->participants->each(function ($participant) {
            $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
        });

        return view('jadwal.umum.editJadwalUmum', compact('event', 'company', 'members'));
    }

    /**
     * ✅ BARU: Update Jadwal Umum
     */
    public function companyUpdate(Request $request, $id)
    {
        $activeCompanyId = session('active_company_id');

        $event = CalendarEvent::whereNull('workspace_id')
            ->where('company_id', $activeCompanyId) // ✅ Validasi company
            ->findOrFail($id);

        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate jadwal ini');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'recurrence' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'is_online_meeting' => 'nullable|boolean',
            'meeting_link' => 'nullable|url',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = filter_var($validated['is_online_meeting'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $recurrence = $validated['recurrence'] ?? 'Jangan Ulangi';
            if ($recurrence === 'Jangan Ulangi') {
                $recurrence = null;
            }

            $startDatetime = Carbon::parse($validated['start_datetime'], 'Asia/Jakarta');
            $endDatetime = Carbon::parse($validated['end_datetime'], 'Asia/Jakarta');

            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $isOnlineMeeting ? ($validated['meeting_link'] ?? null) : null,
            ]);

            if (isset($validated['participants'])) {
                CalendarParticipant::where('event_id', $event->id)
                    ->where('user_id', '!=', $event->created_by)
                    ->delete();

                foreach ($validated['participants'] as $userId) {
                    if ($userId !== $event->created_by) {
                        CalendarParticipant::firstOrCreate([
                            'event_id' => $event->id,
                            'user_id' => $userId,
                        ], [
                            'status' => 'accepted',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('jadwal-umum')
                ->with('success', 'Jadwal berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating company event: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate jadwal: ' . $e->getMessage());
        }
    }

    /**
     * ✅ BARU: Delete Jadwal Umum
     */
    public function companyDestroy($id)
    {
        $activeCompanyId = session('active_company_id');

        $event = CalendarEvent::whereNull('workspace_id')
            ->where('company_id', $activeCompanyId) // ✅ Validasi company
            ->findOrFail($id);

        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus jadwal ini');
        }

        $event->update(['deleted_at' => now()]);

        return redirect()
            ->route('jadwal-umum')
            ->with('success', 'Jadwal berhasil dihapus');
    }

    /**
     * ✅ BARU: Notulensi Jadwal Umum
     */
    public function companyNotulensi()
    {
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return redirect()->route('dashboard')
                ->with('error', 'Silakan pilih perusahaan terlebih dahulu.');
        }

        $company = Company::findOrFail($activeCompanyId);

        $notulensis = CalendarEvent::whereNull('workspace_id') // Hanya jadwal company
            ->where('company_id', $activeCompanyId)
            ->whereNull('deleted_at')
            ->where('is_online_meeting', true)
            ->whereHas('comments')
            ->withCount('comments')
            ->with(['creator', 'participants.user'])
            ->orderBy('start_datetime', 'desc')
            ->get();

        $notulensis->each(function ($notulensi) {
            $notulensi->creator->avatar_url = $this->getAvatarUrl($notulensi->creator);
            $notulensi->participants->each(function ($participant) {
                $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
            });
        });

        return view('jadwal.umum.notulensi-umum', [
            'company' => $company,
            'notulensis' => $notulensis
        ]);
    }

    public function index($workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // ✅ Ganti dengan query langsung (menghilangkan warning IDE)
        $userCompany = UserCompany::where('user_id', $user->id)
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $isCompanyAdmin = in_array($userCompany?->role?->name ?? 'Member', ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);

        if (!$isCompanyAdmin) {
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->where('status_active', true)
                ->firstOrFail();
        }

        // ✅ Check permission untuk buat jadwal
        $canCreateSchedule = $this->canCreateSchedule($workspaceId);

        // ✅ FIX: Ubah path view dari 'jadwal' menjadi 'jadwal.workspace.jadwal'
        return view('jadwal.workspace.jadwal', [
            'workspaceId' => $workspaceId,
            'workspace' => $workspace,
            'canCreateSchedule' => $canCreateSchedule
        ]);
    }

    public function create($workspaceId)
    {
        // ✅ Check permission
        if (!$this->canCreateSchedule($workspaceId)) {
            return redirect()->route('jadwal', ['workspaceId' => $workspaceId])
                ->with('error', 'Anda tidak memiliki izin untuk membuat jadwal.');
        }

        $workspace = Workspace::findOrFail($workspaceId);

        $workspace->load(['userWorkspaces' => function ($query) {
            $query->where('status_active', true)->with('user');
        }]);

        $activeUsers = $workspace->userWorkspaces->pluck('user')->filter();

        if ($activeUsers->isEmpty()) {
            $companyId = $workspace->company_id;
            $companyUsers = UserCompany::where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            $members = $companyUsers;
        } else {
            $members = $activeUsers;
        }

        $members = $members->map(function ($member) {
            $member->avatar_url = $this->getAvatarUrl($member);
            return $member;
        });

        return view('jadwal.workspace.buatJadwal', [
            'active' => 'jadwal',
            'pageTitle' => 'Buat Jadwal',
            'workspaceId' => $workspaceId,
            'workspace' => $workspace,
            'members' => $members
        ]);
    }

    /**
     * ✅ FIXED: Semua peserta langsung accepted (tidak ada status pending)
     */
    public function store(Request $request, $workspaceId)
    {
        // ✅ Check permission
        if (!$this->canCreateSchedule($workspaceId)) {
            return back()->with('error', 'Anda tidak memiliki izin untuk membuat jadwal.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'recurrence' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'is_online_meeting' => 'nullable|boolean',
            'meeting_link' => 'nullable|url',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = filter_var($validated['is_online_meeting'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $recurrence = $validated['recurrence'] ?? 'Jangan Ulangi';
            if ($recurrence === 'Jangan Ulangi') {
                $recurrence = null;
            }

            $startDatetime = Carbon::parse($validated['start_datetime'], 'Asia/Jakarta');
            $endDatetime = Carbon::parse($validated['end_datetime'], 'Asia/Jakarta');

            $event = CalendarEvent::create([
                'workspace_id' => $workspaceId,
                'created_by' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $validated['meeting_link'] ?? null,
            ]);

            // ✅ Creator langsung accepted
            CalendarParticipant::create([
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'status' => 'accepted',
            ]);

            // ✅ SEMUA PESERTA LANGSUNG ACCEPTED (tidak ada pending)
            if (!empty($validated['participants'])) {
                foreach ($validated['participants'] as $userId) {
                    if ($userId !== Auth::id()) {
                        CalendarParticipant::create([
                            'event_id' => $event->id,
                            'user_id' => $userId,
                            'status' => 'accepted', // ✅ Langsung accepted
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('jadwal', ['workspaceId' => $workspaceId])
                ->with('success', 'Jadwal berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating calendar event: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Get events untuk API (untuk FullCalendar)
     */
    public function getEvents(Request $request, $workspaceId)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            $user = Auth::user();

            $startDate = null;
            $endDate = null;

            if ($start) {
                try {
                    $startDate = Carbon::parse($start)->toDateTimeString();
                } catch (\Exception $e) {
                    Log::warning('Failed to parse start date: ' . $start);
                }
            }

            if ($end) {
                try {
                    $endDate = Carbon::parse($end)->toDateTimeString();
                } catch (\Exception $e) {
                    Log::warning('Failed to parse end date: ' . $end);
                }
            }

            $query = CalendarEvent::where('workspace_id', $workspaceId)
                ->whereNull('deleted_at');

            if ($startDate && $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_datetime', [$startDate, $endDate])
                        ->orWhereBetween('end_datetime', [$startDate, $endDate])
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_datetime', '<=', $startDate)
                                ->where('end_datetime', '>=', $endDate);
                        });
                });
            }

            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('participants', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
            });

            $events = $query->with(['creator', 'participants.user'])
                ->withCount('comments')
                ->orderBy('start_datetime', 'asc')
                ->get();

            if ($events->isEmpty()) {
                return response()->json([]);
            }

            $formattedEvents = $events->map(function ($event) use ($user) {
                try {
                    $startDate = $event->start_datetime instanceof Carbon
                        ? $event->start_datetime->setTimezone('Asia/Jakarta')
                        : Carbon::parse($event->start_datetime)->setTimezone('Asia/Jakarta');

                    $endDate = $event->end_datetime instanceof Carbon
                        ? $event->end_datetime->setTimezone('Asia/Jakarta')
                        : Carbon::parse($event->end_datetime)->setTimezone('Asia/Jakarta');

                    $participants = $event->participants->take(3)->map(function ($participant) {
                        return [
                            'id' => $participant->user_id,
                            'name' => $participant->user->full_name ?? 'Unknown',
                            'avatar' => $this->getAvatarUrl($participant->user),
                            'status' => $participant->status
                        ];
                    });

                    return [
                        'id' => $event->id,
                        'title' => $event->title ?? 'Untitled Event',
                        'start' => $startDate->toIso8601String(),
                        'end' => $endDate->toIso8601String(),
                        'backgroundColor' => $event->is_private ? '#ef4444' : '#2563eb',
                        'borderColor' => $event->is_private ? '#ef4444' : '#2563eb',
                        'extendedProps' => [
                            'description' => $event->description ?? '',
                            'is_online' => $event->is_online_meeting ?? false,
                            'meeting_link' => $event->meeting_link ?? '',
                            'participants_count' => $event->participants->count(),
                            'participants' => $participants,
                            'creator_name' => $event->creator->full_name ?? 'Unknown',
                            'creator_avatar' => $this->getAvatarUrl($event->creator),
                            'is_creator' => $event->created_by === $user->id,
                            'start_date' => $startDate->format('Y-m-d'),
                            'end_date' => $endDate->format('Y-m-d'),
                            'comments_count' => $event->comments_count ?? 0,
                        ]
                    ];
                } catch (\Exception $e) {
                    Log::error('Error formatting event', [
                        'event_id' => $event->id ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter();

            return response()->json($formattedEvents->values()->all());
        } catch (\Exception $e) {
            Log::error('Error fetching events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to load events',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail event
     */
    public function show($workspaceId, $id)
    {
        $event = CalendarEvent::with(['creator', 'participants.user'])
            ->where('workspace_id', $workspaceId)
            ->findOrFail($id);

        $user = Auth::user();
        $isParticipant = $event->participants->where('user_id', $user->id)->isNotEmpty();
        $isCreator = $event->created_by === $user->id;

        // ✅ FIXED: Cek apakah SuperAdmin/Admin di company
        $workspace = Workspace::findOrFail($workspaceId);
        $isCompanyAdmin = $this->isCompanyAdmin();

        // ✅ Cek akses untuk jadwal rahasia
        // SuperAdmin/Admin/Manager bisa akses SEMUA jadwal termasuk rahasia
        if ($event->is_private && !$isCreator && !$isParticipant && !$isCompanyAdmin) {
            return redirect()->route('jadwal', ['workspaceId' => $workspaceId])
                ->with('access_denied', [
                    'title' => 'Akses Ditolak',
                    'message' => 'Jadwal ini bersifat rahasia. Silakan hubungi pembuat jadwal untuk mendapatkan akses.',
                    'creator_name' => $event->creator->full_name
                ]);
        }

        // Set avatar URL
        $event->participants->each(function ($participant) {
            $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
        });
        $event->creator->avatar_url = $this->getAvatarUrl($event->creator);

        return view('jadwal.workspace.detailJadwal', compact('event', 'isParticipant', 'isCreator', 'workspaceId'));
    }

    public function edit($workspaceId, $id)
    {
        $event = CalendarEvent::with('participants.user')->findOrFail($id);

        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit jadwal ini');
        }

        $workspace = Workspace::findOrFail($workspaceId);

        $workspace->load(['userWorkspaces' => function ($query) {
            $query->where('status_active', true)->with('user');
        }]);

        $activeUsers = $workspace->userWorkspaces->pluck('user')->filter();

        if ($activeUsers->isEmpty()) {
            $companyId = $workspace->company_id;
            $companyUsers = UserCompany::where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            $members = $companyUsers;
        } else {
            $members = $activeUsers;
        }

        $members = $members->map(function ($member) {
            $member->avatar_url = $this->getAvatarUrl($member);
            return $member;
        });

        $event->participants->each(function ($participant) {
            $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
        });

        return view('jadwal.workspace.editJadwal', compact('event', 'workspace', 'workspaceId', 'members'));
    }

    /**
     * ✅ FIXED: Update juga langsung accepted semua peserta
     */
    public function update(Request $request, $workspaceId, $id)
    {
        $event = CalendarEvent::where('workspace_id', $workspaceId)->findOrFail($id);

        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate jadwal ini');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'recurrence' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'is_online_meeting' => 'nullable|boolean',
            'meeting_link' => 'nullable|url',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = filter_var($validated['is_online_meeting'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $recurrence = $validated['recurrence'] ?? 'Jangan Ulangi';
            if ($recurrence === 'Jangan Ulangi') {
                $recurrence = null;
            }

            $startDatetime = Carbon::parse($validated['start_datetime'], 'Asia/Jakarta');
            $endDatetime = Carbon::parse($validated['end_datetime'], 'Asia/Jakarta');

            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $isOnlineMeeting ? ($validated['meeting_link'] ?? null) : null,
            ]);

            if (isset($validated['participants'])) {
                CalendarParticipant::where('event_id', $event->id)
                    ->where('user_id', '!=', $event->created_by)
                    ->delete();

                // ✅ Semua peserta baru langsung accepted
                foreach ($validated['participants'] as $userId) {
                    if ($userId !== $event->created_by) {
                        CalendarParticipant::firstOrCreate([
                            'event_id' => $event->id,
                            'user_id' => $userId,
                        ], [
                            'status' => 'accepted', // ✅ Langsung accepted
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('jadwal', ['workspaceId' => $workspaceId])
                ->with('success', 'Jadwal berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating event: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate jadwal: ' . $e->getMessage());
        }
    }

    public function destroy($workspaceId, $id)
    {
        $event = CalendarEvent::where('workspace_id', $workspaceId)->findOrFail($id);

        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus jadwal ini');
        }

        $event->update(['deleted_at' => now()]);

        return redirect()
            ->route('jadwal', ['workspaceId' => $workspaceId])
            ->with('success', 'Jadwal berhasil dihapus');
    }

    /**
     * ❌ METHOD INI TIDAK DIPERLUKAN LAGI (karena tidak ada status pending)
     */
    public function updateParticipantStatus(Request $request, $workspaceId, $eventId)
    {
        // Method ini bisa dihapus atau dibiarkan kosong
        return back()->with('info', 'Fitur status undangan sudah tidak digunakan');
    }
}
