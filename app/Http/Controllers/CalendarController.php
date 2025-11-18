<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\CalendarParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Menampilkan halaman calendar dengan events
     */
    public function index($workspaceId)
    {
        // ✅ Validasi workspace exists dan user punya akses
        $workspace = \App\Models\Workspace::findOrFail($workspaceId);

        // ✅ Cek apakah user punya akses ke workspace ini
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $isCompanyAdmin = in_array($userCompany?->role?->name ?? 'Member', ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);

        // Jika bukan admin, cek membership workspace
        if (!$isCompanyAdmin) {
            $userWorkspace = \App\Models\UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->where('status_active', true)
                ->firstOrFail();
        }

        // ✅ Pass workspaceId dan workspace data ke view
        return view('jadwal', [
            'workspaceId' => $workspaceId,
            'workspace' => $workspace
        ]);
    }

    /**
     * Menampilkan detail event
     */
    public function show($workspaceId, $id)
    {
        $event = CalendarEvent::with(['creator', 'participants.user'])
            ->where('workspace_id', $workspaceId)
            ->findOrFail($id);

        // Cek apakah user adalah participant atau punya akses workspace
        $user = Auth::user();
        $isParticipant = $event->participants->where('user_id', $user->id)->isNotEmpty();
        $isCreator = $event->created_by === $user->id;

        // Cek workspace access
        $hasWorkspaceAccess = \App\Models\UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspaceId)
            ->where('status_active', true)
            ->exists();

        // Jika private event, hanya creator dan participants yang bisa lihat
        if ($event->is_private && !$isCreator && !$isParticipant && !$hasWorkspaceAccess) {
            abort(403, 'Anda tidak memiliki akses ke event ini');
        }

        return view('workspace.calendar.show', compact('event', 'isParticipant', 'workspaceId'));
    }

    /**
     * Menampilkan form create event
     */
    public function create($workspaceId)
    {
        // ✅ FIX: Load members dengan cara manual untuk menghindari ambiguous column
        $workspace = \App\Models\Workspace::findOrFail($workspaceId);

        // Load userWorkspaces yang aktif dengan user-nya
        $workspace->load(['userWorkspaces' => function ($query) {
            $query->where('status_active', true)->with('user');
        }]);

        // Convert userWorkspaces to collection of users
        $activeUsers = $workspace->userWorkspaces->pluck('user')->filter();

        // Jika tidak ada members, ambil semua user dari company untuk dijadikan pilihan participants
        if ($activeUsers->isEmpty()) {
            $companyId = $workspace->company_id;
            $companyUsers = \App\Models\UserCompany::where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();

            $members = $companyUsers; // ✅ Gunakan nama variabel yang sama dengan view
        } else {
            $members = $activeUsers; // ✅ Gunakan nama variabel yang sama dengan view
        }

        return view('buatJadwal', [ // ✅ Ganti nama view
            'workspaceId' => $workspaceId,
            'workspace' => $workspace,
            'members' => $members // ✅ Pass variabel members
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
            'recurrence' => 'nullable|string|in:Jangan Ulangi,Setiap Hari,Setiap Minggu,Setiap Bulan,Setiap Tahun',
            'is_private' => 'boolean',
            'is_online_meeting' => 'boolean',
            'meeting_link' => 'nullable|url',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // Buat event
            $event = CalendarEvent::create([
                'workspace_id' => $workspaceId,
                'created_by' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $validated['start_datetime'],
                'end_datetime' => $validated['end_datetime'],
                'recurrence' => ($validated['recurrence'] ?? 'Jangan Ulangi') === 'Jangan Ulangi' ? null : $validated['recurrence'],
                'is_private' => $validated['is_private'] ?? false,
                'is_online_meeting' => $validated['is_online_meeting'] ?? false,
                'meeting_link' => $validated['meeting_link'] ?? null,
            ]);

            // Tambahkan creator sebagai participant dengan status accepted
            CalendarParticipant::create([
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'status' => 'accepted',
            ]);

            // Tambahkan participants jika ada
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
                ->with('success', 'Event berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat event: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form edit event
     */
    public function edit($workspaceId, $id)
    {
        $event = CalendarEvent::with('participants')->findOrFail($id);

        // Cek apakah user adalah creator
        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit event ini');
        }

        // ✅ FIX: Load members dengan cara manual untuk menghindari ambiguous column
        $workspace = \App\Models\Workspace::findOrFail($workspaceId);

        // Load userWorkspaces yang aktif dengan user-nya
        $workspace->load(['userWorkspaces' => function ($query) {
            $query->where('status_active', true)->with('user');
        }]);

        // Convert userWorkspaces to collection of users
        $activeUsers = $workspace->userWorkspaces->pluck('user')->filter();

        // Jika tidak ada members, ambil users dari company
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

        // Cek apakah user adalah creator
        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate event ini');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'recurrence' => 'nullable|string|in:Jangan Ulangi,Setiap Hari,Setiap Minggu,Setiap Bulan,Setiap Tahun',
            'is_private' => 'boolean',
            'is_online_meeting' => 'boolean',
            'meeting_link' => 'nullable|url',
            'participants' => 'nullable|array',
            'participants.*' => 'uuid|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // Update event
            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'start_datetime' => $validated['start_datetime'],
                'end_datetime' => $validated['end_datetime'],
                'recurrence' => ($validated['recurrence'] ?? 'Jangan Ulangi') === 'Jangan Ulangi' ? null : $validated['recurrence'],
                'is_private' => $validated['is_private'] ?? false,
                'is_online_meeting' => $validated['is_online_meeting'] ?? false,
                'meeting_link' => $validated['meeting_link'] ?? null,
            ]);

            // Update participants
            if (isset($validated['participants'])) {
                // Hapus participants yang tidak ada di list baru (kecuali creator)
                CalendarParticipant::where('event_id', $event->id)
                    ->where('user_id', '!=', $event->created_by)
                    ->delete();

                // Tambahkan participants baru
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
                ->with('success', 'Event berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate event: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete event
     */
    public function destroy($workspaceId, $id)
    {
        $event = CalendarEvent::where('workspace_id', $workspaceId)->findOrFail($id);

        // Cek apakah user adalah creator
        if ($event->created_by !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus event ini');
        }

        $event->update(['deleted_at' => now()]);

        return redirect()
            ->route('jadwal', ['workspaceId' => $workspaceId])
            ->with('success', 'Event berhasil dihapus');
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

    /**
     * Get events untuk API (untuk FullCalendar)
     */
    public function getEvents(Request $request, $workspaceId)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $user = Auth::user();

        $query = CalendarEvent::where('workspace_id', $workspaceId)
            ->whereNull('deleted_at')
            ->with(['creator', 'participants.user']);

        // Filter by date range jika ada
        if ($start && $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                    ->orWhereBetween('end_datetime', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_datetime', '<=', $start)
                            ->where('end_datetime', '>=', $end);
                    });
            });
        }

        // ✅ Filter berdasarkan access: hanya tampilkan public events atau events dimana user adalah participant
        $query->where(function ($q) use ($user) {
            $q->where('is_private', false)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('participants', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id);
                });
        });

        $events = $query->get();

        // Format untuk FullCalendar
        return response()->json($events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_datetime,
                'end' => $event->end_datetime,
                'backgroundColor' => $event->is_private ? '#ef4444' : '#2563eb',
                'borderColor' => $event->is_private ? '#ef4444' : '#2563eb',
                'extendedProps' => [
                    'description' => $event->description,
                    'is_online' => $event->is_online_meeting,
                    'meeting_link' => $event->meeting_link,
                    'participants_count' => $event->participants->count(),
                    'creator_name' => $event->creator->full_name,
                    'is_creator' => $event->created_by === Auth::id(),
                ]
            ];
        }));
    }
}
