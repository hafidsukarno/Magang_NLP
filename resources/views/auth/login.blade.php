@section('title', 'Login')
<x-guest-layout>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    <div class="flex items-center justify-center bg-white px-4 py-5">

        <div class="w-full max-w-md bg-white rounded-2xl p-8">

            <!-- HEADER -->
            <div class="flex flex-col items-center mb-6">
                <img src="img/logo_pag.jpeg" alt="Logo PAG" width="170" class="d-block mx-auto mb-3">
                <h2 class="text-2xl font-bold text-gray-800 mt-3">SIREMA-PAG | Login</h2>
                <p class="text-gray-500 text-sm">Silakan login menggunakan akun Anda</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- FORM LOGIN -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- EMAIL -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-1">
                        Email
                    </label>
                    <div class="flex items-center border rounded-lg px-3 py-2 bg-gray-50">
                        <i data-lucide="mail" class="w-5 h-5 text-gray-500 mr-2"></i>
                        <input id="email" type="email" name="email"
                            class="w-full bg-transparent outline-none border rounded-lg px-3 py-2"
                            placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
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

                <!-- LUPA PASSWORD LINK -->
                @if (Route::has('password.request'))
                    <div class="mb-4 text-right">
                        <a href="{{ route('password.request') }}"
                            class="text-sm text-gray-600 hover:text-blue-600 transition font-medium">
                            <span class="text-blue-600 hover:underline">Lupa Password?</span>
                        </a>
                    </div>
                @endif

                <!-- LOGIN BUTTON -->
                <div class="flex flex-col gap-3">
                    <button
                        class="flex items-center justify-center gap-2 bg-blue-600 text-white w-full py-2 rounded-lg hover:bg-blue-700 transition shadow">
                        <i data-lucide="arrow-right-circle" class="w-5 h-5"></i>
                        Masuk
                    </button>

                    <!-- LINK DAFTAR -->
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="text-sm text-center text-gray-600 hover:text-blue-600 transition font-medium">
                            Belum punya akun? <span class="text-blue-600 hover:underline">Daftar di sini</span>
                        </a>
                    @endif

                </div>

            </form>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</x-guest-layout>
