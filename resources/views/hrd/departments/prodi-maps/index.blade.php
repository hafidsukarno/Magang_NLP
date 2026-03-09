@section('title', 'Mapping Prodi')
<x-app-layout>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    <div class="p-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">

            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-800">
                    Mapping Prodi – {{ $department->name }}
                </h1>
            </div>

            <!-- Tombol Kembali -->
            <a href="{{ route('departments.index') }}"
               class="hidden sm:flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                Kembali
            </a>

        </div>

        <!-- Tombol kembali (mobile floating) -->
        <a href="{{ route('departments.index') }}"
            class="sm:hidden fixed bottom-6 left-6 bg-gray-700 text-white p-4 rounded-full shadow-xl hover:bg-gray-800 transition z-50">
            <i data-lucide="arrow-left"></i>
        </a>

        <!-- FORM TAMBAH PRODI -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">

            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-blue-600"></i>
                Tambah Keyword Prodi
            </h2>

            <form method="POST" action="{{ route('prodi-maps.store', $department->id) }}">
                @csrf

                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" name="prodi_keyword"
                        class="border rounded px-3 py-2 flex-1"
                        placeholder="Masukkan keyword prodi..." required>

                    <button class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i data-lucide="plus"></i>
                        <span class="hidden sm:inline">Tambah</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- LIST MAPPING -->
        <div class="bg-white shadow rounded-lg p-4">

            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <i data-lucide="list-checks" class="w-5 h-5 text-gray-700"></i>
                Daftar Mapping Prodi
            </h2>

            @if ($maps->count() == 0)
                <p class="text-gray-500 text-sm">Belum ada mapping prodi.</p>
            @else
                <ul class="space-y-3">

                    @foreach ($maps as $map)
                        <li class="flex items-center justify-between border rounded-lg p-3 hover:bg-gray-50 transition">

                            <div class="flex items-center gap-2">
                                <i data-lucide="bookmark" class="w-4 h-4 text-gray-600"></i>
                                <span class="text-gray-800">{{ $map->prodi_keyword }}</span>
                            </div>

                            <form method="POST" action="{{ route('prodi-maps.destroy', [$department->id, $map->id]) }}">
                                @csrf
                                @method('DELETE')

                                <button class="text-red-600 hover:text-red-700 p-1">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </form>

                        </li>
                    @endforeach

                </ul>
            @endif

        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>

</x-app-layout>
