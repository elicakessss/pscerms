<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PSCERMS Adviser')</title>
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
        .active-nav {
            background-color: rgba(255, 255, 255, 0.1);
            border-right: 4px solid #047857;
        }

        /* Standard font sizes */
        .text-xs { font-size: 10px; }
        .text-sm { font-size: 11px; }
        .text-base { font-size: 12px; }
        .text-lg { font-size: 14px; }
        .text-xl { font-size: 16px; }
        .text-2xl { font-size: 18px; }

        /* Custom button styles */
        .btn-primary {
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
            color: white;
            border: none;
            transition: opacity 0.2s;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }

        h1 { font-size: 18px; font-weight: 600; }
        h2 { font-size: 16px; font-weight: 600; }
        h3 { font-size: 14px; font-weight: 500; }

        .nav-link {
            font-size: 12px;
            font-weight: 400;
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
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 sidebar-gradient text-white flex flex-col">
            <!-- Header -->
            <div class="p-6 border-b border-green-600">
                <h1 class="sidebar-title">PSCERMS</h1>
                <p class="text-green-200 sidebar-subtitle">Adviser Panel</p>
            </div>

            <!-- Adviser Profile -->
            <div class="p-4 border-b border-green-600">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-300 rounded-full flex items-center justify-center">
                        @if(auth()->user()->profile_picture)
                            <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                                 alt="{{ auth()->user()->first_name }}"
                                 class="h-10 w-10 rounded-full object-cover">
                        @else
                            <i class="fas fa-user text-green-800"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="profile-name">{{ auth()->user()->first_name ?? 'Adviser' }} {{ auth()->user()->last_name ?? 'User' }}</h3>
                        <p class="text-green-200 profile-role">Adviser</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('adviser.dashboard') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('adviser.dashboard') ? 'active-nav' : '' }}">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('adviser.councils.index') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('adviser.councils.*') ? 'active-nav' : '' }}">
                    <i class="fas fa-users-cog w-5"></i>
                    <span>My Councils</span>
                </a>

                <a href="{{ route('adviser.student_management.index') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('adviser.student_management.*') ? 'active-nav' : '' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>Student Management</span>
                </a>

                <a href="{{ route('adviser.account.index') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('adviser.account.*') ? 'active-nav' : '' }}">
                    <i class="fas fa-user-circle w-5"></i>
                    <span>Account</span>
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-green-600">
                <form method="POST" action="{{ route('adviser.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors text-left nav-link">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="header-gradient shadow-sm border-b p-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-semibold text-white">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4 text-white">
                        @yield('header-actions')
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p class="text-base">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p class="text-base">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>






