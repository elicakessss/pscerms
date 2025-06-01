<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PSCERMS Student')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .sidebar-gradient {
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
        }
        .header-gradient {
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
        }
        .sidebar-title {
            font-size: 16px;
            font-weight: 700;
        }
        .sidebar-subtitle {
            font-size: 11px;
        }
        .profile-name {
            font-size: 12px;
            font-weight: 500;
        }
        .profile-role {
            font-size: 10px;
        }

        /* Standard font sizes */
        .text-xs { font-size: 10px; }
        .text-sm { font-size: 11px; }
        .text-base { font-size: 12px; }
        .text-lg { font-size: 14px; }
        .text-xl { font-size: 16px; }
        .text-2xl { font-size: 18px; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 sidebar-gradient text-white flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-green-600">
                <h1 class="sidebar-title">PSCERMS</h1>
                <p class="text-green-200 sidebar-subtitle">Student Panel</p>
            </div>

            <!-- Student Profile -->
            <div class="p-4 border-b border-green-600">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-300 rounded-full flex items-center justify-center">
                        @if(auth()->user()->profile_picture)
                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="{{ auth()->user()->first_name }}" class="h-10 w-10 rounded-full object-cover">
                        @else
                            <i class="fas fa-user text-green-800"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="profile-name">{{ auth()->user()->first_name ?? 'Student' }} {{ auth()->user()->last_name ?? 'User' }}</h3>
                        <p class="text-green-200 profile-role">Student</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('student.dashboard') }}" class="flex items-center px-6 py-3 text-white hover:bg-green-700 {{ request()->routeIs('student.dashboard') ? 'bg-green-700' : '' }}">
                            <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.councils.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-green-700 {{ request()->routeIs('student.councils.*') ? 'bg-green-700' : '' }}">
                            <i class="fas fa-users-cog w-5 h-5 mr-3"></i>
                            <span>My Councils</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('student.account.index') }}" class="flex items-center px-6 py-3 text-white hover:bg-green-700 {{ request()->routeIs('student.account.*') ? 'bg-green-700' : '' }}">
                            <i class="fas fa-user-circle w-5 h-5 mr-3"></i>
                            <span>Account</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Logout Form -->
            <div class="mt-auto p-4 border-t border-green-600">
                <form method="POST" action="{{ route('student.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2 text-white hover:bg-green-700 rounded">
                        <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="header-gradient shadow">
                <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="text-green-100 hover:text-white">
                                <i class="fas fa-bell"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>

