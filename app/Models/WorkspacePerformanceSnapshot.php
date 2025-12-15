<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WorkspacePerformanceSnapshot extends Model
{
    use HasUuids;

    protected $table = 'workspace_performance_snapshots';

    protected $fillable = [
        'workspace_id',
        'period_start',
        'period_end',
        'period_type',
        'metrics',
        'performance_score',
        'quality_score',
        'risk_score',
        'suggestions',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'metrics' => 'array',
        'suggestions' => 'array',
        'performance_score' => 'integer',
        'quality_score' => 'integer',
        'risk_score' => 'integer',
    ];

    // Relationship
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    // Helper: Check if snapshot exists
    public static function exists($workspaceId, $periodStart, $periodEnd)
    {
        return self::where('workspace_id', $workspaceId)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->exists();
    }

    // Helper: Get latest snapshot
    public static function getLatest($workspaceId, $periodStart, $periodEnd)
    {
        return self::where('workspace_id', $workspaceId)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->latest()
            ->first();
    }
}