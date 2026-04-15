<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'definition',
        'type',
        'deadline_hours',
        'auto_route_next',
        'require_comments',
        'send_reminder_hours',
        'allow_rejection',
        'is_active',
    ];

    protected $casts = [
        'definition' => 'array',
        'auto_route_next' => 'boolean',
        'require_comments' => 'boolean',
        'allow_rejection' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    // Template methods
    public function applySingleApproverTemplate(int $approverId): void
    {
        $this->update([
            'type' => 'single',
            'definition' => [
                'steps' => [
                    [
                        'sequence' => 1,
                        'approvers' => [$approverId],
                        'parallel' => false,
                    ]
                ]
            ]
        ]);
    }

    public function applySequentialTemplate(array $approverIds): void
    {
        $steps = [];
        foreach ($approverIds as $index => $approverId) {
            $steps[] = [
                'sequence' => $index + 1,
                'approvers' => [$approverId],
                'parallel' => false,
            ];
        }

        $this->update([
            'type' => 'sequential',
            'definition' => ['steps' => $steps]
        ]);
    }

    public function applyParallelTemplate(array $approverIds): void
    {
        $this->update([
            'type' => 'parallel',
            'definition' => [
                'steps' => [
                    [
                        'sequence' => 1,
                        'approvers' => $approverIds,
                        'parallel' => true,
                        'require_all' => true,
                    ]
                ]
            ]
        ]);
    }

    public function applyCustomTemplate(array $steps): void
    {
        $this->update([
            'type' => 'custom',
            'definition' => ['steps' => $steps]
        ]);
    }

    // Helper methods
    public function getSteps(): array
    {
        return $this->definition['steps'] ?? [];
    }

    public function getApproversForStep(int $stepSequence): array
    {
        $steps = $this->getSteps();
        foreach ($steps as $step) {
            // Check for both 'sequence' and 'order' for backward compatibility
            $stepSeq = $step['sequence'] ?? $step['order'] ?? null;
            if ($stepSeq === $stepSequence) {
                return $step['approvers'] ?? [];
            }
        }
        return [];
    }

    public function isStepParallel(int $stepSequence): bool
    {
        $steps = $this->getSteps();
        foreach ($steps as $step) {
            // Check for both 'sequence' and 'order' for backward compatibility
            $stepSeq = $step['sequence'] ?? $step['order'] ?? null;
            if ($stepSeq === $stepSequence) {
                return $step['parallel'] ?? false;
            }
        }
        return false;
    }

    public function doesStepRequireAll(int $stepSequence): bool
    {
        $steps = $this->getSteps();
        foreach ($steps as $step) {
            // Check for both 'sequence' and 'order' for backward compatibility
            $stepSeq = $step['sequence'] ?? $step['order'] ?? null;
            if ($stepSeq === $stepSequence) {
                return $step['require_all'] ?? false;
            }
        }
        return false;
    }

    public function getTotalSteps(): int
    {
        return count($this->getSteps());
    }

    public function getNextStepSequence(int $currentSequence): ?int
    {
        $steps = $this->getSteps();
        foreach ($steps as $step) {
            // Check for both 'sequence' and 'order' for backward compatibility
            $stepSeq = $step['sequence'] ?? $step['order'] ?? null;
            if ($stepSeq > $currentSequence) {
                return $stepSeq;
            }
        }
        return null;
    }

    public function getDeadline(): ?\Carbon\Carbon
    {
        if (!$this->deadline_hours) {
            return null;
        }
        return now()->addHours($this->deadline_hours);
    }
}
