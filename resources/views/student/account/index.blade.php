@extends('student.layout')

@section('title', 'My Account')
@section('page-title', 'My Account')

@section('content')
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-800">Account Information</h2>
    </div>

    <div class="p-6">
        <div class="flex items-start">
            <div class="mr-6">
                @if(auth()->user()->profile_picture)
                    <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                         alt="{{ auth()->user()->first_name }}"
                         class="w-32 h-32 object-cover rounded-full border-4 border-gray-200">
                @else
                    <div class="w-32 h-32 bg-green-100 rounded-full flex items-center justify-center border-4 border-gray-200">
                        <i class="fas fa-user text-green-600 text-4xl"></i>
                    </div>
                @endif
            </div>

            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-800">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h3>
                <p class="text-gray-600 mb-4">{{ auth()->user()->id_number }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-gray-800">{{ auth()->user()->email }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Department</p>
                        <p class="text-gray-800">{{ auth()->user()->department->name ?? 'Not Assigned' }}</p>
                    </div>
                </div>

                <a href="{{ route('student.account.edit') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg inline-block">
                    <i class="fas fa-edit mr-2"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


