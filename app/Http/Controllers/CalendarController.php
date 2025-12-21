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
use App\Services\NotificationService;

class CalendarController extends Controller
{

    // ✅ TAMBAH INI
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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

        // Pastikan ada avatar dari storage
        if ($user->avatar) {
            if (Str::startsWith($user->avatar, ['http://', 'https://'])) {
                return $user->avatar;
            }
            return asset('storage/' . $user->avatar);
        }

        // Fallback ke UI Avatars dengan validasi ketat
        $name = $user->full_name ?? $user->name ?? 'User';

        // Bersihkan nama dari karakter khusus
        $cleanName = preg_replace('/[^a-zA-Z\s]/', '', $name);
        $cleanName = trim($cleanName);

        // Jika nama kosong setelah dibersihkan, gunakan 'User'
        if (empty($cleanName)) {
            $cleanName = 'User';
        }

        // Ambil inisial jika nama panjang
        if (str_word_count($cleanName) > 1) {
            $words = explode(' ', $cleanName);
            $cleanName = $words[0][0] . (isset($words[1]) ? $words[1][0] : '');
        } else {
            $cleanName = substr($cleanName, 0, 2);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($cleanName) . '&background=3B82F6&color=fff&bold=true&size=128';
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

        // ✅ TAMPILKAN SEMUA (online + offline) yang punya komentar
        $notulensis = CalendarEvent::where('workspace_id', $workspaceId)
            ->whereNull('deleted_at')
            // ✅ HAPUS filter is_online_meeting
            ->whereHas('comments') // Yang penting ada komentar
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

        return view('jadwal.workspace.notulensi', [
            'active' => 'jadwal',
            'jadwalSubPage' => 'notulensi',
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
            'meeting_mode' => 'required|in:online,offline', // ✅ TAMBAH VALIDASI
            'meeting_link' => 'required_if:meeting_mode,online|nullable|url', // ✅ WAJIB JIKA ONLINE
            'location' => 'required_if:meeting_mode,offline|nullable|string|max:255', // ✅ WAJIB JIKA OFFLINE
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = $validated['meeting_mode'] === 'online'; // ✅ DARI meeting_mode

            $recurrence = $validated['recurrence'] ?? 'Jangan Ulangi';
            if ($recurrence === 'Jangan Ulangi') {
                $recurrence = null;
            }

            $startDatetime = Carbon::parse($validated['start_datetime'], 'Asia/Jakarta');
            $endDatetime = Carbon::parse($validated['end_datetime'], 'Asia/Jakarta');

            $event = CalendarEvent::create([
                'workspace_id' => null,
                'company_id' => session('active_company_id'),
                'created_by' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $isOnlineMeeting ? ($validated['meeting_link'] ?? null) : null, // ✅ HANYA JIKA ONLINE
                'location' => !$isOnlineMeeting ? ($validated['location'] ?? null) : null, // ✅ HANYA JIKA OFFLINE
            ]);

            // Creator langsung accepted
            CalendarParticipant::create([
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'status' => 'accepted',
            ]);

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

                        $this->notificationService->notifyEventCreated($event);


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

            // ✅ FILTER: User hanya melihat jadwal yang tidak rahasia ATAU jadwal dimana dia peserta
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false) // Jadwal publik
                    ->orWhereHas('participants', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id); // Atau jadwal dimana user adalah peserta
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
                            'location' => $event->location ?? '', // ✅ TAMBAH LOCATION
                            'is_private' => $event->is_private ?? false, // ✅ TAMBAH IS_PRIVATE
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
            ->where('company_id', $activeCompanyId)
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
            'meeting_mode' => 'required|in:online,offline',
            'meeting_link' => 'required_if:meeting_mode,online|nullable|url',
            'location' => 'required_if:meeting_mode,offline|nullable|string|max:255',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = $validated['meeting_mode'] === 'online';

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
                'location' => !$isOnlineMeeting ? ($validated['location'] ?? null) : null,
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

        // ✅ TAMPILKAN SEMUA (online + offline) yang punya komentar
        $notulensis = CalendarEvent::whereNull('workspace_id')
            ->where('company_id', $activeCompanyId)
            ->whereNull('deleted_at')
            // ✅ HAPUS filter is_online_meeting
            ->whereHas('comments') // Yang penting ada komentar
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

    // Update method create untuk workspace
    public function create($workspaceId)
    {
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

        // ✅ PASTIKAN INI ADA - MAPPING AVATAR
        $members = $members->map(function ($member) {
            $member->avatar_url = $this->getAvatarUrl($member);
            return $member;
        });

        return view('jadwal.workspace.buatJadwal', [
            'active' => 'jadwal',
            'jadwalSubPage' => 'buat-jadwal',
            'pageTitle' => 'Buat Jadwal',
            'workspaceId' => $workspaceId,
            'workspace' => $workspace,
            'members' => $members // ✅ PASTIKAN $members SUDAH DI-MAP
        ]);
    }

    /**
     * ✅ FIXED: Semua peserta langsung accepted (tidak ada status pending)
     */
    public function store(Request $request, $workspaceId)
    {
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
            'meeting_mode' => 'required|in:online,offline',
            'meeting_link' => 'required_if:meeting_mode,online|nullable|url',
            'location' => 'required_if:meeting_mode,offline|nullable|string|max:255',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // ✅ GET WORKSPACE TO GET COMPANY_ID
            $workspace = Workspace::findOrFail($workspaceId);

            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = $validated['meeting_mode'] === 'online';

            $recurrence = $validated['recurrence'] ?? 'Jangan Ulangi';
            if ($recurrence === 'Jangan Ulangi') {
                $recurrence = null;
            }

            $startDatetime = Carbon::parse($validated['start_datetime'], 'Asia/Jakarta');
            $endDatetime = Carbon::parse($validated['end_datetime'], 'Asia/Jakarta');

            $event = CalendarEvent::create([
                'workspace_id' => $workspaceId,
                'company_id' => $workspace->company_id, // ✅ TAMBAHKAN INI
                'created_by' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $isOnlineMeeting ? ($validated['meeting_link'] ?? null) : null,
                'location' => !$isOnlineMeeting ? ($validated['location'] ?? null) : null,
            ]);

            // Creator langsung accepted
            CalendarParticipant::create([
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'status' => 'accepted',
            ]);

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

                        $this->notificationService->notifyEventCreated($event);


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
                            'location' => $event->location ?? '', // ✅ TAMBAH LOCATION
                            'is_private' => $event->is_private ?? false, // ✅ TAMBAH IS_PRIVATE
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

        $workspace = Workspace::findOrFail($workspaceId);
        $isCompanyAdmin = $this->isCompanyAdmin();

        if ($event->is_private && !$isCreator && !$isParticipant && !$isCompanyAdmin) {
            return redirect()->route('jadwal', ['workspaceId' => $workspaceId])
                ->with('access_denied', [
                    'title' => 'Akses Ditolak',
                    'message' => 'Jadwal ini bersifat rahasia. Silakan hubungi pembuat jadwal untuk mendapatkan akses.',
                    'creator_name' => $event->creator->full_name
                ]);
        }

        $event->participants->each(function ($participant) {
            $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
        });
        $event->creator->avatar_url = $this->getAvatarUrl($event->creator);

        return view('jadwal.workspace.detailJadwal', [
            'event' => $event,
            'isParticipant' => $isParticipant,
            'isCreator' => $isCreator,
            'workspaceId' => $workspaceId,
            'workspace' => $workspace, // ✅ TAMBAHKAN INI
            'active' => 'jadwal', // ✅ TAMBAHKAN INI
            'jadwalSubPage' => 'detail-jadwal' // ✅ TAMBAHKAN INI
        ]);
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

        return view('jadwal.workspace.editJadwal', [
            'event' => $event,
            'workspace' => $workspace,
            'workspaceId' => $workspaceId,
            'members' => $members,
            'active' => 'jadwal', // ✅ TAMBAHKAN INI
            'jadwalSubPage' => 'edit-jadwal' // ✅ TAMBAHKAN INI (opsional, untuk halaman edit)
        ]);
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
            'meeting_mode' => 'required|in:online,offline',
            'meeting_link' => 'required_if:meeting_mode,online|nullable|url',
            'location' => 'required_if:meeting_mode,offline|nullable|string|max:255',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // ✅ GET WORKSPACE TO ENSURE COMPANY_ID
            $workspace = Workspace::findOrFail($workspaceId);

            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = $validated['meeting_mode'] === 'online';

            $recurrence = $validated['recurrence'] ?? 'Jangan Ulangi';
            if ($recurrence === 'Jangan Ulangi') {
                $recurrence = null;
            }

            $startDatetime = Carbon::parse($validated['start_datetime'], 'Asia/Jakarta');
            $endDatetime = Carbon::parse($validated['end_datetime'], 'Asia/Jakarta');

            $event->update([
                'company_id' => $workspace->company_id, // ✅ PASTIKAN COMPANY_ID ADA
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $isOnlineMeeting ? ($validated['meeting_link'] ?? null) : null,
                'location' => !$isOnlineMeeting ? ($validated['location'] ?? null) : null,
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

// Tambahkan method ini ke dalam CalendarController.php

    /**
     * ✅ Check apakah ada jadwal yang bentrok untuk user tertentu
     * Cek di jadwal umum (company) DAN jadwal workspace
     */
    private function checkScheduleConflict($userId, $startDatetime, $endDatetime, $excludeEventId = null, $currentWorkspaceId = null)
    {
        $conflicts = [];

        // Parse datetime
        $start = Carbon::parse($startDatetime);
        $end = Carbon::parse($endDatetime);

        // 1️⃣ CEK JADWAL UMUM (COMPANY LEVEL) - dimana user adalah peserta
        $companyConflicts = CalendarEvent::whereNull('workspace_id')
            ->where('company_id', session('active_company_id'))
            ->whereNull('deleted_at')
            ->when($excludeEventId, function ($q) use ($excludeEventId) {
                $q->where('id', '!=', $excludeEventId);
            })
            ->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where(function ($q) use ($start, $end) {
                // Cek overlap: jadwal baru mulai sebelum jadwal lama selesai
                // DAN jadwal baru selesai setelah jadwal lama mulai
                $q->where(function ($query) use ($start, $end) {
                    $query->where('start_datetime', '<', $end)
                        ->where('end_datetime', '>', $start);
                });
            })
            ->with(['participants', 'creator'])
            ->get();

        foreach ($companyConflicts as $event) {
            $conflicts[] = [
                'type' => 'company',
                'event' => $event,
                'title' => $event->title,
                'start' => $event->start_datetime->format('d M Y H:i'),
                'end' => $event->end_datetime->format('d M Y H:i'),
                'location' => $event->location ?? ($event->is_online_meeting ? 'Online Meeting' : '-'),
                'is_online' => $event->is_online_meeting
            ];
        }

        // 2️⃣ CEK JADWAL WORKSPACE - dimana user adalah peserta
        $workspaceConflicts = CalendarEvent::whereNotNull('workspace_id')
            ->whereNull('deleted_at')
            ->when($excludeEventId, function ($q) use ($excludeEventId) {
                $q->where('id', '!=', $excludeEventId);
            })
            ->when($currentWorkspaceId, function ($q) use ($currentWorkspaceId) {
                // Jika sedang edit jadwal workspace, exclude workspace yang sama
                // karena konflik di workspace yang sama sudah jelas
                $q->where('workspace_id', '!=', $currentWorkspaceId);
            })
            ->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($query) use ($start, $end) {
                    $query->where('start_datetime', '<', $end)
                        ->where('end_datetime', '>', $start);
                });
            })
            ->with(['participants', 'creator', 'workspace'])
            ->get();

        foreach ($workspaceConflicts as $event) {
            $conflicts[] = [
                'type' => 'workspace',
                'event' => $event,
                'title' => $event->title,
                'workspace_name' => $event->workspace->name ?? 'Unknown Workspace',
                'start' => $event->start_datetime->format('d M Y H:i'),
                'end' => $event->end_datetime->format('d M Y H:i'),
                'location' => $event->location ?? ($event->is_online_meeting ? 'Online Meeting' : '-'),
                'is_online' => $event->is_online_meeting
            ];
        }

        return $conflicts;
    }

    /**
     * ✅ API endpoint untuk cek konflik jadwal (dipanggil dari frontend)
     */
    public function checkConflicts(Request $request, $workspaceId = null)
    {
        try {
            $validated = $request->validate([
                'start_datetime' => 'required|date',
                'end_datetime' => 'required|date|after:start_datetime',
                'exclude_event_id' => 'nullable|uuid',
                'participants' => 'nullable|array',
                'participants.*' => 'uuid|exists:users,id'
            ]);

            $userId = Auth::id();
            $participants = $validated['participants'] ?? [$userId];

            // Cek konflik untuk semua peserta
            $allConflicts = [];
            foreach ($participants as $participantId) {
                $conflicts = $this->checkScheduleConflict(
                    $participantId,
                    $validated['start_datetime'],
                    $validated['end_datetime'],
                    $validated['exclude_event_id'] ?? null,
                    $workspaceId
                );

                if (!empty($conflicts)) {
                    $user = User::find($participantId);
                    $allConflicts[$participantId] = [
                        'user_name' => $user->full_name ?? 'Unknown',
                        'conflicts' => $conflicts
                    ];
                }
            }

            return response()->json([
                'has_conflicts' => !empty($allConflicts),
                'conflicts' => $allConflicts
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking conflicts: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to check conflicts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ API endpoint untuk jadwal umum
     */
    public function checkCompanyConflicts(Request $request)
    {
        return $this->checkConflicts($request, null);
    }

    public function recordAttendance(Request $request, $eventId)
    {
        try {
            $user = Auth::user();

            // ✅ Cari atau buat participant baru
            $participant = CalendarParticipant::firstOrCreate(
                [
                    'event_id' => $eventId,
                    'user_id' => $user->id,
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'status' => 'accepted', // Auto-accept karena dia join meeting
                    'attendance' => false
                ]
            );

            // ✅ Update attendance menjadi true
            $participant->update(['attendance' => true]);

            // Log untuk tracking
            Log::info('User joined meeting', [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'was_new_participant' => $participant->wasRecentlyCreated,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kehadiran berhasil dicatat',
                'was_new_participant' => $participant->wasRecentlyCreated
            ]);
        } catch (\Exception $e) {
            Log::error('Error recording attendance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat kehadiran'
            ], 500);
        }
    }

    /**
     * ✅ BARU: Get attendance statistics untuk event
     */
    public function getAttendanceStats($eventId)
    {
        try {
            $event = CalendarEvent::with('participants')->findOrFail($eventId);

            $totalParticipants = $event->participants->count();
            $attended = $event->participants->where('attendance', true)->count();
            $notAttended = $totalParticipants - $attended;

            $attendanceRate = $totalParticipants > 0
                ? round(($attended / $totalParticipants) * 100, 2)
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_participants' => $totalParticipants,
                    'attended' => $attended,
                    'not_attended' => $notAttended,
                    'attendance_rate' => $attendanceRate,
                    'participants' => $event->participants->map(function ($p) {
                        return [
                            'id' => $p->user_id,
                            'name' => $p->user->full_name,
                            'avatar' => $this->getAvatarUrl($p->user),
                            'attended' => $p->attendance,
                            'attended_at' => $p->updated_at // Jika attendance true, berarti dia update saat join
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting attendance stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik kehadiran'
            ], 500);
        }
    }
}
