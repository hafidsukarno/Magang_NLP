@section('title', 'Reset Password')
<x-guest-layout>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    <div class="flex items-center justify-center bg-white px-4 py-5">

        <div class="w-full max-w-md bg-white rounded-2xl p-8">

            <!-- HEADER -->
            <div class="flex flex-col items-center mb-6">
                <img src="img/logo_pag.jpeg" alt="Logo PAG" width="170" class="d-block mx-auto mb-3">
                <h2 class="text-2xl font-bold text-gray-800 mt-3">SIREMA-PAG | Reset Password</h2>
                <p class="text-gray-500 text-sm">Buat password baru untuk akun Anda</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-1">
                        Email
                    </label>
                    <div class="flex items-center border rounded-lg px-3 py-2 bg-gray-50">
                        <i data-lucide="mail" class="w-5 h-5 text-gray-500 mr-2"></i>
                        <input id="email" type="email" name="email" :value="old('email', $request->email)"
                               class="w-full bg-transparent outline-none border rounded-lg px-3 py-2"
                               placeholder="your@email.com" required autofocus autocomplete="username" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium mb-1">
                        Password Baru
                    </label>
                    <div class="flex items-center border rounded-lg px-3 py-2 bg-gray-50">
                        <i data-lucide="lock" class="w-5 h-5 text-gray-500 mr-2"></i>
                        <input id="password" type="password" name="password"
                               class="w-full bg-transparent outline-none border rounded-lg px-3 py-2"
                               placeholder="••••••••" required autocomplete="new-password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">
                        Konfirmasi Password
                    </label>
                    <div class="flex items-center border rounded-lg px-3 py-2 bg-gray-50">
                        <i data-lucide="lock" class="w-5 h-5 text-gray-500 mr-2"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="w-full bg-transparent outline-none border rounded-lg px-3 py-2"
                               placeholder="••••••••" required autocomplete="new-password" />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex flex-col gap-3">
                    <button
                        class="flex items-center justify-center gap-2 bg-blue-600 text-white w-full py-2 rounded-lg hover:bg-blue-700 transition shadow">
                        <i data-lucide="key" class="w-5 h-5"></i>
                        Reset Password
                    </button>

                    <a href="{{ route('login') }}"
                        class="text-sm text-center text-gray-600 hover:text-blue-600 transition font-medium">
                        <span class="text-blue-600 hover:underline">Kembali ke Login</span>
                    </a>
                </div>
            </form>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</x-guest-layout>
