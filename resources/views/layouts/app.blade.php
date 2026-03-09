<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SIREMA-PAG | @yield('title')</title>

    <link rel="icon" href="/img/logo_sirema.png" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex overflow-x-hidden">

        @php
            $isAdmin = auth()->user()->role === 'admin';
            $isHrd = auth()->user()->role === 'hrd';
            $isMahasiswa = auth()->user()->role === 'mahasiswa';
        @endphp

        <!-- DESKTOP SIDEBAR -->
        <aside
            class="hidden md:flex flex-col w-64 bg-gray-900 text-gray-100 shadow-xl fixed left-0 top-0 h-screen overflow-y-auto">

            <!-- Logo  -->
            <div class="flex items-center justify-center">
                <img src="/img/logo.png" width="180" alt="Logo">
            </div>

            <!-- Panel Title berubah sesuai role -->
            {{-- <div class="px-6 py-5 border-b border-gray-700">

                <!-- Nama Panel -->
                <div class="mt-1 text-lg font-semibold text-gray-300">
                    {{ $isAdmin ? 'Admin Panel' : 'HRD Panel' }}
                </div>

            </div> --}}

            <nav class="flex flex-col p-4 text-gray-200 h-full">

                <!-- MENU ADMIN -->
                @if ($isAdmin)
                    <div>
                        <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Admin Menu</h6>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('admin.users.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="users" class="w-5 h-5"></i> Manage Users
                            </a>
                        </div>
                    </div>
                @elseif ($isHrd)
                    <!-- MENU HRD -->
                    <div>
                        <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Menu Utama</h6>
                        <div class="flex flex-col space-y-2">

                            <a href="{{ route('hrd.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('hrd.dashboard') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                            </a>

                            <a href="{{ route('departments.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('departments.*') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="layers" class="w-5 h-5"></i> Departments
                            </a>

                            <a href="{{ route('hrd.applications.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('hrd.applications.*') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="file-text" class="w-5 h-5"></i> Daftar Pengajuan
                            </a>
                        </div>
                    </div>
                @elseif ($isMahasiswa)
                    <!-- MENU MAHASISWA -->
                    <div>
                        <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Menu Utama</h6>
                        <div class="flex flex-col space-y-2">

                            <a href="{{ route('mahasiswa.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                            </a>

                            <a href="{{ route('mahasiswa.applications.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('mahasiswa.applications.*') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="file-text" class="w-5 h-5"></i> Riwayat Pengajuan
                            </a>
                        </div>
                    </div>
                @endif

                <!-- MENU PROFILE -->
                <div>
                    <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Profile</h6>
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                      {{ request()->routeIs('profile.edit') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                        <i data-lucide="user" class="w-5 h-5"></i> Profile
                    </a>
                </div>

                <!-- MENU LOGOUT -->
                <!-- MENU LOGOUT -->
                <div class="mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-700 bg-red-600 text-white w-full">
                            <i data-lucide="log-out" class="w-5 h-5"></i> Logout
                        </button>
                    </form>
                </div>

            </nav>
        </aside>


        <!-- ========================= MOBILE SIDEBAR ========================= -->

        <div id="mobileSidebar"
            class="fixed top-0 left-0 w-64 bg-gray-900 text-gray-100 h-full shadow-xl transform -translate-x-full transition-transform duration-300 z-50">

            <div class="px-6 py-5 text-xl font-bold border-b border-gray-700">
                @if ($isAdmin)
                    Admin Panel
                @elseif ($isHrd)
                    HRD Panel
                @elseif ($isMahasiswa)
                    Mahasiswa Panel
                @endif
            </div>

            <nav class="flex flex-col p-4 text-gray-200 space-y-4">

                <!-- MOBILE ADMIN -->
                @if ($isAdmin)
                    <div>
                        <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Admin Menu</h6>
                        <div class="flex flex-col space-y-2">

                            <a href="{{ route('admin.users.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="users" class="w-5 h-5"></i> Manage Users
                            </a>

                        </div>
                    </div>
                @elseif ($isHrd)
                    <!-- MOBILE HRD -->
                    <div>
                        <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Menu Utama</h6>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('hrd.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('hrd.dashboard') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                            </a>

                            <a href="{{ route('departments.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('departments.*') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="layers" class="w-5 h-5"></i> Departments
                            </a>

                            <a href="{{ route('hrd.applications.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('hrd.applications.*') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="file-text" class="w-5 h-5"></i> Daftar Pengajuan
                            </a>
                        </div>
                    </div>
                @elseif ($isMahasiswa)
                    <!-- MOBILE MAHASISWA -->
                    <div>
                        <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Menu Utama</h6>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('mahasiswa.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                            </a>

                            <a href="{{ route('mahasiswa.applications.index') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('mahasiswa.applications.*') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="file-text" class="w-5 h-5"></i> Riwayat Pengajuan
                            </a>
                        </div>
                    </div>
                @else
                    <!-- MOBILE HRD (Fallback) -->
                    <div>
                        <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Menu Utama</h6>
                        <div class="flex flex-col space-y-2">
                            <a href="{{ route('hrd.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                                  {{ request()->routeIs('hrd.dashboard') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
                            </a>
                        </div>
                    </div>
                @endif


                <!-- MOBILE PROFILE -->
                <div>
                    <h6 class="text-gray-400 uppercase text-xs font-semibold mb-2">Profile</h6>
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                          {{ request()->routeIs('profile.edit') ? 'bg-gray-800 text-white font-semibold' : 'text-gray-200 hover:bg-gray-700' }}">
                        <i data-lucide="user" class="w-5 h-5"></i> Profile
                    </a>
                </div>

                <!-- MOBILE LOGOUT -->
                <div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex items-center gap-3 px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 w-full">
                            <i data-lucide="log-out" class="w-5 h-5"></i> Logout
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <!-- BACKDROP -->
        <div id="mobileBackdrop"
            class="fixed inset-0 bg-black/40 hidden md:hidden z-40 backdrop-blur-sm transition-opacity duration-300">
        </div>

        <!-- MOBILE TOGGLE -->
        <button id="mobileToggle"
            class="md:hidden fixed top-3 left-3 z-[9999] bg-gray-900 text-white p-2 rounded-lg shadow-lg">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <!-- MAIN CONTENT -->
        <main id="mainContent" class="flex-1 p-4 md:p-6 w-full transition-all duration-300 md:ml-64">
            {{ $slot }}
        </main>

    </div>

    <style>
        #mobileSidebar {
            will-change: transform;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();

            const sidebar = document.getElementById("mobileSidebar");
            const btn = document.getElementById("mobileToggle");
            const backdrop = document.getElementById("mobileBackdrop");
            const mainContent = document.getElementById("mainContent");

            function openSidebar() {
                sidebar.classList.remove("-translate-x-full");
                backdrop.classList.remove("hidden");
                mainContent.classList.add("blur-sm");
                btn.innerHTML = '<i data-lucide="x" class="w-6 h-6"></i>';
                lucide.createIcons();
            }

            function closeSidebar() {
                sidebar.classList.add("-translate-x-full");
                backdrop.classList.add("hidden");
                mainContent.classList.remove("blur-sm");
                btn.innerHTML = '<i data-lucide="menu" class="w-6 h-6"></i>';
                lucide.createIcons();
            }

            btn.addEventListener("click", () => {
                if (sidebar.classList.contains("-translate-x-full")) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            backdrop.addEventListener("click", closeSidebar);
        });
    </script>

</body>

</html>
