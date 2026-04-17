<x-app-layout>
 <x-slot name="header">
 <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
 {{ __('Create Workspace') }}
 </h2>
 </x-slot>

 <div class="py-12">
 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
 <div class="p-6 bg-white dark:bg-gray-800 ">
 <div class="mb-6">
 <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 ">
 Create New Workspace
 </h1>
 </div>

 @if($errors->any())
 <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 rounded">
 <ul>
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <form action="{{ route('workspaces.store') }}" method="POST">
 @csrf
 
 <div class="mb-4">
 <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
 Workspace Name *
 </label>
 <input type="text" id="name" name="name" required
 value="{{ old('name') }}"
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
 </div>

 <div class="flex justify-end space-x-3">
 <a href="{{ route('workspaces.page') }}" 
 class="px-4 py-2 bg-gray-300 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 transition duration-200">
 Cancel
 </a>
 <button type="submit" 
 class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
 Create
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
