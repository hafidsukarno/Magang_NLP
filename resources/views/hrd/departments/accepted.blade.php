@section('title', 'Pengajuan Diterima')
<x-app-layout>

    <div class="px-2 md:px-4 py-4">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-3xl font-bold text-gray-800">
                    Pengajuan Diterima
                </h3>
                <p class="text-gray-600 mt-1">
                    Departemen: <strong>{{ $department->name }}</strong>
                </p>
            </div>

            <a href="{{ route('departments.index') }}"
                class="hidden sm:flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                Kembali
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto bg-white shadow-md rounded-xl p-4 sm:p-6 border border-gray-100">
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden min-w-[700px]">
                <thead class="bg-gray-50 text-gray-700 text-center">
                    <tr>
                        <th class="px-3 py-2 border">ID</th>
                        <th class="px-3 py-2 border">Leader</th>
                        <th class="px-3 py-2 border">Major</th>
                        <th class="px-3 py-2 border">Univ</th>
                        <th class="px-3 py-2 border">Periode</th>
                        <th class="px-3 py-2 border">Status</th>
                        <th class="px-3 py-2 border">Aksi</th>
                    </tr>
                </thead>


                <tbody class="text-gray-700 text-center">
                    @forelse($applications as $a)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 py-2 border">{{ $a->id }}</td>
                            <td class="px-3 py-2 border font-medium">{{ $a->leader_name }}</td>
                            <td class="px-3 py-2 border">{{ $a->major }}</td>
                            <td class="px-3 py-2 border">{{ $a->university }}</td>

                            <!-- Periode -->
                            <td class="px-3 py-2 border">
                                @if ($a->period_start && $a->period_end)
                                    {{ \Carbon\Carbon::parse($a->period_start)->format('d M Y') }} -
                                    {{ \Carbon\Carbon::parse($a->period_end)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-3 py-2 border">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-lg
                @if ($a->status === 'diterima') bg-green-100 text-green-700
                @elseif($a->status === 'selesai')
                    bg-gray-100 text-gray-700
                @else
                    bg-blue-100 text-blue-700 @endif
            ">
                                    {{ ucfirst($a->status) }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-3 py-2 border">
                                <a href="{{ route('hrd.application.show', $a->id) }}"
                                    class="inline-flex items-center gap-1 text-blue-600 font-semibold hover:underline">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-gray-500">
                                Belum ada mahasiswa diterima di departemen ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $applications->links() }}
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();
        });
    </script>

</x-app-layout>
