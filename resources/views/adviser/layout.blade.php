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
                    <span>Students</span>
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
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div id="modal-icon" class="w-12 h-12 rounded-full flex items-center justify-center mr-4">
                            <i id="modal-icon-class" class="text-2xl"></i>
                        </div>
                        <div>
                            <h3 id="modal-title" class="text-lg font-medium text-gray-900"></h3>
                            <p id="modal-message" class="text-sm text-gray-500 mt-1"></p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button id="modal-cancel" type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button id="modal-confirm" type="button"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Global notification system
        function showNotification(message, type = 'success', duration = 5000) {
            const container = document.getElementById('notification-container');
            const notificationId = 'notification-' + Date.now();

            const typeConfig = {
                success: {
                    bgColor: 'bg-green-500',
                    icon: 'fas fa-check-circle',
                    textColor: 'text-white'
                },
                error: {
                    bgColor: 'bg-red-500',
                    icon: 'fas fa-exclamation-circle',
                    textColor: 'text-white'
                },
                warning: {
                    bgColor: 'bg-yellow-500',
                    icon: 'fas fa-exclamation-triangle',
                    textColor: 'text-white'
                },
                info: {
                    bgColor: 'bg-blue-500',
                    icon: 'fas fa-info-circle',
                    textColor: 'text-white'
                }
            };

            const config = typeConfig[type] || typeConfig.success;

            const notification = document.createElement('div');
            notification.id = notificationId;
            notification.className = `${config.bgColor} ${config.textColor} px-6 py-4 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out max-w-sm`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="${config.icon} mr-3"></i>
                    <span class="flex-1 text-sm font-medium">${message}</span>
                    <button onclick="removeNotification('${notificationId}')" class="ml-3 ${config.textColor} hover:opacity-75">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            container.appendChild(notification);

            // Trigger animation
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove
            setTimeout(() => {
                removeNotification(notificationId);
            }, duration);
        }

        function removeNotification(notificationId) {
            const notification = document.getElementById(notificationId);
            if (notification) {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }

        // Confirmation modal system
        function showConfirmation(title, message, onConfirm, type = 'question') {
            const modal = document.getElementById('confirmation-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalMessage = document.getElementById('modal-message');
            const modalIcon = document.getElementById('modal-icon');
            const modalIconClass = document.getElementById('modal-icon-class');
            const confirmBtn = document.getElementById('modal-confirm');
            const cancelBtn = document.getElementById('modal-cancel');

            const typeConfig = {
                question: {
                    bgColor: 'bg-blue-100',
                    iconColor: 'text-blue-600',
                    icon: 'fas fa-question-circle',
                    confirmColor: 'bg-blue-600 hover:bg-blue-700'
                },
                warning: {
                    bgColor: 'bg-yellow-100',
                    iconColor: 'text-yellow-600',
                    icon: 'fas fa-exclamation-triangle',
                    confirmColor: 'bg-yellow-600 hover:bg-yellow-700'
                },
                danger: {
                    bgColor: 'bg-red-100',
                    iconColor: 'text-red-600',
                    icon: 'fas fa-exclamation-circle',
                    confirmColor: 'bg-red-600 hover:bg-red-700'
                }
            };

            const config = typeConfig[type] || typeConfig.question;

            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalIcon.className = `w-12 h-12 rounded-full flex items-center justify-center mr-4 ${config.bgColor}`;
            modalIconClass.className = `text-2xl ${config.iconColor} ${config.icon}`;
            confirmBtn.className = `px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 ${config.confirmColor}`;

            modal.classList.remove('hidden');

            // Handle confirm
            const handleConfirm = () => {
                modal.classList.add('hidden');
                if (onConfirm) onConfirm();
                confirmBtn.removeEventListener('click', handleConfirm);
                cancelBtn.removeEventListener('click', handleCancel);
            };

            // Handle cancel
            const handleCancel = () => {
                modal.classList.add('hidden');
                confirmBtn.removeEventListener('click', handleConfirm);
                cancelBtn.removeEventListener('click', handleCancel);
            };

            confirmBtn.addEventListener('click', handleConfirm);
            cancelBtn.addEventListener('click', handleCancel);

            // Close on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    handleCancel();
                }
            });
        }

        // Show session messages as notifications
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                showNotification('{{ session('success') }}', 'success');
            @endif

            @if(session('error'))
                showNotification('{{ session('error') }}', 'error');
            @endif

            @if(session('warning'))
                showNotification('{{ session('warning') }}', 'warning');
            @endif

            @if(session('info'))
                showNotification('{{ session('info') }}', 'info');
            @endif
        });
    </script>
</body>
</html>






