<?php

namespace App\Console\Commands;

use App\Models\WorkspaceUser;
use App\Models\ProjectCollaborator;
use App\Notifications\AccessShare;
use App\Notifications\ProjectAccessShare;
use App\Models\Workspace;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResendPendingInvitations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitations:resend-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend pending invitations that were sent more than 7 days ago and not yet accepted';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for pending invitations to resend...');

        // Resend workspace invitations
        $workspaceResends = $this->resendWorkspaceInvitations();
        $this->info("Resent {$workspaceResends} workspace invitations");

        // Resend project invitations
        $projectResends = $this->resendProjectInvitations();
        $this->info("Resent {$projectResends} project invitations");

        $this->info('Invitation resend check completed.');
        
        return Command::SUCCESS;
    }

    /**
     * Resend pending workspace invitations sent more than 7 days ago.
     */
    private function resendWorkspaceInvitations(): int
    {
        $count = 0;

        // Get pending workspace invitations created more than 7 days ago
        $pendingWorkspaceUsers = WorkspaceUser::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(7))
            ->with(['workspace', 'user'])
            ->get();

        foreach ($pendingWorkspaceUsers as $workspaceUser) {
            try {
                if ($workspaceUser->user) {
                    // Resend notification regardless of read status
                    $workspaceUser->user->notify(new AccessShare(
                        $workspaceUser->workspace,
                        $workspaceUser->workspace->owner,
                        'pending'
                    ));
                    $count++;
                    Log::info("Resent workspace invitation to user {$workspaceUser->user->email} for workspace {$workspaceUser->workspace->name}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to resend workspace invitation: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Resend pending project invitations sent more than 7 days ago.
     */
    private function resendProjectInvitations(): int
    {
        $count = 0;

        // Get pending project invitations invited more than 7 days ago
        $pendingCollaborators = ProjectCollaborator::where('status', 'pending')
            ->where('invited_at', '<=', now()->subDays(7))
            ->with(['project', 'user'])
            ->get();

        foreach ($pendingCollaborators as $collaborator) {
            try {
                if ($collaborator->user) {
                    // Resend notification regardless of read status
                    $collaborator->user->notify(new ProjectAccessShare(
                        $collaborator->project,
                        $collaborator->project->owner,
                        'pending'
                    ));
                    $count++;
                    Log::info("Resent project invitation to user {$collaborator->user->email} for project {$collaborator->project->name}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to resend project invitation: " . $e->getMessage());
            }
        }

        return $count;
    }
}
