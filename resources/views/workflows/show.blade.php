<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Workflow Details - {{ $workflow->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <a href="{{ route('workspace.show', [$project->workspace, $project]) }}" 
                   class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition-colors mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to {{ $project->name }}
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $workflow->name }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ ucfirst($workflow->type) }} approval workflow with {{ $workflow->getTotalSteps() }} step(s)
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Workflow Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Workflow Settings -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Workflow Settings</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">Status</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Active or inactive</div>
                                </div>
                                @if($workflow->is_active)
                                    <span class="px-3 py-1 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 rounded-full text-sm font-medium">Active</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300 rounded-full text-sm font-medium">Inactive</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">Auto-route to next approver</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Automatically route to next when current approves</div>
                                </div>
                                @if($workflow->auto_route_next)
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">Require approval comments</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Approvers must add comments when deciding</div>
                                </div>
                                @if($workflow->require_comments)
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">Allow rejection</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Reviewers can reject assets</div>
                                </div>
                                @if($workflow->allow_rejection)
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm text-gray-700 dark:text-gray-300">Approval deadline</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    @if($workflow->deadline_hours)
                                        {{ $workflow->deadline_hours }} hours
                                    @else
                                        No deadline set
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-700 dark:text-gray-300">Reminder emails</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    @if($workflow->send_reminder_hours)
                                {{ $workflow->send_reminder_hours }} hours before deadline
                                    @else
                                        No reminders configured
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Workflow Steps -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Workflow Steps</h3>
                        </div>
                        <div class="p-6">
                            @if($workflow->definition && isset($workflow->definition['steps']))
                                <div class="space-y-4">
                                    @foreach($workflow->definition['steps'] as $step)
                                        <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                <span class="text-lg font-semibold text-blue-600 dark:text-blue-300">{{ $step['order'] }}</span>
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                                    Step {{ $step['order'] }}
                                                    @if($step['parallel'] ?? false)
                                                        <span class="ml-2 px-2 py-1 text-xs bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 rounded-full">Parallel</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Approvers:
                                                    @if(isset($step['approvers']) && count($step['approvers']) > 0)
                                                        @php
                                                            $approverNames = [];
                                                            foreach($step['approvers'] as $approverId) {
                                                                $user = \App\Models\User::find($approverId);
                                                                if($user) $approverNames[] = $user->name;
                                                            }
                                                        @endphp
                                                        {{ implode(', ', $approverNames) }}
                                                    @else
                                                        No approvers assigned
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                    <p>No workflow steps configured</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Active Approvals -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Approvals</h3>
                        </div>
                        <div class="p-6">
                            @if($workflow->approvals->count() > 0)
                                <div class="space-y-3">
                                    @foreach($workflow->approvals as $approval)
                                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                    <span class="text-lg">📄</span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $approval->asset->name ?? 'Unknown Asset' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        Assigned to: {{ $approval->assignedUser->name ?? 'Unknown' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                @if($approval->status === 'pending')
                                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300 rounded-full">Pending</span>
                                                @elseif($approval->status === 'approved')
                                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 rounded-full">Approved</span>
                                                @elseif($approval->status === 'rejected')
                                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 rounded-full">Rejected</span>
                                                @elseif($approval->status === 'changes_requested')
                                                    <span class="px-2 py-1 text-xs bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300 rounded-full">Changes Requested</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                    <p>No active approvals for this workflow</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('workflows.edit', $workflow) }}"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Workflow
                            </a>
                            <form action="{{ route('workflows.destroy', $workflow) }}" method="POST" class="w-full" onsubmit="return confirm('Are you sure you want to delete this workflow? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete Workflow
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Workflow Stats -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Statistics</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">Total Steps</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $workflow->getTotalSteps() }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">Active Approvals</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $workflow->approvals->count() }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">Created At</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $workflow->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
