@section('title', 'Register')
<x-guest-layout>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    <div class="flex items-center justify-center bg-white px-4 py-5">

        <div class="w-full max-w-md bg-white rounded-2xl p-8">

            <!-- HEADER -->
            <div class="flex flex-col items-center mb-6">
                <img src="img/logo_pag.jpeg" alt="Logo PAG" width="170" class="d-block mx-auto mb-3">
                <h2 class="text-2xl font-bold text-gray-800 mt-3">SIREMA-PAG | Register</h2>
                <p class="text-gray-500 text-sm">Lengkapi data untuk membuat akun</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- NAME -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium mb-1">
                        Nama Lengkap
                    </label>
                    <div class="flex items-center border rounded-lg px-3 py-2 bg-gray-50">
                        <i data-lucide="user" class="w-5 h-5 text-gray-500 mr-2"></i>
                        <input id="name" type="text" name="name"
                               class="w-full bg-transparent outline-none border rounded-lg px-3 py-2"
                               placeholder="John Doe"
                               value="{{ old('name') }}" required autofocus>
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- EMAIL -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-1">
                        Email
                    </label>
                    <div class="flex items-center border rounded-lg px-3 py-2 bg-gray-50">
                        <i data-lucide="mail" class="w-5 h-5 text-gray-500 mr-2"></i>
                        <input id="email" type="email" name="email"
                               class="w-full bg-transparent outline-none border rounded-lg px-3 py-2"
                               placeholder="you@example.com"
                               value="{{ old('email') }}" required>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- PASSWORD -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium mb-1">
                        Password
                    </label>
                    <div class="flex items-center border rounded-lg px-3 py-2 bg-gray-50">
                        <i data-lucide="lock" class="w-5 h-5 text-gray-500 mr-2"></i>
                        <input id="password" type="password" name="password"
                               class="w-full bg-transparent outline-none border rounded-lg px-3 py-2"
                               placeholder="••••••••" required>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- CONFIRM PASSWORD -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">
                        Konfirmasi Password
                    </label>
                    <div class="flex items-center border rounded-lg px-3 py-2 bg-gray-50">
                        <i data-lucide="lock" class="w-5 h-5 text-gray-500 mr-2"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="w-full bg-transparent outline-none border rounded-lg px-3 py-2"
                               placeholder="••••••••" required>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- BUTTON + LINK -->
                <div class="flex flex-col gap-3">

                    <button
                        class="flex items-center justify-center gap-2 bg-blue-600 text-white w-full py-2 rounded-lg hover:bg-blue-700 transition shadow">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        Daftar
                    </button>

                    <a href="{{ route('login') }}"
                       class="text-sm text-center text-gray-600 hover:text-blue-600 transition font-medium">
                        Sudah punya akun? <span class="text-blue-600 hover:underline">Masuk di sini</span>
                    </a>

                </div>

            </form>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</x-guest-layout>
