@section('title', 'Pengajuan')
<x-app-layout>

    <div class="px-2 md:px-4 py-4">

        <!-- Header + Search -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <h3 class="text-3xl font-bold text-gray-800">Daftar Semua Pengajuan</h3>
            <form method="GET" action="{{ route('hrd.applications.index') }}"
                class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari Leader, Major, Dept..."
                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition w-full sm:w-auto">Cari</button>
                <!-- Tombol Reset -->
                <a href="{{ route('hrd.applications.index') }}"
                    class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition w-full sm:w-auto text-center">
                    Reset
                </a>
            </form>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto bg-white shadow-md rounded-xl p-4 sm:p-6 border border-gray-100">
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden min-w-[700px]">
                <thead class="bg-gray-50 text-gray-700 text-center">
                    <tr>
                        <th class="px-3 py-2 border">No</th>
                        <th class="px-10 py-2 border">Kode Pengajuan</th>
                        <th class="px-3 py-2 border">Tipe</th>
                        <th class="px-3 py-2 border">Nama / Anggota</th>
                        <th class="px-3 py-2 border">Major</th>
                        <th class="px-3 py-2 border">Departemen</th>
                        {{-- <th class="px-3 py-2 border">Score</th> --}}
                        <th class="px-3 py-2 border">Status</th>
                        <th class="px-3 py-2 border">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-center">
                    @foreach ($applications as $a)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 py-2 border">{{ $a->id }}</td>
                            <td class="px-3 py-2 border font-semibold text-gray-800">
                                {{ $a->registration_code }}
                            </td>
                            <td class="px-3 py-2 border">
                                <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if ($a->type == 'group') bg-blue-100 text-blue-700
                                    @else bg-purple-100 text-purple-700 @endif">
                                    {{ ucfirst($a->type) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 border font-medium text-left">
                                @if ($a->type === 'group')
                                    <div class="text-sm">
                                        <div class="font-semibold text-gray-900">👤 {{ $a->leader_name }}</div>
                                        @if ($a->members && $a->members->count() > 0)
                                            <div class="text-gray-600 mt-1">
                                                @foreach ($a->members as $member)
                                                    <div class="text-xs">• {{ $member->name }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{ $a->leader_name }}
                                @endif
                            </td>
                            <td class="px-3 py-2 border">{{ $a->major }}</td>
                            <td class="px-3 py-2 border">{{ $a->department->name ?? '-' }}</td>
                            {{-- <td class="px-3 py-2 border">
                            @if (is_numeric($a->score))
                                @if ($a->score >= 80)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-semibold">
                                        {{ $a->score }}
                                    </span>
                                @elseif($a->score >= 50)
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-semibold">
                                        {{ $a->score }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-semibold">
                                        {{ $a->score }}
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold">-</span>
                            @endif
                        </td> --}}
                            <td class="px-3 py-2 border">
                                @if ($a->type === 'individual')
                                    {{-- Individual: tampilkan leader_status --}}
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-lg
                                    @if ($a->leader_status == 'menunggu') bg-yellow-100 text-yellow-700
                                    @elseif($a->leader_status == 'diterima') bg-green-100 text-green-700
                                    @elseif($a->leader_status == 'ditolak') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucfirst($a->leader_status) }}
                                    </span>
                                @else
                                    {{-- Group: tampilkan status leader dan ringkasan anggota --}}
                                    <div class="text-xs space-y-1">
                                        <div>
                                            <span class="font-semibold">Ketua:</span>
                                            <span
                                                class="px-2 py-0.5 rounded text-xs font-semibold ml-1
                                            @if ($a->leader_status == 'menunggu') bg-yellow-100 text-yellow-700
                                            @elseif($a->leader_status == 'diterima') bg-green-100 text-green-700
                                            @elseif($a->leader_status == 'ditolak') bg-red-100 text-red-700
                                            @else bg-gray-100 text-gray-700 @endif">
                                                {{ ucfirst($a->leader_status) }}
                                            </span>
                                        </div>
                                        @php
                                            $diterima = $a->members->where('status', 'diterima')->count();
                                            $ditolak = $a->members->where('status', 'ditolak')->count();
                                            $menunggu = $a->members->where('status', 'menunggu')->count();
                                            $total = $a->members->count();
                                        @endphp
                                        <div class="text-gray-700">
                                            Anggota: <span class="font-semibold">{{ $diterima }}/{{ $total }}</span>
                                            @if ($ditolak > 0)
                                                <span class="text-red-600">{{ $ditolak }} tolak</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2 border">
                                <a href="{{ route('hrd.application.show', $a->id) }}"
                                    class="inline-flex items-center gap-1 text-blue-600 font-semibold hover:underline">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($errors->has('quota') || $errors->has('general'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: `{!! addslashes($errors->first('quota') ?: $errors->first('general')) !!}`,
                            confirmButtonColor: '#ef4444'
                        });
                    });
                </script>
            @endif


            <!-- Pagination -->
            <div class="mt-4">
                {{ $applications->withQueryString()->links() }}
            </div>
        </div>

    </div>

    <!-- Init Lucide -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();
        });
    </script>

</x-app-layout>
