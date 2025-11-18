<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\CalendarParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Menampilkan halaman calendar dengan events
     */
    public function index($workspaceId)
    {
        $workspace = \App\Models\Workspace::findOrFail($workspaceId);
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $isCompanyAdmin = in_array($userCompany?->role?->name ?? 'Member', ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);

        if (!$isCompanyAdmin) {
            $userWorkspace = \App\Models\UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->where('status_active', true)
                ->firstOrFail();
        }

        return view('jadwal', [
            'workspaceId' => $workspaceId,
            'workspace' => $workspace
        ]);
    }

    /**
     * Menampilkan form create event
     */
    public function create($workspaceId)
    {
        $workspace = \App\Models\Workspace::findOrFail($workspaceId);

        $workspace->load(['userWorkspaces' => function ($query) {
            $query->where('status_active', true)->with('user');
        }]);

        $activeUsers = $workspace->userWorkspaces->pluck('user')->filter();

        if ($activeUsers->isEmpty()) {
            $companyId = $workspace->company_id;
            $companyUsers = \App\Models\UserCompany::where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            $members = $companyUsers;
        } else {
            $members = $activeUsers;
        }

        return view('buatJadwal', [
            'workspaceId' => $workspaceId,
            'workspace' => $workspace,
            'members' => $members
        ]);
    }

    /**
     * Menyimpan event baru - FIXED VERSION
     */
    public function store(Request $request, $workspaceId)
    {
        // Validasi input
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
            // Normalisasi nilai boolean
            $isPrivate = filter_var($validated['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isOnlineMeeting = filter_var($validated['is_online_meeting'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Normalisasi recurrence
            $recurrence = $validated['recurrence'] ?? 'Jangan Ulangi';
            if ($recurrence === 'Jangan Ulangi') {
                $recurrence = null;
            }

            // Buat event
            $event = CalendarEvent::create([
                'workspace_id' => $workspaceId,
                'created_by' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $validated['start_datetime'],
                'end_datetime' => $validated['end_datetime'],
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $validated['meeting_link'] ?? null,
            ]);

            // Tambahkan creator sebagai participant dengan status accepted
            CalendarParticipant::create([
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'status' => 'accepted',
            ]);

            // Tambahkan participants lainnya
            if (!empty($validated['participants'])) {
                foreach ($validated['participants'] as $userId) {
                    if ($userId !== Auth::id()) {
                        CalendarParticipant::create([
                            'event_id' => $event->id,
                            'user_id' => $userId,
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('jadwal', ['workspaceId' => $workspaceId])
                ->with('success', 'Jadwal berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating calendar event: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Get events untuk API (untuk FullCalendar) - COMPLETELY FIXED VERSION
     */
    public function getEvents(Request $request, $workspaceId)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');
            $user = Auth::user();

            // ✅ FIX: Parse datetime string dari FullCalendar ke format yang benar
            $startDate = null;
            $endDate = null;

            if ($start) {
                try {
                    // Parse berbagai format datetime yang mungkin dikirim FullCalendar
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

            // Log untuk debugging
            Log::info('Fetching events', [
                'workspace_id' => $workspaceId,
                'user_id' => $user->id,
                'start_raw' => $start,
                'end_raw' => $end,
                'start_parsed' => $startDate,
                'end_parsed' => $endDate
            ]);

            $query = CalendarEvent::where('workspace_id', $workspaceId)
                ->whereNull('deleted_at');

            // Filter by date range jika ada DAN berhasil di-parse
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

            // Filter berdasarkan access
            $query->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('participants', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
            });

            // ✅ PENTING: Eager load relations SEBELUM get()
            $events = $query->with(['creator', 'participants.user'])
                ->orderBy('start_datetime', 'asc')
                ->get();

            Log::info('Events found', ['count' => $events->count()]);

            // ✅ FIX: Cek apakah events kosong
            if ($events->isEmpty()) {
                return response()->json([]);
            }

            // Format untuk FullCalendar
            $formattedEvents = $events->map(function ($event) use ($user) {
                try {
                    // ✅ FIX: Pastikan datetime di-parse dengan benar
                    $startDate = $event->start_datetime instanceof Carbon
                        ? $event->start_datetime
                        : Carbon::parse($event->start_datetime);

                    $endDate = $event->end_datetime instanceof Carbon
                        ? $event->end_datetime
                        : Carbon::parse($event->end_datetime);

                    // ✅ Get participants dengan avatar (limit 3 untuk display)
                    $participants = $event->participants->take(3)->map(function ($participant) {
                        return [
                            'id' => $participant->user_id,
                            'name' => $participant->user->full_name ?? 'Unknown',
                            'avatar' => $participant->user->avatar
                                ? asset('storage/' . $participant->user->avatar)
                                : asset('images/default-avatar.png'),
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
                            'creator_avatar' => $event->creator && $event->creator->avatar
                                ? asset('storage/' . $event->creator->avatar)
                                : asset('images/default-avatar.png'),
                            'is_creator' => $event->created_by === $user->id,
                        ]
                    ];
                } catch (\Exception $e) {
                    Log::error('Error formatting event', [
                        'event_id' => $event->id ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter(); // ✅ Remove null values

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

        $hasWorkspaceAccess = \App\Models\UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspaceId)
            ->where('status_active', true)
            ->exists();

        if ($event->is_private && !$isCreator && !$isParticipant && !$hasWorkspaceAccess) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini');
        }

        return view('workspace.calendar.show', compact('event', 'isParticipant', 'workspaceId'));
    }

    /**
     * Menampilkan form edit event
     */
    public function edit($workspaceId, $id)
    {
        $event = CalendarEvent::with('participants')->findOrFail($id);

        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit jadwal ini');
        }

        $workspace = \App\Models\Workspace::findOrFail($workspaceId);

        $workspace->load(['userWorkspaces' => function ($query) {
            $query->where('status_active', true)->with('user');
        }]);

        $activeUsers = $workspace->userWorkspaces->pluck('user')->filter();

        if ($activeUsers->isEmpty()) {
            $companyId = $workspace->company_id;
            $companyUsers = \App\Models\UserCompany::where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            $members = $companyUsers;
        } else {
            $members = $activeUsers;
        }

        return view('editJadwal', compact('event', 'workspace', 'workspaceId', 'members'));
    }

    /**
     * Update event
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

            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $validated['start_datetime'],
                'end_datetime' => $validated['end_datetime'],
                'recurrence' => $recurrence,
                'is_private' => $isPrivate,
                'is_online_meeting' => $isOnlineMeeting,
                'meeting_link' => $validated['meeting_link'] ?? null,
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
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('calendar.show', ['workspaceId' => $workspaceId, 'id' => $event->id])
                ->with('success', 'Jadwal berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating event: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete event
     */
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
     * Update status participant (accept/decline)
     */
    public function updateParticipantStatus(Request $request, $workspaceId, $eventId)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $participant = CalendarParticipant::where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $participant->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status berhasil diupdate');
    }
}
