<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
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

    private function getAvatarUrl($user)
    {
        if (!$user) {
            return 'https://ui-avatars.com/api/?name=User&background=3B82F6&color=fff&bold=true&size=128';
        }

        // Jika avatar adalah URL penuh
        if ($user->avatar && Str::startsWith($user->avatar, ['http://', 'https://'])) {
            return $user->avatar;
        }

        // Jika avatar adalah path storage
        if ($user->avatar) {
            return asset('storage/' . $user->avatar);
        }

        // Default avatar dengan inisial
        $name = $user->full_name ?? $user->name ?? 'User';
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=3B82F6&color=fff&bold=true&size=128';
    }

    public function index($workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
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

        return view('jadwal', [
            'workspaceId' => $workspaceId,
            'workspace' => $workspace
        ]);
    }

    public function create($workspaceId)
    {
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

        // Add avatar URL to each member
        $members = $members->map(function ($member) {
            $member->avatar_url = $this->getAvatarUrl($member);
            return $member;
        });

        return view('buatJadwal', [
            'workspaceId' => $workspaceId,
            'workspace' => $workspace,
            'members' => $members
        ]);
    }

    /**
     * Menyimpan event baru
     */
    public function store(Request $request, $workspaceId)
    {
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
                            'status' => 'pending',
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
                ->orderBy('start_datetime', 'asc')
                ->get();

            if ($events->isEmpty()) {
                return response()->json([]);
            }

            $formattedEvents = $events->map(function ($event) use ($user) {
                try {
                    $startDate = $event->start_datetime instanceof Carbon
                        ? $event->start_datetime
                        : Carbon::parse($event->start_datetime);

                    $endDate = $event->end_datetime instanceof Carbon
                        ? $event->end_datetime
                        : Carbon::parse($event->end_datetime);

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

        $hasWorkspaceAccess = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspaceId)
            ->where('status_active', true)
            ->exists();

        if ($event->is_private && !$isCreator && !$isParticipant && !$hasWorkspaceAccess) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini');
        }

        // Add avatar URL to participants
        $event->participants->each(function ($participant) {
            $participant->user->avatar_url = $this->getAvatarUrl($participant->user);
        });

        $event->creator->avatar_url = $this->getAvatarUrl($event->creator);

        return view('detailJadwal', compact('event', 'isParticipant', 'isCreator', 'workspaceId'));
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
