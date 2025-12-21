<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\Notification;
use App\Models\UserCompany;
use App\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Get users with SuperAdmin, Administrator, Manager roles in company
     * EXCLUDING the actor (person who did the action)
     */
    public function getCompanyAdmins($companyId, $excludeUserId = null): array
    {
        $query = User::whereHas('userCompanies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId)
                ->whereHas('role', function ($roleQuery) {
                    $roleQuery->whereIn('name', ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
                });
        });

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->pluck('id')->toArray();
    }

    /**
     * Get all members in a workspace (untuk chat workspace)
     */
    public function getWorkspaceMembers($workspaceId): array
    {
        $workspace = \App\Models\Workspace::with('users')->find($workspaceId);
        if (!$workspace) {
            Log::warning("Workspace not found: {$workspaceId}");
            return [];
        }
        
        // ✅ FIX: Gunakan relationship users, bukan UserWorkspace
        $members = $workspace->users()
            ->wherePivot('status_active', true)
            ->pluck('users.id')
            ->toArray();
        
        Log::info("Workspace {$workspaceId} members: " . json_encode($members));
        
        return $members;
    }

    /**
     * Get all members in a company (untuk chat perusahaan)
     */
    public function getCompanyMembers($companyId): array
    {
        return User::whereHas('userCompanies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId)
                ->whereNull('deleted_at');
        })->pluck('id')->toArray();
    }

    /**
     * Send notification to specific user
     */
    public function send(array $data)
    {
        try {
            $notification = Notification::create([
                'user_id' => $data['user_id'],
                'company_id' => $data['company_id'],
                'workspace_id' => $data['workspace_id'] ?? null,
                'type' => $data['type'],
                'title' => $data['title'],
                'message' => $data['message'],
                'context' => $data['context'] ?? null,
                'notifiable_type' => $data['notifiable_type'],
                'notifiable_id' => $data['notifiable_id'],
                'actor_id' => $data['actor_id'] ?? null,
                'action_url' => $data['action_url'] ?? null,
            ]);

            // Broadcast notification via WebSocket
            broadcast(new NotificationSent($notification))->toOthers();

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send bulk notifications
     */
    public function sendBulk(array $userIds, array $data)
    {
        $notifications = [];

        foreach ($userIds as $userId) {
            $notificationData = array_merge($data, ['user_id' => $userId]);
            $notification = $this->send($notificationData);
            
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    /**
     * ========================================
     * CHAT NOTIFICATIONS
     * ========================================
     * 
     * Rules:
     * - Chat Perusahaan Group: Semua anggota perusahaan (kecuali pengirim)
     * - Chat Workspace Group: Semua anggota workspace (kecuali pengirim)
     * - Chat Personal (Private): HANYA penerima 1-on-1 (tidak termasuk admin!)
     */
    public function notifyNewMessage($message)
    {
        $conversation = $message->conversation;
        $sender = $message->sender;
        $recipients = [];

        Log::info("=== CHAT NOTIFICATION DEBUG ===");
        Log::info("Conversation ID: {$conversation->id}");
        Log::info("Conversation Type: {$conversation->type}");
        Log::info("Conversation Scope: " . ($conversation->scope ?? 'workspace'));
        Log::info("Conversation Company ID: " . ($conversation->company_id ?? 'NULL'));
        Log::info("Conversation Workspace ID: " . ($conversation->workspace_id ?? 'NULL'));
        Log::info("Sender ID: {$sender->id}");

        // 🔥 FIX: Dapatkan company_id dari workspace jika conversation tidak punya company_id
        $companyId = $conversation->company_id;
        if (!$companyId && $conversation->workspace_id) {
            $workspace = \App\Models\Workspace::find($conversation->workspace_id);
            if ($workspace) {
                $companyId = $workspace->company_id;
                Log::info("Company ID dari workspace: {$companyId}");
            }
        }

        if (!$companyId) {
            Log::error("Cannot determine company_id for conversation {$conversation->id}");
            return [];
        }

        // Determine recipients based on conversation type
        if ($conversation->type === 'private') {
            // ✅ Private chat - ONLY the other person (1-on-1)
            // Get all participants EXCEPT sender
            $allParticipants = $conversation->participants()
                ->pluck('user_id')
                ->toArray();
            
            Log::info("All participants in private chat: " . json_encode($allParticipants));
            
            $recipients = array_filter($allParticipants, fn($id) => $id !== $sender->id);
            
            Log::info("Recipients after filtering sender: " . json_encode($recipients));
        } else {
            // Group chat
            if ($conversation->scope === 'company') {
                // ✅ Company group chat - ALL company members except sender
                Log::info("Processing company group chat for company: {$conversation->company_id}");
                $recipients = $this->getCompanyMembers($conversation->company_id);
            } else {
                // ✅ Workspace group chat - ALL workspace members except sender
                Log::info("Processing workspace group chat for workspace: {$conversation->workspace_id}");
                $recipients = $this->getWorkspaceMembers($conversation->workspace_id);
            }
            
            Log::info("Recipients before filtering sender: " . json_encode($recipients));
            
            // Remove sender from recipients
            $recipients = array_filter($recipients, fn($id) => $id !== $sender->id);
            
            Log::info("Recipients after filtering sender: " . json_encode($recipients));
        }

        // Re-index array untuk menghindari masalah dengan array_filter
        $recipients = array_values($recipients);

        // Jika tidak ada recipients, jangan kirim notifikasi
        if (empty($recipients)) {
            Log::warning("No recipients for notification - conversation: {$conversation->id}, type: {$conversation->type}");
            return [];
        }

        Log::info("Final recipients count: " . count($recipients));
        Log::info("Final recipients: " . json_encode($recipients));

        // Prepare notification data
        $notificationData = [
            'company_id' => $companyId,  // 🔥 FIX: Gunakan companyId yang sudah dicek
            'workspace_id' => $conversation->workspace_id,
            'type' => 'chat',
            'title' => 'Pesan baru dari ' . $sender->full_name,
            'message' => $this->truncateMessage($message->content, 100),
            'context' => $this->getConversationContext($conversation),
            'notifiable_type' => get_class($message),
            'notifiable_id' => $message->id,
            'actor_id' => $sender->id,
            'action_url' => $this->getChatActionUrl($conversation),
        ];

        Log::info("Notification data prepared with company_id: {$companyId}");
        Log::info("Sending notifications to " . count($recipients) . " users");

        return $this->sendBulk($recipients, $notificationData);
    }

    /**
     * ========================================
     * TASK NOTIFICATIONS
     * ========================================
     * 
     * Rules:
     * - Admins (SuperAdmin, Administrator, Manager) EXCEPT creator
     * - Assigned members
     */
    public function notifyTaskCreated($task)
    {
        $creator = $task->creator;
        $workspace = $task->workspace;

        // Get company admins (excluding creator)
        $companyAdmins = $this->getCompanyAdmins($workspace->company_id, $creator->id);
        
        // Get assigned members
        $assignedMembers = $task->assignments->pluck('user_id')->toArray();
        
        // Merge and remove duplicates
        $recipients = array_unique(array_merge($companyAdmins, $assignedMembers));
        
        // Ensure creator is not in recipients
        $recipients = array_filter($recipients, fn($id) => $id !== $creator->id);

        $notificationData = [
            'company_id' => $workspace->company_id,
            'workspace_id' => $workspace->id,
            'type' => 'task',
            'title' => 'Tugas baru ditugaskan',
            'message' => $task->title,
            'context' => ($task->phase ?? 'Belum ada fase') . ' · ' . $workspace->name,
            'notifiable_type' => get_class($task),
            'notifiable_id' => $task->id,
            'actor_id' => $creator->id,
            'action_url' => route('kanban-tugas', ['workspace' => $workspace->id]) . '?task=' . $task->id,
        ];

        return $this->sendBulk($recipients, $notificationData);
    }

    /**
     * Task Update Notification
     * 
     * Rules:
     * - Admins (SuperAdmin, Administrator, Manager) EXCEPT updater
     * - Assigned members
     * - Task creator
     */
    public function notifyTaskUpdated($task, $updater)
    {
        $workspace = $task->workspace;

        // Get company admins (excluding updater)
        $companyAdmins = $this->getCompanyAdmins($workspace->company_id, $updater->id);
        
        // Get assigned members + creator
        $assignedMembers = $task->assignments->pluck('user_id')->toArray();
        $recipients = array_unique(array_merge($companyAdmins, $assignedMembers, [$task->created_by]));
        
        // Ensure updater is not in recipients
        $recipients = array_filter($recipients, fn($id) => $id !== $updater->id);

        $notificationData = [
            'company_id' => $workspace->company_id,
            'workspace_id' => $workspace->id,
            'type' => 'task',
            'title' => 'Tugas diperbarui',
            'message' => $task->title . ' telah diperbarui',
            'context' => ($task->phase ?? 'Belum ada fase') . ' · ' . $workspace->name,
            'notifiable_type' => get_class($task),
            'notifiable_id' => $task->id,
            'actor_id' => $updater->id,
            'action_url' => route('kanban-tugas', ['workspace' => $workspace->id]) . '?task=' . $task->id,
        ];

        return $this->sendBulk($recipients, $notificationData);
    }

    /**
     * ========================================
     * ANNOUNCEMENT NOTIFICATIONS
     * ========================================
     * 
     * Rules:
     * - Admins (SuperAdmin, Administrator, Manager) EXCEPT creator
     * - Specified recipients (if private)
     */
    public function notifyAnnouncementCreated($announcement)
    {
        $creator = $announcement->creator;
        
        if ($announcement->workspace_id) {
            // Workspace announcement
            $workspace = $announcement->workspace;
            
            // Get company admins (excluding creator)
            $companyAdmins = $this->getCompanyAdmins($workspace->company_id, $creator->id);
            
            if ($announcement->is_private) {
                // ✅ Private announcement - admins + specified recipients
                $specifiedRecipients = $announcement->recipients->pluck('user_id')->toArray();
                $recipients = array_unique(array_merge($companyAdmins, $specifiedRecipients));
            } else {
                // ✅ Public announcement - admins only
                $recipients = $companyAdmins;
            }

            $context = $workspace->name;
            $actionUrl = route('pengumuman.show', ['workspace' => $workspace->id, 'pengumuman' => $announcement->id]);
        } else {
            // Company announcement
            $companyAdmins = $this->getCompanyAdmins($announcement->company_id, $creator->id);
            
            if ($announcement->is_private) {
                // ✅ Private - admins + specified recipients
                $specifiedRecipients = $announcement->recipients->pluck('user_id')->toArray() ?? [];
                $recipients = array_unique(array_merge($companyAdmins, $specifiedRecipients));
            } else {
                // ✅ Public - admins only
                $recipients = $companyAdmins;
            }

            $context = 'Pengumuman Perusahaan';
            $actionUrl = route('pengumuman-perusahaan.show', ['company_id' => $announcement->company_id, 'id' => $announcement->id]);
        }

        // Ensure creator is not in recipients
        $recipients = array_filter($recipients, fn($id) => $id !== $creator->id);

        $notificationData = [
            'company_id' => $announcement->company_id,
            'workspace_id' => $announcement->workspace_id,
            'type' => 'announcement',
            'title' => 'Pengumuman baru',
            'message' => $announcement->title,
            'context' => $context,
            'notifiable_type' => get_class($announcement),
            'notifiable_id' => $announcement->id,
            'actor_id' => $creator->id,
            'action_url' => $actionUrl,
        ];

        return $this->sendBulk($recipients, $notificationData);
    }

    /**
     * ========================================
     * SCHEDULE/EVENT NOTIFICATIONS
     * ========================================
     * 
     * Rules:
     * - Admins (SuperAdmin, Administrator, Manager) EXCEPT creator
     * - Participants (invited members)
     */
    public function notifyEventCreated($event)
    {
        $creator = $event->creator;
        
        if ($event->workspace_id) {
            // Workspace event
            $workspace = $event->workspace;
            
            // Get company admins (excluding creator)
            $companyAdmins = $this->getCompanyAdmins($workspace->company_id, $creator->id);
            
            // Get participants
            $participants = $event->participants->pluck('user_id')->toArray();
            
            // Merge admins + participants
            $recipients = array_unique(array_merge($companyAdmins, $participants));

            $context = 'Workspace: ' . $workspace->name;
            $actionUrl = route('calendar.show', ['workspaceId' => $workspace->id, 'id' => $event->id]);
        } else {
            // Company event
            // Get company admins (excluding creator)
            $companyAdmins = $this->getCompanyAdmins($event->company_id, $creator->id);
            
            // Get participants
            $participants = $event->participants->pluck('user_id')->toArray();
            
            // Merge admins + participants
            $recipients = array_unique(array_merge($companyAdmins, $participants));

            $context = 'Jadwal Umum Perusahaan';
            $actionUrl = route('jadwal-umum.show', ['company_id' => $event->company_id, 'id' => $event->id]);
        }

        // Ensure creator is not in recipients
        $recipients = array_filter($recipients, fn($id) => $id !== $creator->id);

        $notificationData = [
            'company_id' => $event->company_id,
            'workspace_id' => $event->workspace_id,
            'type' => 'schedule',
            'title' => 'Jadwal meeting baru',
            'message' => $event->title,
            'context' => $context,
            'notifiable_type' => get_class($event),
            'notifiable_id' => $event->id,
            'actor_id' => $creator->id,
            'action_url' => $actionUrl,
        ];

        return $this->sendBulk($recipients, $notificationData);
    }

    /**
     * Helper: Truncate message
     */
    private function truncateMessage($message, $length = 100)
    {
        if (!$message) {
            return '';
        }
        
        return strlen($message) > $length 
            ? substr($message, 0, $length) . '...' 
            : $message;
    }

    /**
     * Helper: Get conversation context
     */
    private function getConversationContext($conversation)
    {
        if ($conversation->scope === 'company') {
            return 'Chat Perusahaan';
        }

        $type = $conversation->type === 'private' ? 'Chat Pribadi' : 'Grup';
        $workspaceName = $conversation->workspace?->name ?? 'Unknown';
        
        return $type . ' · ' . $workspaceName;
    }

    /**
     * Helper: Get chat action URL
     */
    private function getChatActionUrl($conversation)
    {
        if ($conversation->scope === 'company') {
            return route('company.chat', $conversation->company_id);
        }

        return route('chat', $conversation->workspace_id);
    }
}