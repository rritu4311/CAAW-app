<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Project;
use App\Models\User;
use App\Models\Approval;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkflowController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display workflow builder for a project.
     */
    public function create(Project $project)
    {


        $projectCollaborators = $project->projectCollaborators()->with('user')->get();
        $workspaceMembers = $project->workspace->members()->get();

        return view('workflows.builder', compact('project', 'projectCollaborators', 'workspaceMembers'));
    }

    /**
     * Display workflow edit page.
     */
    public function edit(Workflow $workflow)
    {


        $project = $workflow->project;
        $projectCollaborators = $project->projectCollaborators()->with('user')->get();
        $workspaceMembers = $project->workspace->members()->get();

        return view('workflows.builder', compact('project', 'projectCollaborators', 'workspaceMembers', 'workflow'));
    }

    /**
     * Store a new workflow.
     */
    public function store(Request $request, Project $project): JsonResponse
    {


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,sequential,parallel,custom',
            'definition' => 'required|array',
            'deadline_hours' => 'nullable|integer|min:1',
            'auto_route_next' => 'boolean',
            'require_comments' => 'boolean',
            'send_reminder_hours' => 'nullable|integer|min:1',
            'allow_rejection' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $workflow = $project->workflows()->create($validated);

        return response()->json([
            'message' => 'Workflow created successfully',
            'workflow' => $workflow
        ]);
    }

    /**
     * Update an existing workflow.
     */
    public function update(Request $request, Workflow $workflow): JsonResponse
    {


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,sequential,parallel,custom',
            'definition' => 'required|array',
            'deadline_hours' => 'nullable|integer|min:1',
            'auto_route_next' => 'boolean',
            'require_comments' => 'boolean',
            'send_reminder_hours' => 'nullable|integer|min:1',
            'allow_rejection' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $workflow->update($validated);

        return response()->json([
            'message' => 'Workflow updated successfully',
            'workflow' => $workflow
        ]);
    }

    /**
     * Apply a template to a workflow.
     */
    public function applyTemplate(Request $request, Workflow $workflow): JsonResponse
    {
       

        $validated = $request->validate([
            'template' => 'required|in:single,sequential,parallel,custom',
            'approvers' => 'required|array',
            'steps' => 'nullable|array',
        ]);

        switch ($validated['template']) {
            case 'single':
                if (count($validated['approvers']) !== 1) {
                    return response()->json(['error' => 'Single approver template requires exactly one approver'], 422);
                }
                $workflow->applySingleApproverTemplate($validated['approvers'][0]);
                break;
            case 'sequential':
                $workflow->applySequentialTemplate($validated['approvers']);
                break;
            case 'parallel':
                $workflow->applyParallelTemplate($validated['approvers']);
                break;
            case 'custom':
                if (empty($validated['steps'])) {
                    return response()->json(['error' => 'Custom template requires steps definition'], 422);
                }
                $workflow->applyCustomTemplate($validated['steps']);
                break;
        }

        return response()->json([
            'message' => 'Template applied successfully',
            'workflow' => $workflow->fresh()
        ]);
    }

    /**
     * Update workflow settings.
     */
    public function updateSettings(Request $request, Workflow $workflow): JsonResponse
    {
        

        $validated = $request->validate([
            'deadline_hours' => 'nullable|integer|min:1',
            'auto_route_next' => 'boolean',
            'require_comments' => 'boolean',
            'send_reminder_hours' => 'nullable|integer|min:1',
            'allow_rejection' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $workflow->update($validated);

        return response()->json([
            'message' => 'Workflow settings updated successfully',
            'workflow' => $workflow->fresh()
        ]);
    }

    /**
     * Start workflow for an asset.
     */
    public function startForAsset(Request $request, Asset $asset): JsonResponse
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
        ]);

        $workflow = Workflow::findOrFail($validated['workflow_id']);

       

        // Delete existing approvals for this asset
        $asset->approvals()->delete();

        // Create approvals based on workflow definition
        $steps = $workflow->getSteps();
        $deadline = $workflow->getDeadline();
        $workflowType = $workflow->type;

        foreach ($steps as $step) {
            foreach ($step['approvers'] as $approverId) {
                // For sequential workflows, don't use order column - sequence is in workflow definition
                // For parallel and single, we can use order column for backward compatibility
                // Check for both 'sequence' and 'order' for backward compatibility
                $stepSequence = $step['sequence'] ?? $step['order'] ?? null;
                $orderValue = ($workflowType === 'sequential') ? null : $stepSequence;

                Approval::create([
                    'asset_id' => $asset->id,
                    'workflow_id' => $workflow->id,
                    'assigned_to' => $approverId,
                    'status' => 'pending',
                    'order' => $orderValue,
                    'deadline_at' => $deadline,
                ]);
            }
        }

        // Update asset status
        $asset->update(['status' => 'pending_approval']);

        return response()->json([
            'message' => 'Workflow started successfully',
            'asset' => $asset->fresh()
        ]);
    }

    /**
     * Display workflow details.
     */
    public function show(Workflow $workflow)
    {


        $workflow->load('approvals.assignedUser');
        $project = $workflow->project;

        return view('workflows.show', compact('workflow', 'project'));
    }

    /**
     * Delete a workflow.
     */
    public function destroy(Workflow $workflow)
    {
       

        $workflow->delete();

        return redirect()->back();
    }

    /**
     * Get available users for workflow approvers.
     */
    public function getAvailableUsers(Project $project): JsonResponse
    {
        
        $collaborators = $project->projectCollaborators()->with('user')->get()->map(function ($collab) {
            return [
                'id' => $collab->user->id,
                'name' => $collab->user->name,
                'email' => $collab->user->email,
                'role' => $collab->role,
            ];
        });

        $workspaceMembers = $project->workspace->members()->get()->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => 'workspace_member',
            ];
        });

        return response()->json([
            'collaborators' => $collaborators,
            'workspace_members' => $workspaceMembers,
        ]);
    }
}
