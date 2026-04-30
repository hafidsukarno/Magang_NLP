@section('title', 'Pengajuan')
<x-app-layout>

    <div class="px-2 md:px-4 py-4">

        <!-- Header + Filters -->
        <div class="mb-10">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h3 class="text-3xl font-extrabold text-gray-900 tracking-tight">Daftar Semua Pengajuan</h3>
                    <p class="text-gray-500 mt-1 text-sm">Manajemen dan pantau seluruh status pengajuan magang.</p>
                </div>
                
                <form method="GET" action="{{ route('hrd.applications.index') }}"
                    class="bg-white p-2 border border-gray-200 rounded-2xl shadow-sm flex flex-col md:flex-row items-center gap-2 w-full lg:w-auto">
                    
                    {{-- Search Field --}}
                    <div class="relative w-full md:w-64">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari Leader, Major, Dept..."
                            class="pl-10 pr-4 py-2 w-full bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>

                    <div class="h-8 w-px bg-gray-200 hidden md:block"></div>

                    {{-- Dropdown Filters Group --}}
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select name="type" onchange="this.form.submit()" 
                            class="flex-1 md:w-36 px-3 py-2 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 text-sm outline-none cursor-pointer">
                            <option value="">Semua Tipe</option>
                            <option value="individual" @selected(request('type') == 'individual')>Individual</option>
                            <option value="group" @selected(request('type') == 'group')>Group</option>
                        </select>

                        <select name="status" onchange="this.form.submit()" 
                            class="flex-1 md:w-36 px-3 py-2 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 text-sm outline-none cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="menunggu" @selected(request('status') == 'menunggu')>Menunggu</option>
                            <option value="diproses" @selected(request('status') == 'diproses')>Diproses</option>
                            <option value="diterima" @selected(request('status') == 'diterima')>Diterima</option>
                            <option value="ditolak" @selected(request('status') == 'ditolak')>Ditolak</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full md:w-auto px-5 py-2 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-sm text-sm">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
            <table class="w-full text-sm border-separate border-spacing-0">
                <thead class="bg-gray-50/80 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                    <tr>
                        <th class="px-4 py-4 border-b border-gray-100 text-center rounded-tl-xl">No</th>
                        <th class="px-4 py-4 border-b border-gray-100 text-left">Kode Pengajuan</th>
                        <th class="px-4 py-4 border-b border-gray-100 text-center">Tipe</th>
                        <th class="px-4 py-4 border-b border-gray-100 text-left">Nama / Anggota</th>
                        <th class="px-4 py-4 border-b border-gray-100 text-left">Major</th>
                        <th class="px-4 py-4 border-b border-gray-100 text-left">Departemen</th>
                        <th class="px-4 py-4 border-b border-gray-100 text-center">Status</th>
                        <th class="px-4 py-4 border-b border-gray-100 text-center rounded-tr-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    @foreach ($applications as $a)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-4 py-4 border-b border-gray-50 text-center font-medium text-gray-500">
                                {{ $loop->iteration + ($applications->firstItem() - 1) }}
                            </td>
                            <td class="px-3 py-2 border font-semibold text-gray-800">
                                {{ $a->registration_code }}
                            </td>
                            <td class="px-3 py-2 border text-center">
                                <span class="px-2 py-1 text-[10px] font-bold rounded-lg uppercase
                                    @if ($a->type == 'group') bg-blue-100 text-blue-700
                                    @else bg-purple-100 text-purple-700 @endif">
                                    {{ ucfirst($a->type) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 border font-medium text-left">
                                @if ($a->type === 'group')
                                    <div class="text-sm">
                                        <div class="font-semibold text-gray-900"><span class="text-blue-600">Ketua:</span> {{ $a->leader_name }}</div>
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
                            <td class="px-3 py-2 border text-left">{{ $a->major }}</td>
                            <td class="px-3 py-2 border text-left">{{ $a->department->name ?? '-' }}</td>
                            <td class="px-3 py-2 border text-center">
                                <div class="flex flex-col items-center gap-1">
                                    {{-- Global Status --}}
                                    @php
                                        $statusColor = match($a->status) {
                                            'menunggu' => 'bg-yellow-100 text-yellow-700',
                                            'diterima' => 'bg-green-100 text-green-700',
                                            'ditolak' => 'bg-red-100 text-red-700',
                                            default => 'bg-blue-100 text-blue-700'
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded uppercase tracking-wider {{ $statusColor }}">
                                        {{ $a->status }}
                                    </span>

                                    @if ($a->type === 'group')
                                        <div class="flex items-center gap-2 mt-1">
                                            {{-- Ketua status --}}
                                            <div class="flex flex-col items-center">
                                                <span class="text-[8px] text-gray-400 font-bold">K</span>
                                                @php
                                                    $leaderColor = match($a->leader_status) {
                                                        'diterima' => 'bg-green-50 text-green-600 border-green-100',
                                                        'ditolak' => 'bg-red-50 text-red-600 border-red-100',
                                                        default => 'bg-yellow-50 text-yellow-600 border-yellow-100'
                                                    };
                                                @endphp
                                                <span class="px-1 py-0.5 rounded text-[9px] font-bold border {{ $leaderColor }}">
                                                    {{ strtoupper(substr($a->leader_status, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div class="w-px h-5 bg-gray-200"></div>
                                            {{-- Member Summary --}}
                                            <div class="flex flex-col items-center">
                                                <span class="text-[8px] text-gray-400 font-bold">A</span>
                                                @php
                                                    $acc = $a->members->where('status', 'diterima')->count();
                                                    $tot = $a->members->count();
                                                @endphp
                                                <span class="text-[9px] font-bold text-gray-600">{{ $acc }}/{{ $tot }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2 border text-center">
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
