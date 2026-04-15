<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            @isset($workflow) Edit Workflow @else Workflow Builder @endif - {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    @isset($workflow) Edit Approval Workflow @else Approval Workflow Builder @endif
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    @isset($workflow) Modify the approval workflow for assets in {{ $project->name }} @else Create a custom approval workflow for assets in {{ $project->name }} @endif
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Workflow Configuration -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Workflow Configuration
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Workflow Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Workflow Name
                            </label>
                            <input type="text" id="workflow-name"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g., Design Review Process"
                                @isset($workflow) value="{{ $workflow->name }}" @endif>
                        </div>

                        <!-- Template Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Template
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" 
                                    class="template-btn p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors text-left"
                                    data-template="single">
                                    <div class="font-semibold text-gray-900 dark:text-white">Single Approver</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Direct manager approves</div>
                                </button>
                                <button type="button" 
                                    class="template-btn p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors text-left"
                                    data-template="sequential">
                                    <div class="font-semibold text-gray-900 dark:text-white">Sequential Chain</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Designer → Manager → Client</div>
                                </button>
                                <button type="button" 
                                    class="template-btn p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors text-left"
                                    data-template="parallel">
                                    <div class="font-semibold text-gray-900 dark:text-white">Parallel Review</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Multiple reviewers simultaneously</div>
                                </button>
                            </div>
                        </div>

                        <!-- Approvers Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Who needs to approve this asset?
                            </label>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Project Collaborators</div>
                                    <select id="approvers-select" multiple
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white h-32">
                                        @foreach($projectCollaborators as $collaborator)
                                            <option value="{{ $collaborator->user->id }}">
                                                {{ $collaborator->user->name }} ({{ ucfirst($collaborator->role) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Workspace Members</div>
                                    <select id="workspace-members-select" multiple
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white h-32">
                                        @foreach($workspaceMembers as $member)
                                            <option value="{{ $member->id }}">
                                                {{ $member->name }} ({{ $member->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Hold Ctrl/Cmd to select multiple approvers
                            </p>
                        </div>

                        <!-- Sequential Order Selection (shown only for sequential template) -->
                        <div id="sequential-order-section" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Approval Sequence
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                Drag and drop to set the approval order (who approves 1st, 2nd, etc.)
                            </p>
                            <div id="sequential-approvers-list" class="space-y-2 min-h-[100px] border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-3">
                                <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                                    Select approvers above to see sequence options
                                </div>
                            </div>
                        </div>

                        <!-- Approval Settings -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Approval Settings</h4>
                            
                            <!-- Auto-route to next approver -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">Auto-route to next approver</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Automatically route to next when current approves</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="auto-route-next" @isset($workflow) {{ $workflow->auto_route_next ? 'checked' : '' }} @else checked @endif class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Deadline -->
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">
                                    Approval deadline (hours)
                                </label>
                                <input type="number" id="deadline-hours" min="1" placeholder="e.g., 24"
                                    @isset($workflow) value="{{ $workflow->deadline_hours }}" @endif
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Leave empty for no deadline
                                </p>
                            </div>

                            <!-- Require comments -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">Require approval comments</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Approvers must add comments when deciding</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="require-comments" @isset($workflow) {{ $workflow->require_comments ? 'checked' : '' }} @endif class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Reminder emails -->
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">
                                    Send reminder emails (hours before deadline)
                                </label>
                                <input type="number" id="reminder-hours" min="1"
                                    @isset($workflow) value="{{ $workflow->send_reminder_hours }}" @else value="24" @endif
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>

                            <!-- Allow rejection -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300">Allow rejection</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Reviewers can reject assets</div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="allow-rejection" @isset($workflow) {{ $workflow->allow_rejection ? 'checked' : '' }} @else checked @endif class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-3 pt-4">
                            <button onclick="saveWorkflow()" 
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                Save Workflow
                            </button>
                            <a href="{{ route('workspace.show', [$project->workspace, $project]) }}" 
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Workflow Preview -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Workflow Preview
                        </h3>
                    </div>
                    <div class="p-6">
                        <div id="workflow-preview" class="space-y-4">
                            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                                <p>Select a template and add approvers to see the workflow preview</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedTemplate = null;
        let selectedApprovers = [];
        let sequentialOrder = []; // Store the sequence order for sequential workflows
        let workflowId = @if(isset($workflow)) {{ $workflow->id }} @else null @endif;

        @isset($workflow)
        let editingWorkflow = true;
        let workflowType = '{{ $workflow->type }}';
        @php
            $allApprovers = [];
            $sequentialOrderData = [];
            if($workflow->definition && isset($workflow->definition['steps'])) {
                foreach($workflow->definition['steps'] as $step) {
                    if(isset($step['approvers'])) {
                        $allApprovers = array_merge($allApprovers, $step['approvers']);
                        if(isset($step['sequence'])) {
                            $sequentialOrderData[$step['sequence']] = $step['approvers'][0];
                        }
                    }
                }
                $allApprovers = array_unique($allApprovers);
            }
        @endphp
        let allApproverIds = @json(array_values($allApprovers));
        let sequentialOrderInitial = @if($workflow->type === 'sequential' && !empty($sequentialOrderData)) @json(array_values($sequentialOrderData)) @else [] @endif;
        @else
        let editingWorkflow = false;
        let workflowType = null;
        let allApproverIds = [];
        let sequentialOrderInitial = [];
        @endif

        // Initialize event listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Template button event listeners
            document.querySelectorAll('.template-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const template = this.getAttribute('data-template');
                    selectTemplate(template);
                });
            });
            
            // Approver selection event listeners
            const approversSelect = document.getElementById('approvers-select');
            const workspaceMembersSelect = document.getElementById('workspace-members-select');
            
            if (approversSelect) {
                approversSelect.addEventListener('change', function() {
                    updatePreview();
                    if (selectedTemplate === 'sequential') {
                        updateSequentialOrderList();
                    }
                });
            }
            if (workspaceMembersSelect) {
                workspaceMembersSelect.addEventListener('change', function() {
                    updatePreview();
                    if (selectedTemplate === 'sequential') {
                        updateSequentialOrderList();
                    }
                });
            }

            // Pre-select template and approvers when editing
            if (editingWorkflow) {
                selectedTemplate = workflowType;
                selectTemplate(selectedTemplate);

                // Pre-select approvers from workflow definition
                allApproverIds.forEach(approverId => {
                    // Try to select from project collaborators
                    const approversSelectEl = document.getElementById('approvers-select');
                    if (approversSelectEl) {
                        for (let option of approversSelectEl.options) {
                            if (option.value == approverId) {
                                option.selected = true;
                            }
                        }
                    }
                    // Try to select from workspace members
                    const workspaceSelectEl = document.getElementById('workspace-members-select');
                    if (workspaceSelectEl) {
                        for (let option of workspaceSelectEl.options) {
                            if (option.value == approverId) {
                                option.selected = true;
                            }
                        }
                    }
                });

                // Set sequential order if editing sequential workflow
                if (workflowType === 'sequential' && sequentialOrderInitial.length > 0) {
                    sequentialOrder = sequentialOrderInitial;
                }

                updatePreview();
                updateSequentialOrderList();
            }
        });

        function selectTemplate(template) {
            selectedTemplate = template;

            // Update button styles
            document.querySelectorAll('.template-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'dark:border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20');
                btn.classList.add('border-gray-200', 'dark:border-gray-600');
            });

            const selectedBtn = document.querySelector(`[data-template="${template}"]`);
            if (selectedBtn) {
                selectedBtn.classList.remove('border-gray-200', 'dark:border-gray-600');
                selectedBtn.classList.add('border-blue-500', 'dark:border-blue-400', 'bg-blue-50', 'dark:bg-blue-900/20');
            }

            // Show/hide sequential order section
            const sequentialSection = document.getElementById('sequential-order-section');
            if (template === 'sequential') {
                sequentialSection.classList.remove('hidden');
                updateSequentialOrderList();
            } else {
                sequentialSection.classList.add('hidden');
            }

            updatePreview();
        }

        function getSelectedApprovers() {
            const approversSelect = document.getElementById('approvers-select');
            const workspaceSelect = document.getElementById('workspace-members-select');
            
            const approvers = Array.from(approversSelect.selectedOptions).map(opt => ({
                id: opt.value,
                name: opt.text
            }));
            
            const workspaceMembers = Array.from(workspaceSelect.selectedOptions).map(opt => ({
                id: opt.value,
                name: opt.text
            }));
            
            return [...approvers, ...workspaceMembers];
        }

        function updateSequentialOrderList() {
            const listContainer = document.getElementById('sequential-approvers-list');
            const approvers = getSelectedApprovers();

            if (approvers.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                        Select approvers above to see sequence options
                    </div>
                `;
                return;
            }

            // Initialize sequential order if empty
            if (sequentialOrder.length === 0) {
                sequentialOrder = approvers.map(a => parseInt(a.id));
            }

            // Filter out approvers that are no longer selected
            const selectedIds = approvers.map(a => parseInt(a.id));
            sequentialOrder = sequentialOrder.filter(id => selectedIds.includes(id));

            // Add newly selected approvers to the end
            approvers.forEach(approver => {
                const id = parseInt(approver.id);
                if (!sequentialOrder.includes(id)) {
                    sequentialOrder.push(id);
                }
            });

            // Build the drag-and-drop list
            let html = '';
            sequentialOrder.forEach((approverId, index) => {
                const approver = approvers.find(a => parseInt(a.id) === approverId);
                if (approver) {
                    html += `
                        <div class="flex items-center space-x-3 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg cursor-move hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                             draggable="true"
                             data-index="${index}"
                             data-id="${approverId}">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-300 font-semibold">
                                ${index + 1}
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">${approver.name}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Approves ${index === 0 ? 'first' : (index === sequentialOrder.length - 1 ? 'last' : 'after #' + index)}</div>
                            </div>
                            <button type="button" onclick="removeFromSequence(${index})" 
                                    class="text-gray-400 hover:text-red-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                }
            });

            listContainer.innerHTML = html;

            // Add drag-and-drop event listeners
            const items = listContainer.querySelectorAll('[draggable="true"]');
            items.forEach(item => {
                item.addEventListener('dragstart', handleDragStart);
                item.addEventListener('dragover', handleDragOver);
                item.addEventListener('drop', handleDrop);
                item.addEventListener('dragend', handleDragEnd);
            });
        }

        let draggedItem = null;

        function handleDragStart(e) {
            draggedItem = this;
            this.classList.add('opacity-50');
            e.dataTransfer.effectAllowed = 'move';
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            return false;
        }

        function handleDrop(e) {
            e.stopPropagation();
            if (draggedItem !== this) {
                const fromIndex = parseInt(draggedItem.dataset.index);
                const toIndex = parseInt(this.dataset.index);

                // Reorder the array
                const item = sequentialOrder.splice(fromIndex, 1)[0];
                sequentialOrder.splice(toIndex, 0, item);

                updateSequentialOrderList();
                updatePreview();
            }
            return false;
        }

        function handleDragEnd(e) {
            this.classList.remove('opacity-50');
            const items = document.querySelectorAll('#sequential-approvers-list [draggable="true"]');
            items.forEach(item => {
                item.classList.remove('opacity-50');
            });
        }

        function removeFromSequence(index) {
            const approverId = sequentialOrder[index];
            sequentialOrder.splice(index, 1);
            
            // Deselect the approver from the dropdowns
            const approversSelect = document.getElementById('approvers-select');
            const workspaceSelect = document.getElementById('workspace-members-select');
            
            for (let option of approversSelect.options) {
                if (option.value == approverId) {
                    option.selected = false;
                }
            }
            for (let option of workspaceSelect.options) {
                if (option.value == approverId) {
                    option.selected = false;
                }
            }

            updateSequentialOrderList();
            updatePreview();
        }

        function updatePreview() {
            const preview = document.getElementById('workflow-preview');
            selectedApprovers = getSelectedApprovers();
            
            if (!selectedTemplate || selectedApprovers.length === 0) {
                preview.innerHTML = `
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        <p>Select a template and add approvers to see the workflow preview</p>
                    </div>
                `;
                return;
            }

            let html = '';
            
            switch(selectedTemplate) {
                case 'single':
                    html = `
                        <div class="flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-3">
                                    <span class="text-2xl">👤</span>
                                </div>
                                <div class="font-medium text-gray-900 dark:text-white">${selectedApprovers[0].name}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Approver</div>
                            </div>
                        </div>
                    `;
                    break;
                    
                case 'sequential':
                    // Use the sequential order for preview
                    const orderedApprovers = sequentialOrder.map(id => selectedApprovers.find(a => parseInt(a.id) === id)).filter(a => a);
                    html = '<div class="space-y-4">';
                    orderedApprovers.forEach((approver, index) => {
                        html += `
                            <div class="flex items-center">
                                <div class="flex-1 text-center">
                                    <div class="w-12 h-12 mx-auto bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mb-2">
                                        <span class="text-lg">👤</span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">${approver.name}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Step ${index + 1}</div>
                                </div>
                                ${index < orderedApprovers.length - 1 ? `
                                    <div class="px-4">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    });
                    html += '</div>';
                    break;
                    
                case 'parallel':
                    html = `
                        <div class="flex items-center justify-around">
                            ${selectedApprovers.map(approver => `
                                <div class="text-center">
                                    <div class="w-12 h-12 mx-auto bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mb-2">
                                        <span class="text-lg">👤</span>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">${approver.name}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Parallel Reviewer</div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    break;
                    
                case 'custom':
                    html = `
                        <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                            <p>Custom workflow - define steps programmatically</p>
                        </div>
                    `;
                    break;
            }
            
            preview.innerHTML = html;
        }

        async function saveWorkflow() {
            const name = document.getElementById('workflow-name').value;
            const approvers = getSelectedApprovers();

            console.log('Saving workflow...');
            console.log('Name:', name);
            console.log('Selected template:', selectedTemplate);
            console.log('Approvers:', approvers);
            console.log('Sequential order:', sequentialOrder);
            console.log('Workflow ID:', workflowId);

            if (!name) {
                alert('Please enter a workflow name');
                return;
            }

            if (!selectedTemplate) {
                alert('Please select a template');
                return;
            }

            if (approvers.length === 0) {
                alert('Please select at least one approver');
                return;
            }

            let steps;
            if (selectedTemplate === 'parallel') {
                // Parallel: all approvers in one step, all must approve
                steps = [{
                    sequence: 1,
                    approvers: approvers.map(a => parseInt(a.id)),
                    parallel: true,
                    require_all: true // All approvers must approve
                }];
            } else if (selectedTemplate === 'single') {
                steps = [{
                    sequence: 1,
                    approvers: [parseInt(approvers[0].id)],
                    parallel: false
                }];
            } else if (selectedTemplate === 'sequential') {
                // Sequential: use the drag-and-drop order, no order column
                const orderedApprovers = sequentialOrder.map(id => approvers.find(a => parseInt(a.id) === id)).filter(a => a);
                steps = orderedApprovers.map((approver, index) => ({
                    sequence: index + 1, // Use sequence instead of order
                    approvers: [parseInt(approver.id)],
                    parallel: false
                }));
            } else {
                // custom
                steps = approvers.map((a, i) => ({
                    sequence: i + 1,
                    approvers: [parseInt(a.id)],
                    parallel: false
                }));
            }

            console.log('Steps:', steps);

            const workflowData = {
                name: name,
                type: selectedTemplate,
                definition: {
                    steps: steps
                },
                deadline_hours: document.getElementById('deadline-hours').value ? parseInt(document.getElementById('deadline-hours').value) : null,
                auto_route_next: document.getElementById('auto-route-next').checked,
                require_comments: document.getElementById('require-comments').checked,
                send_reminder_hours: parseInt(document.getElementById('reminder-hours').value),
                allow_rejection: document.getElementById('allow-rejection').checked,
                is_active: true
            };

            console.log('Workflow data to send:', workflowData);

            try {
                let url, method;
                if (workflowId) {
                    url = `/workflows/${workflowId}`;
                    method = 'PUT';
                } else {
                    url = `/projects/{{ $project->id }}/workflows`;
                    method = 'POST';
                }

                console.log('Request URL:', url);
                console.log('Request method:', method);

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(workflowData)
                });

                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);

                if (response.ok) {
                    const result = await response.json();
                    console.log('Response data:', result);
                    alert(workflowId ? 'Workflow updated successfully!' : 'Workflow created successfully!');
                    window.location.href = '{{ route("workspace.show", [$project->workspace, $project]) }}';
                } else {
                    const error = await response.json();
                    console.error('Error response:', error);
                    alert('Error saving workflow: ' + (error.message || JSON.stringify(error) || 'Unknown error'));
                }
            } catch (error) {
                console.error('Fetch error:', error);
                alert('Error saving workflow: ' + error.message);
            }
        }
    </script>
</x-app-layout>
