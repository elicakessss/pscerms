@extends('adviser.layout')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-700 mb-4">Welcome, {{ $adviser->first_name }}!</h2>
        <p class="text-gray-600">
            This is your dashboard where you can manage students and access various features.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Quick Actions Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Quick Actions</h3>
            <ul class="space-y-3">
                <li>
                    <a href="{{ route('adviser.student_management.index') }}" class="flex items-center text-green-600 hover:text-green-800">
                        <i class="fas fa-users mr-2"></i>
                        <span>Manage Students</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center text-green-600 hover:text-green-800">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        <span>View Reports</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center text-green-600 hover:text-green-800">
                        <i class="fas fa-cog mr-2"></i>
                        <span>Account Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Department Info Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Department Information</h3>
            <p class="text-gray-600 mb-2">
                <span class="font-medium">Department:</span>
                {{ $adviser->department->name ?? 'Not assigned' }}
            </p>
            <p class="text-gray-600">
                <span class="font-medium">Role:</span> Adviser
            </p>
        </div>

        <!-- System Updates Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">System Updates</h3>
            <div class="text-gray-600">
                <p class="mb-2">Welcome to the new Adviser Dashboard!</p>
                <p>Here you can manage your students and access various system features.</p>
            </div>
        </div>
    </div>
</div>
@endsection
