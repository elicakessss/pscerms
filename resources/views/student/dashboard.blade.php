@extends('student.layout')

@section('title', 'Dashboard - PSCERMS')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Welcome Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-home text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Welcome</h3>
                <p class="text-lg font-bold text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-gray-600">Welcome to your student dashboard.</p>
        </div>
    </div>

    <!-- Councils Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-users-cog text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">My Councils</h3>
                <p class="text-lg font-bold text-gray-900">Coming Soon</p>
            </div>
        </div>
        <div class="mt-4">
            <a href="#" class="text-green-600 hover:text-green-800 text-sm font-medium">
                View Councils <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Account Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-user-circle text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Account</h3>
                <p class="text-lg font-bold text-gray-900">Manage Settings</p>
            </div>
        </div>
        <div class="mt-4">
            <a href="#" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                Account Settings <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>
@endsection


