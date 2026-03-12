@section('title', 'Dashboard Mahasiswa')
<x-app-layout>
<div class="p-6">
    <!-- BREADCRUMB -->
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-600">
        <span class="font-semibold text-gray-800">Mahasiswa</span>
        <span>/</span>
        <span class="font-semibold text-gray-800">Dashboard</span>
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-4">Dashboard Pengajuan Magang</h1>
        
        <!-- TAB NAVIGATION -->
        <div class="flex border-b border-gray-300">
            <a href="{{ route('mahasiswa.dashboard') }}" class="px-6 py-3 font-semibold text-blue-600 border-b-2 border-blue-600">
                Dashboard
            </a>
            <a href="{{ route('mahasiswa.applications.index') }}" class="px-6 py-3 font-semibold text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-gray-300">
                Riwayat Pengajuan
            </a>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-100 p-4 rounded-lg border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm">Total Pengajuan</p>
            <p class="text-2xl font-bold text-blue-600">{{ $summary['total'] }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ $summary['total_individual'] }} Individual · {{ $summary['total_group'] }} Group</p>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg border-l-4 border-yellow-500">
            <p class="text-gray-600 text-sm">Menunggu Review</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $summary['menunggu'] }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ $summary['menunggu_individual'] }} Individual · {{ $summary['menunggu_group'] }} Group</p>
        </div>
        <div class="bg-green-100 p-4 rounded-lg border-l-4 border-green-500">
            <p class="text-gray-600 text-sm">Diterima</p>
            <p class="text-2xl font-bold text-green-600">{{ $summary['diterima_count'] }}</p>
            <p class="text-xs text-green-600 mt-1 font-semibold">{{ $summary['diterima'] }} aplikasi</p>
        </div>
        <div class="bg-red-100 p-4 rounded-lg border-l-4 border-red-500">
            <p class="text-gray-600 text-sm">Ditolak</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['ditolak_count'] }}</p>
            <p class="text-xs text-red-600 mt-1 font-semibold">{{ $summary['ditolak'] }} aplikasi</p>
        </div>
    </div>

    <!-- TOMBOL BUAT PENGAJUAN -->
    <a href="{{ route('apply.form') }}" class="px-4 py-2 bg-green-600 text-white rounded mb-4 inline-block hover:bg-green-700">
        + Buat Pengajuan Baru
    </a>

    <!-- DAFTAR PENGAJUAN TERBARU -->
    <div class="mt-6">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-bold">Pengajuan Terbaru</h2>
            <a href="{{ route('mahasiswa.applications.index') }}" class="text-blue-600 hover:underline text-sm">Lihat semua →</a>
        </div>
        <table class="w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">Kode</th>
                    <th class="border p-2">Tipe</th>
                    <th class="border p-2">Universitas</th>
                    <th class="border p-2">Departemen</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Tanggal</th>
                    <th class="border p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                    <tr>
                        <td class="border p-2 font-mono text-sm">{{ $app->registration_code }}</td>
                        <td class="border p-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded
                                @if($app->type === 'individual') bg-purple-100 text-purple-700
                                @else bg-blue-100 text-blue-700
                                @endif">
                                {{ ucfirst($app->type) }}
                            </span>
                        </td>
                        <td class="border p-2">{{ $app->university }}</td>
                        <td class="border p-2">{{ $app->department?->name ?? '-' }}</td>
                        <td class="border p-2">
                            @if ($app->type === 'individual')
                                {{-- Individual: tampilkan leader_status --}}
                                <span class="px-2 py-1 text-sm rounded text-white
                                    @if($app->leader_status === 'menunggu') bg-yellow-500
                                    @elseif($app->leader_status === 'diterima') bg-green-500
                                    @elseif($app->leader_status === 'ditolak') bg-red-500
                                    @endif
                                ">
                                    {{ ucfirst($app->leader_status ?? 'menunggu') }}
                                </span>
                            @else
                                {{-- Group: tampilkan ringkasan leader & members dengan breakdown --}}
                                <div class="text-xs space-y-1">
                                    <div>
                                        <span class="font-semibold">K:</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold
                                            @if($app->leader_status === 'menunggu') bg-yellow-100 text-yellow-700
                                            @elseif($app->leader_status === 'diterima') bg-green-100 text-green-700
                                            @elseif($app->leader_status === 'ditolak') bg-red-100 text-red-700
                                            @endif">
                                            {{ ucfirst(substr($app->leader_status ?? 'menunggu', 0, 1)) }}
                                        </span>
                                    </div>
                                    @php
                                        $diterima = $app->members->where('status', 'diterima')->count();
                                        $ditolak = $app->members->where('status', 'ditolak')->count();
                                        $total = $app->members->count();
                                    @endphp
                                    <div class="text-gray-700">
                                        A: <span class="font-semibold">{{ $diterima }}/{{ $total }}</span>
                                        @if($ditolak > 0)
                                            <span class="text-red-600"> -{{ $ditolak }} tolak</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td class="border p-2 text-sm">{{ $app->created_at->format('d/m/Y') }}</td>
                        <td class="border p-2">
                            <a href="{{ route('mahasiswa.applications.show', $app->id) }}" class="text-blue-600 hover:underline">
                                Lihat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="border p-4 text-center text-gray-500">
                            Belum ada pengajuan. <a href="{{ route('apply.form') }}" class="text-blue-600 hover:underline">Buat sekarang</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $applications->links() }}
    </div>
</div>
</x-app-layout>
