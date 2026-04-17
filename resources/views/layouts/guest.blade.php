<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
 <head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <meta name="color-scheme" content="light">

 <title>{{ config('app.name', 'Laravel') }}</title>

 <!-- Fonts -->
 <link rel="preconnect" href="https://fonts.bunny.net">
 <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" /> 

 <!-- Scripts -->
 @vite(['resources/css/app.css', 'resources/js/app.js'])
 </head>
 <body class="font-sans text-gray-900 antialiased">
 <div class="min-h-screen flex">
 <!-- Left Side - Login/Register Form -->
 <div class="w-full lg:w-1/2 flex flex-col items-center justify-center bg-white">
 <div class="w-full max-w-md px-8 py-12">
 <!-- Logo -->
 <div class="mb-8 text-center">
 <div class="flex items-center justify-center gap-2 mb-2">
 
 <span class="text-2xl font-bold tracking-tight">CONTENT & ASSETAPPROVAL
WORKFLOW TOOL</span>
 </div>
 </div>
 {{ $slot }}
 </div>
 </div>

 <!-- Right Side - Dashboard Preview -->
 <div class="hidden lg:flex lg:w-1/2 items-center justify-center bg-gradient-to-br from-pink-700 via-pink-800 to-purple-900 relative overflow-hidden">
 <!-- Decorative circles -->
 <div class="absolute top-20 right-20 w-64 h-64 bg-white/5 rounded-full"></div>
 <div class="absolute bottom-20 left-20 w-48 h-48 bg-white/5 rounded-full"></div>
 <div class="absolute top-40 left-40 w-32 h-32 bg-white/5 rounded-full"></div>
 
 <!-- Dashboard Preview Card -->
 <div class="w-full max-w-lg mx-8 bg-white rounded-2xl shadow-2xl overflow-hidden">
 <!-- Dashboard Header -->
 <div class="bg-gray-50 px-4 py-3 border-b flex items-center gap-2">
 <div class="w-3 h-3 rounded-full bg-red-400"></div>
 <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
 <div class="w-3 h-3 rounded-full bg-green-400"></div>
 <span class="ml-4 text-sm text-gray-500">Dashboard</span>
 </div>
 
 <!-- Dashboard Content -->
 <div class="p-6">
 <!-- Stats Cards -->
 <div class="grid grid-cols-3 gap-4 mb-6">
 <div class="bg-gray-50 rounded-lg p-3">
 <p class="text-xs text-gray-500 mb-1">Total Earnings</p>
 <p class="text-lg font-bold text-gray-800">$485,323</p>
 <div class="mt-2 h-1 bg-blue-500 rounded-full w-2/3"></div>
 </div>
 <div class="bg-gray-50 rounded-lg p-3">
 <p class="text-xs text-gray-500 mb-1">Total Sale</p>
 <p class="text-lg font-bold text-gray-800">$32,585</p>
 <div class="mt-2 h-1 bg-orange-400 rounded-full w-1/2"></div>
 </div>
 <div class="bg-gray-50 rounded-lg p-3">
 <p class="text-xs text-gray-500 mb-1">Total Profit</p>
 <p class="text-lg font-bold text-gray-800">$255,707</p>
 <div class="mt-2 h-1 bg-green-500 rounded-full w-3/4"></div>
 </div>
 </div>
 
 <!-- Chart -->
 <div class="bg-gray-50 rounded-lg p-4 mb-4">
 <div class="flex items-center justify-between mb-3">
 <span class="text-sm font-medium text-gray-700">Overall Revenue</span>
 <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 12h16M4 16h16"/>
 </svg>
 </div>
 <div class="flex items-end justify-between h-24 gap-2">
 <div class="w-full bg-blue-500 rounded-t" style="height: 45%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 60%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 55%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 80%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 65%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 70%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 50%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 85%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 75%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 90%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 70%"></div>
 <div class="w-full bg-blue-500 rounded-t" style="height: 55%"></div>
 </div>
 </div>
 
 <!-- Recent Orders Table -->
 <div class="bg-gray-50 rounded-lg p-4">
 <div class="flex items-center justify-between mb-3">
 <span class="text-sm font-medium text-gray-700">Recent Orders</span>
 <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 12h16M4 16h16"/>
 </svg>
 </div>
 <div class="overflow-x-auto">
 <table class="w-full text-xs">
 <thead>
 <tr class="text-gray-500">
 <th class="text-left py-1">Customer</th>
 <th class="text-left py-1">Invoice</th>
 <th class="text-left py-1">Amount</th>
 <th class="text-left py-1">Status</th>
 </tr>
 </thead>
 <tbody class="text-gray-700">
 <tr>
 <td class="py-1 flex items-center gap-2">
 <div class="w-5 h-5 rounded-full bg-gray-300"></div>
 <span>John Doe</span>
 </td>
 <td class="py-1">#INV001</td>
 <td class="py-1">$1,200</td>
 <td class="py-1"><span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-xs">Paid</span></td>
 </tr>
 <tr>
 <td class="py-1 flex items-center gap-2">
 <div class="w-5 h-5 rounded-full bg-gray-300"></div>
 <span>Jane Smith</span>
 </td>
 <td class="py-1">#INV002</td>
 <td class="py-1">$850</td>
 <td class="py-1"><span class="px-2 py-0.5 bg-yellow-100 text-yellow-600 rounded text-xs">Pending</span></td>
 </tr>
 </tbody>
 </table>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </body>
</html>
