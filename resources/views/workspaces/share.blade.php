<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Share Workspace') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                            </svg>
                            Share "{{ $workspace->name }}"
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Share by {{ auth()->user()->id === $workspace->owner_id ? 'Admin' : $workspace->owner->name }} - Invite team members to collaborate on this workspace.
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Invite Form -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Invite New Member
                        </h3>
                        <form action="{{ route('workspaces.invite', $workspace) }}" method="POST">
                            @csrf
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <input type="email" 
                                           name="email" 
                                           placeholder="Enter email address" 
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:text-white"
                                           required>
                                </div>
                                <div>
                                    <select name="role" 
                                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:text-white">
                                        @if($workspace->isOwnedBy(auth()->user()))
                                            <option value="admin">Admin</option>
                                        @endif
                                        <option value="user">User</option>
                                    </select>
                                </div>
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                    Send Invite
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Current Approved Members -->
                    @php
                        $isOwner = $workspace->isOwnedBy(auth()->user());
                    @endphp
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Current Members ({{
                                $approvedMembers->filter(function($m) use ($workspace, $isOwner) {
                                    if ($m->user->id === $workspace->owner_id) return false;
                                    if (!$isOwner && $m->role === 'admin') return false;
                                    return true;
                                })->count()
                            }})
                        </h3>
                        @if($approvedMembers->count() === 0)
                            <p class="text-gray-500 dark:text-gray-400">
                                No members yet. Invite someone to get started!
                            </p>
                        @else
                            <div class="space-y-3">
                                @foreach($approvedMembers as $memberRecord)
                                    @if($memberRecord->user->id === $workspace->owner_id)
                                        @continue
                                    @endif
                                    @if(!$isOwner && $memberRecord->role === 'admin')
                                        @continue
                                    @endif
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($memberRecord->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $memberRecord->user->name }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $memberRecord->user->email }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                                {{ $memberRecord->role === 'owner' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                                   ($memberRecord->role === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                                {{ ucfirst($memberRecord->role) }}
                                            </span>
                                            @if($memberRecord->role !== 'owner')
                                                <form action="{{ route('workspaces.remove-member', [$workspace, $memberRecord->user]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this member?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Pending Workspace User Requests -->
                    @if($pendingRequests->count() > 0)
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Pending Invitations ({{ $pendingRequests->count() }})
                            </h3>
                            <div class="space-y-3">
                                @foreach($pendingRequests as $request)
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $request->user->name }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $request->user->email }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Pending
                                            </span>
                                            <div class="flex space-x-2">
                                            
                                                <form action="{{ route('notifications.reject-workspace', $request->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium py-1 px-3 rounded transition duration-200">
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Rejected Workspace User Requests -->
                    @if($rejectedRequests->count() > 0)
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Rejected Invitations ({{ $rejectedRequests->count() }})
                            </h3>
                            <div class="space-y-3">
                                @foreach($rejectedRequests as $request)
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-800">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $request->user->name }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $request->user->email }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Rejected
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('workspaces.page') }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            Back to Workspaces
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
