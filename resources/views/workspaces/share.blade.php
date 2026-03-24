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
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
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
                                        <option value="member">Member</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                    Send Invite
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Current Members -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Current Members ({{ $members->count() }})
                        </h3>
                        @if($members->count() === 0)
                            <p class="text-gray-500 dark:text-gray-400">
                                No members yet. Invite someone to get started!
                            </p>
                        @else
                            <div class="space-y-3">
                                @foreach($members as $member)
                                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $member->name }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $member->email }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                                {{ $member->pivot->role === 'owner' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                                   ($member->pivot->role === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                                {{ ucfirst($member->pivot->role) }}
                                            </span>
                                            @if($member->pivot->role !== 'owner')
                                                <form action="{{ route('workspaces.remove-member', [$workspace, $member]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this member?')">
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
