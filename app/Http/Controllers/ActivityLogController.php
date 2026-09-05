<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Task;
use App\Models\File;
use App\Models\UserWorkspace;
use App\Models\Decision;


class ActivityLogController extends Controller
{
    public function index(Workspace $workspace)
    {
        // ===== 1. TASK ACTIVITIES =====
        $tasks = Task::where('workspace_id', $workspace->id)
            ->with('creator')
            ->latest('created_at')
            ->get();

        $taskActivities = $tasks->map(function ($task) {
            $creatorName = $task->creator ? $task->creator->full_name : 'Seseorang';
            $priority = strtolower($task->priority ?? 'low');

            return [
                'type' => 'task',
                'color' => 'blue',
                'icon' => 'check',
                'title' => 'Tugas Baru Dibuat',
                'creator' => $creatorName,
                'task_title' => $task->title,
                'priority' => $priority,
                'file_name' => null,
                'file_type' => null,
                'folder_name' => null,
                'time' => $task->created_at->diffForHumans(),
                'timestamp' => $task->created_at->timestamp,
            ];
        });

        // ===== 2. FILE UPLOAD ACTIVITIES =====
        $files = File::where('workspace_id', $workspace->id)
            ->with('uploader', 'folder')
            ->whereNotNull('uploaded_at')
            ->latest('uploaded_at')
            ->get();

        $fileActivities = $files->map(function ($file) {
            $uploaderName = $file->uploader ? $file->uploader->full_name : 'Seseorang';

            return [
                'type' => 'file',
                'color' => 'green',
                'icon' => 'upload',
                'title' => 'File Diupload',
                'creator' => $uploaderName,
                'task_title' => null,
                'priority' => null,
                'file_name' => $file->file_name,
                'file_type' => $file->getFileTypeCategory(),
                'folder_name' => $file->folder ? $file->folder->name : null,
                'time' => $file->uploaded_at->diffForHumans(),
                'timestamp' => $file->uploaded_at->timestamp,
            ];
        });

        // ===== 3. MEMBER JOIN ACTIVITIES =====
        $members = UserWorkspace::where('workspace_id', $workspace->id)
            ->with('user')
            ->where('status_active', true)
            ->get();

        $memberActivities = $members->map(function ($member) {
            $memberName = $member->user ? $member->user->full_name : 'Seseorang';
            $time = $member->join_date ? $member->join_date->diffForHumans() : 'Baru-baru ini';
            $timestamp = $member->join_date ? $member->join_date->timestamp : 0;

            return [
                'type' => 'member',
                'color' => 'red',
                'icon' => 'user',
                'title' => 'Member Diundang',
                'creator' => $memberName,
                'task_title' => null,
                'priority' => null,
                'file_name' => null,
                'file_type' => null,
                'folder_name' => null,
                'time' => $time,
                'timestamp' => $timestamp,
            ];
        });

        // ===== 4. TASK MOVEMENT ACTIVITIES =====
        $taskMovements = \App\Models\TaskActivity::where('workspace_id', $workspace->id)
            ->with('user')
            ->latest('created_at')
            ->get();

        $movementActivities = $taskMovements->map(function ($activity) {
            $actorName = $activity->user ? $activity->user->full_name : 'Seseorang';

            return [
                'type' => 'task_moved',
                'color' => 'purple',
                'icon' => 'calendar',
                'title' => 'Tugas Dipindahkan',
                'creator' => $actorName,
                'task_title' => $activity->task_title,
                'priority' => null,
                'file_name' => null,
                'file_type' => null,
                'folder_name' => null,
                'old_column' => $activity->old_column,
                'new_column' => $activity->new_column,
                'time' => $activity->created_at->diffForHumans(),
                'timestamp' => $activity->created_at->timestamp,
            ];
        });

        // ===== 5. DECISION ACTIVITIES =====
        $decisions = Decision::with('evidenceFile', 'creator')
            ->where('workspace_id', $workspace->id)
            ->orderByDesc('decision_date')
            ->get();

        $decisionActivities = $decisions->map(function ($decision) {
            $creatorName = $decision->creator ? $decision->creator->full_name : 'Seseorang';
            $time = $decision->created_at ? $decision->created_at->diffForHumans() : 'Baru-baru ini';
            $timestamp = $decision->created_at ? $decision->created_at->timestamp : 0;

            return [
                'type' => 'decision',
                'color' => 'indigo',
                'icon' => 'gavel',
                'title' => 'Keputusan Baru Dibuat',
                'creator' => $creatorName,
                'task_title' => $decision->title,
                'priority' => null,
                'file_name' => null,
                'file_type' => null,
                'folder_name' => null,
                'time' => $time,
                'timestamp' => $timestamp,
                'desc' => $decision->description,
            ];
        });

        // ===== 6. AI PROCESSING LOGS =====
        $aiProcessingLogs = \App\Models\AIProcessingLog::where('workspace_id', $workspace->id)
            ->with('user')
            ->latest()
            ->get();

        // ===== 7. MERGE & SORT =====
        $sortedActivities = $taskActivities
            ->concat($fileActivities)
            ->concat($memberActivities)
            ->concat($movementActivities)
            ->concat($decisionActivities)
            ->sortByDesc('timestamp')
            ->values()
            ->map(function ($item, $index) {
                $item['side'] = $index % 2 === 0 ? 'right' : 'left';
                return $item;
            });

        return view('activity-log', compact('workspace', 'sortedActivities', 'decisions', 'aiProcessingLogs'));
    }
}
