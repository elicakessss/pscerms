@extends('admin.layout')

@section('title', 'Dashboard - PSCERMS')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Users</h3>
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Student::count() + \App\Models\Adviser::count() + \App\Models\Admin::count() }}</p>
            </div>
        </div>
    </div>

    <!-- Students Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-user-graduate text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Students</h3>
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Student::count() }}</p>
            </div>
        </div>
    </div>

    <!-- Advisers Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-chalkboard-teacher text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Advisers</h3>
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Adviser::count() }}</p>
            </div>
        </div>
    </div>

    <!-- Administrators Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
                <i class="fas fa-user-shield text-red-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Administrators</h3>
                <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Admin::count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow mb-8">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.user_management.create') }}"
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors">
                <div class="p-2 bg-green-100 rounded-lg mr-4">
                    <i class="fas fa-user-plus text-green-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Add New User</h4>
                    <p class="text-sm text-gray-500">Create student, adviser, or admin account</p>
                </div>
            </a>

            <a href="{{ route('admin.user_management.index') }}"
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                <div class="p-2 bg-blue-100 rounded-lg mr-4">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Manage Users</h4>
                    <p class="text-sm text-gray-500">View and manage all user accounts</p>
                </div>
            </a>

            <div class="flex items-center p-4 border border-gray-200 rounded-lg opacity-50">
                <div class="p-2 bg-gray-100 rounded-lg mr-4">
                    <i class="fas fa-cog text-gray-400"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-400">System Settings</h4>
                    <p class="text-sm text-gray-400">Coming soon...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
    </div>
    <div class="p-6">
        <div class="text-center py-8">
            <i class="fas fa-clock text-4xl text-gray-300 mb-4"></i>
            <h4 class="text-lg font-medium text-gray-500 mb-2">No Recent Activity</h4>
            <p class="text-gray-400">Activity logs will appear here once the system is in use.</p>
        </div>
    </div>
</div>
@endsection
