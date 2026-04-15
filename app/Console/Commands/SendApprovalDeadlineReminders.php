<?php

namespace App\Console\Commands;

use App\Models\Approval;
use App\Models\Asset;
use App\Notifications\ApprovalDeadlineReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendApprovalDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approvals:send-deadline-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 24-hour deadline reminders for pending asset approvals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for pending approvals approaching deadline...');

        $count = 0;

        // Get pending approvals that were created 24 hours ago
        // and haven't been reminded yet
        $approvalsNeedingReminder = Approval::where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24))
            ->where('created_at', '<', now()->subHours(23))
            ->with(['asset', 'asset.project', 'assignedUser'])
            ->get();

        foreach ($approvalsNeedingReminder as $approval) {
            try {
                if ($approval->assignedUser && $approval->asset) {
                    // Send deadline reminder notification
                    $approval->assignedUser->notify(new ApprovalDeadlineReminder($approval->asset, $approval));
                    $count++;
                    Log::info("Sent deadline reminder to user {$approval->assignedUser->email} for asset {$approval->asset->name}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send deadline reminder: " . $e->getMessage());
            }
        }

        $this->info("Sent {$count} deadline reminders.");
        $this->info('Deadline reminder check completed.');
        
        return Command::SUCCESS;
    }
}
