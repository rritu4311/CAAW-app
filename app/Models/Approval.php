<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'workflow_id',
        'assigned_to',
        'status',
        'decision_reason',
        'decided_at',
        'decided_by',
        'order',
        'deadline_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'deadline_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($approval) {
            // Call updateAssetStatus when approval status changes
            if ($approval->isDirty('status')) {
                $approval->updateAssetStatus();
            }
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    // Approval decision methods
    public function approve(int $userId): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'approved';
        $this->decided_at = now();
        $this->decided_by = $userId;
        $this->save();

        // Update asset status based on workflow type
        $this->updateAssetStatus();

        // Notify asset uploader about approval
        $approver = User::find($userId);
        if ($this->asset->uploadedBy && $this->asset->uploadedBy->id !== $userId) {
            $this->asset->uploadedBy->notify(new \App\Notifications\AssetApproved($this->asset, $this, $approver));
        }

        return true;
    }

    public function reject(int $userId, string $reason): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'rejected';
        $this->decision_reason = $reason;
        $this->decided_at = now();
        $this->decided_by = $userId;
        $this->save();

        // Revert asset to draft
        $this->asset->update(['status' => 'draft']);

        return true;
    }

    public function requestChanges(int $userId, string $comments): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'changes_requested';
        $this->decision_reason = $comments;
        $this->decided_at = now();
        $this->decided_by = $userId;
        $this->save();

        // Revert asset to draft but retain comments
        $this->asset->update(['status' => 'draft']);

        // Notify asset uploader about requested changes
        $requester = User::find($userId);
        if ($this->asset->uploadedBy && $this->asset->uploadedBy->id !== $userId) {
            $this->asset->uploadedBy->notify(new \App\Notifications\AssetChangesRequested($this->asset, $this, $requester));
        }

        return true;
    }

    private function updateAssetStatus(): void
    {
        $asset = $this->asset;
        $workflow = $this->workflow;

        \Log::info('updateAssetStatus called', [
            'approval_id' => $this->id,
            'asset_id' => $asset->id,
            'workflow_id' => $workflow ? $workflow->id : null,
            'workflow_type' => $workflow ? $workflow->type : 'no workflow',
            'approval_status' => $this->status
        ]);

        if (!$workflow) {
            // Fallback to simple logic if no workflow
            \Log::info('updateAssetStatus: no workflow, using simpleUpdateAssetStatus');
            $this->simpleUpdateAssetStatus($asset);
            return;
        }

        $workflowType = $workflow->type;

        if ($workflowType === 'parallel') {
            // Parallel: All approvers in the step must approve
            \Log::info('updateAssetStatus: workflow type is parallel, calling handleParallelApproval');
            $this->handleParallelApproval($asset, $workflow);
        } elseif ($workflowType === 'sequential') {
            // Sequential: Activate next step when current step is complete
            \Log::info('updateAssetStatus: workflow type is sequential, calling handleSequentialApproval');
            $this->handleSequentialApproval($asset, $workflow);
        } else {
            // Single or custom: use simple logic
            \Log::info('updateAssetStatus: workflow type is single/custom, using simpleUpdateAssetStatus', ['type' => $workflowType]);
            $this->simpleUpdateAssetStatus($asset);
        }
    }

    private function handleParallelApproval(Asset $asset, Workflow $workflow): void
    {
        \Log::info('handleParallelApproval called', [
            'asset_id' => $asset->id,
            'workflow_id' => $workflow->id,
            'workflow_type' => $workflow->type
        ]);

        // For parallel, all approvers are in sequence 1
        $currentSequence = 1;
        $stepApprovers = $workflow->getApproversForStep($currentSequence);
        $stepApprovals = $asset->approvals()
            ->whereIn('assigned_to', $stepApprovers)
            ->get();

        $approvedCount = $stepApprovals->where('status', 'approved')->count();
        $rejectedCount = $stepApprovals->where('status', 'rejected')->count();
        $changesRequestedCount = $stepApprovals->where('status', 'changes_requested')->count();

        \Log::info('handleParallelApproval: approval counts', [
            'step_approvers' => $stepApprovers,
            'step_approvals_count' => $stepApprovals->count(),
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'changes_requested_count' => $changesRequestedCount
        ]);

        // Check if step requires all approvals
        $requireAll = $workflow->doesStepRequireAll($currentSequence);
        \Log::info('handleParallelApproval: requireAll', ['require_all' => $requireAll]);

        if ($requireAll) {
            // All approvers must approve
            if ($approvedCount === count($stepApprovers)) {
                // All approved
                \Log::info('handleParallelApproval: all approved, setting status to approved');
                $asset->update(['status' => 'approved']);
            } elseif ($rejectedCount > 0 || $changesRequestedCount > 0) {
                // Any rejection or changes requested - immediate effect, independent of other approvers
                \Log::info('handleParallelApproval: rejection or changes requested, setting status to draft');
                $asset->update(['status' => 'draft']);
            } else {
                // Still waiting for approvals
                \Log::info('handleParallelApproval: still waiting for approvals, keeping status as pending_approval');
                $asset->update(['status' => 'pending_approval']);
            }
        }
    }

    private function handleSequentialApproval(Asset $asset, Workflow $workflow): void
    {
        \Log::info('handleSequentialApproval called', [
            'user_id' => $this->assigned_to,
            'asset_id' => $asset->id,
            'workflow_id' => $workflow->id,
            'workflow_type' => $workflow->type
        ]);

        // For sequential, find which step this approval belongs to by matching assigned user
        $steps = $workflow->getSteps();
        $currentStepSequence = null;

        foreach ($steps as $step) {
            if (in_array($this->assigned_to, $step['approvers'])) {
                // Check for both 'sequence' and 'order' for backward compatibility
                $currentStepSequence = $step['sequence'] ?? $step['order'] ?? null;
                \Log::info('handleSequentialApproval: found step', [
                    'current_step_sequence' => $currentStepSequence
                ]);
                break;
            }
        }

        if ($currentStepSequence === null) {
            // Should not happen, but fallback
            \Log::info('handleSequentialApproval: currentStepSequence is null, using fallback');
            $this->simpleUpdateAssetStatus($asset);
            return;
        }

        // Get all approvals for the current step
        $stepApprovers = $workflow->getApproversForStep($currentStepSequence);
        $stepApprovals = $asset->approvals()
            ->whereIn('assigned_to', $stepApprovers)
            ->get();

        $approvedCount = $stepApprovals->where('status', 'approved')->count();
        $rejectedCount = $stepApprovals->where('status', 'rejected')->count();
        $changesRequestedCount = $stepApprovals->where('status', 'changes_requested')->count();

        \Log::info('handleSequentialApproval: step approval counts', [
            'current_step_sequence' => $currentStepSequence,
            'step_approvers' => $stepApprovers,
            'step_approvals_count' => $stepApprovals->count(),
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'changes_requested_count' => $changesRequestedCount
        ]);

        if ($rejectedCount > 0 || $changesRequestedCount > 0) {
            // Step failed
            \Log::info('handleSequentialApproval: step failed, setting status to draft');
            $asset->update(['status' => 'draft']);
            return;
        }

        if ($approvedCount === $stepApprovals->count()) {
            // Current step complete, activate next step
            $nextSequence = $workflow->getNextStepSequence($currentStepSequence);

            \Log::info('handleSequentialApproval: step complete', [
                'current_step_sequence' => $currentStepSequence,
                'next_sequence' => $nextSequence
            ]);

            if ($nextSequence !== null) {
                // Get next step's approvers and ensure their approvals are pending
                $nextStepApprovers = $workflow->getApproversForStep($nextSequence);
                // Ensure asset status remains pending_approval for next step
                \Log::info('handleSequentialApproval: setting status to pending_approval for next step');
                $asset->update(['status' => 'pending_approval']);
            } else {
                // No more steps, asset approved
                \Log::info('handleSequentialApproval: no more steps, setting status to approved');
                $asset->update(['status' => 'approved']);
            }
        }
    }

    private function simpleUpdateAssetStatus(Asset $asset): void
    {
        $pendingApprovals = $asset->pendingApprovals()->count();

        if ($pendingApprovals === 0) {
            // All approvals completed, check if all were approved
            $rejectedCount = $asset->approvals()->where('status', 'rejected')->count();
            $changesRequestedCount = $asset->approvals()->where('status', 'changes_requested')->count();

            if ($rejectedCount > 0 || $changesRequestedCount > 0) {
                $asset->update(['status' => 'draft']);
            } else {
                $asset->update(['status' => 'approved']);
            }
        }
    }

    // Status helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasChangesRequested(): bool
    {
        return $this->status === 'changes_requested';
    }

    public function isDecided(): bool
    {
        return in_array($this->status, ['approved', 'rejected', 'changes_requested']);
    }

    // Check if this approval is the current active step in a sequential workflow
    public function isCurrentSequentialStep(): bool
    {
        \Log::info('isCurrentSequentialStep called', [
            'approval_id' => $this->id,
            'user_id' => $this->assigned_to,
            'asset_id' => $this->asset->id,
            'workflow_id' => $this->workflow ? $this->workflow->id : null,
            'workflow_type' => $this->workflow ? $this->workflow->type : 'no workflow',
            'approval_status' => $this->status
        ]);

        if (!$this->workflow || $this->workflow->type !== 'sequential') {
            \Log::info('isCurrentSequentialStep: not sequential workflow, returning true');
            return true; // Not sequential, so always current
        }

        // If no approvals exist for this asset yet, first step is always current
        $allApprovals = $this->asset->approvals()->count();
        if ($allApprovals === 0) {
            \Log::info('isCurrentSequentialStep: no approvals exist, returning true');
            return true;
        }

        $steps = $this->workflow->getSteps();
        $currentSequence = null;

        // Find the sequence of this approval's step
        foreach ($steps as $step) {
            if (in_array($this->assigned_to, $step['approvers'])) {
                // Check for both 'sequence' and 'order' for backward compatibility
                $currentSequence = $step['sequence'] ?? $step['order'] ?? null;
                \Log::info('isCurrentSequentialStep: found step', [
                    'current_sequence' => $currentSequence,
                    'step_approvers' => $step['approvers']
                ]);
                break;
            }
        }

        if ($currentSequence === null) {
            \Log::info('isCurrentSequentialStep: currentSequence is null, returning true');
            return true; // Should not happen, but fallback
        }

        // First step is always current
        if ($currentSequence === 1) {
            \Log::info('isCurrentSequentialStep: first step, returning true');
            return true;
        }

        \Log::info('isCurrentSequentialStep: checking previous steps', [
            'current_sequence' => $currentSequence
        ]);

        // Check if all previous steps are complete
        for ($i = 1; $i < $currentSequence; $i++) {
            $previousStepApprovers = $this->workflow->getApproversForStep($i);
            $previousStepApprovals = $this->asset->approvals()
                ->whereIn('assigned_to', $previousStepApprovers)
                ->get();

            \Log::info('isCurrentSequentialStep: checking previous step', [
                'step_i' => $i,
                'previous_approvers' => $previousStepApprovers,
                'previous_approvals_count' => $previousStepApprovals->count()
            ]);

            // If there are no approvals for previous step, it's not complete
            if ($previousStepApprovals->isEmpty()) {
                \Log::info('isCurrentSequentialStep: previous step has no approvals, returning false');
                return false;
            }

            // Check if all approvals in previous step are approved
            $allApproved = $previousStepApprovals->every(function ($approval) {
                return $approval->status === 'approved';
            });

            if (!$allApproved) {
                \Log::info('isCurrentSequentialStep: previous step not all approved, returning false');
                return false; // Previous step not complete
            }
        }

        \Log::info('isCurrentSequentialStep: all previous steps complete, returning true');
        return true; // All previous steps complete, this is current
    }

    // Check if user can see/act on this approval
    public function canUserAct(int $userId): bool
    {
        \Log::info('canUserAct called', [
            'approval_id' => $this->id,
            'user_id' => $userId,
            'assigned_to' => $this->assigned_to,
            'approval_status' => $this->status,
            'asset_id' => $this->asset->id,
            'asset_status' => $this->asset->status,
            'workflow_type' => $this->workflow ? $this->workflow->type : 'no workflow'
        ]);

        // User must be assigned to this approval
        if ($this->assigned_to !== $userId) {
            \Log::info('canUserAct: user not assigned to this approval');
            return false;
        }

        // Approval must be pending
        if (!$this->isPending()) {
            \Log::info('canUserAct: approval is not pending');
            return false;
        }

        // For sequential workflows, check if this is the current step
        if ($this->workflow && $this->workflow->type === 'sequential') {
            $isCurrent = $this->isCurrentSequentialStep();
            \Log::info('canUserAct: sequential workflow, checking isCurrentSequentialStep', ['is_current' => $isCurrent]);
            return $isCurrent;
        }

        // For single template workflows, only the specific approver in the workflow definition can act
        if ($this->workflow && $this->workflow->type === 'single') {
            $definition = $this->workflow->definition;
            $singleApproverId = $definition['steps'][0]['approvers'][0] ?? null;
            $isSingleApprover = ($singleApproverId === $userId);
            \Log::info('canUserAct: single workflow, checking if user is the specific approver', [
                'single_approver_id' => $singleApproverId,
                'is_single_approver' => $isSingleApprover
            ]);
            return $isSingleApprover;
        }

        \Log::info('canUserAct: parallel workflow, allowing action');
        return true; // Parallel workflow - any approver can act
    }
}
