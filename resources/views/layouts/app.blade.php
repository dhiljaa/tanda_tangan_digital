<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DigiSign') }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 bg-blue-600 text-white">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <i class="bi bi-shield-check text-xl"></i>
                    <h1 class="text-xl font-semibold">DigiSign</h1>
                </a>
                <button id="closeSidebar" class="lg:hidden p-1 rounded-md hover:bg-blue-500 transition-colors">
                    <i class="bi bi-x text-xl"></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-6 px-4">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors group {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <i class="bi bi-house text-lg {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'group-hover:text-blue-600' }}"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('documents.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors group {{ request()->routeIs('documents.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <i class="bi bi-file-earmark-text text-lg {{ request()->routeIs('documents.*') ? 'text-blue-600' : 'group-hover:text-blue-600' }}"></i>
                            <span class="font-medium">Documents</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('documents.create') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors group {{ request()->routeIs('documents.create') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <i class="bi bi-plus-circle text-lg {{ request()->routeIs('documents.create') ? 'text-blue-600' : 'group-hover:text-blue-600' }}"></i>
                            <span class="font-medium">Create Document</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('verify.index') }}" class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors group {{ request()->routeIs('verify.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                            <i class="bi bi-shield-check text-lg {{ request()->routeIs('verify.*') ? 'text-blue-600' : 'group-hover:text-blue-600' }}"></i>
                            <span class="font-medium">Verify Document</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            @auth
            <!-- User Profile Section -->
            <div class="absolute bottom-0 w-full p-4 border-t border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <div class="relative">
                        <button id="userMenuButton" class="p-1 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <div id="userMenu" class="hidden absolute bottom-full right-0 mb-2 w-48 py-1 bg-white rounded-md shadow-lg border border-gray-200">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="bi bi-person mr-2"></i>Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="bi bi-box-arrow-right mr-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endauth
        </div>
        
        <!-- Mobile Overlay -->
        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 h-16 flex items-center justify-between px-6 lg:px-8">
                <div class="flex items-center space-x-4">
                    <button id="openSidebar" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-900">
                        @hasSection('page-title')
                            @yield('page-title')
                        @else
                            Dashboard
                        @endif
                    </h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button class="p-2 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="bi bi-bell text-lg"></i>
                    </button>
                    
                    <!-- Search -->
                    <div class="hidden md:block relative">
                        <input type="search" placeholder="Search..." class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="bi bi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-auto p-6 lg:p-8">
                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="bi bi-check-circle text-green-400 mr-2"></i>
                            <p class="text-sm text-green-800">{{ session('success') }}</p>
                            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-green-400 hover:text-green-600">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="bi bi-exclamation-circle text-red-400 mr-2"></i>
                            <p class="text-sm text-red-800">{{ session('error') }}</p>
                            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-600">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Mobile sidebar functionality
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const openSidebar = document.getElementById('openSidebar');
        const closeSidebar = document.getElementById('closeSidebar');

        openSidebar.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        });

        closeSidebar.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        // User menu functionality
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenu = document.getElementById('userMenu');

        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', () => {
                userMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (event) => {
                if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>