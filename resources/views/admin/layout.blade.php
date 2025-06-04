<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PSCERMS Admin')</title>
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

        /* Button styles */
        .btn-primary {
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
            color: white;
            border: none;
            transition: opacity 0.2s;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }

        /* Table header styles */
        .table-header-green {
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
        }

        /* Fix dropdown text visibility */
        select {
            color: #374151 !important;
        }
        select option {
            color: #374151 !important;
            background: white !important;
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
                <p class="text-green-200 sidebar-subtitle">Admin Panel</p>
            </div>

            <!-- Admin Profile -->
            <div class="p-4 border-b border-green-600">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-300 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-green-800"></i>
                    </div>
                    <div>
                        <h3 class="profile-name">{{ auth()->user()->full_name ?? 'Admin User' }}</h3>
                        <p class="text-green-200 profile-role">Administrator</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('admin.dashboard') ? 'active-nav' : '' }}">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.user_management.index') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('admin.user_management.*') ? 'active-nav' : '' }}">
                    <i class="fas fa-users w-5"></i>
                    <span>Users</span>
                </a>

                <a href="{{ route('admin.departments.index') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('admin.departments.*') ? 'active-nav' : '' }}">
                    <i class="fas fa-building w-5"></i>
                    <span>Departments</span>
                </a>

                <a href="{{ route('admin.council_management.index') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('admin.council_management.*') ? 'active-nav' : '' }}">
                    <i class="fas fa-user-tie w-5"></i>
                    <span>Councils</span>
                </a>

                <a href="{{ route('admin.evaluation_forms.index') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('admin.evaluation_forms.*') ? 'active-nav' : '' }}">
                    <i class="fas fa-clipboard-list w-5"></i>
                    <span>Evaluation Forms</span>
                </a>

                <a href="{{ route('admin.account.index') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link {{ request()->routeIs('admin.account.*') ? 'active-nav' : '' }}">
                    <i class="fas fa-user-cog w-5"></i>
                    <span>Account</span>
                </a>

                <a href="#"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-600 transition-colors nav-link">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>System Logs</span>
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-green-600">
                <form method="POST" action="{{ route('admin.logout') }}">
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
                        <!-- Search (if needed) -->
                        @yield('header-actions')
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline text-base">{{ session('success') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display='none';">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                @endif

                <!-- Error Message -->
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline text-base">{{ session('error') }}</span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display='none';">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.style.display='none';">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>


