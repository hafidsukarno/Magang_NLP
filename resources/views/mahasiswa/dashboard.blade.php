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
    <div class="grid grid-cols-5 gap-4 mb-6">
        <div class="bg-blue-100 p-4 rounded-lg border-l-4 border-blue-500">
            <p class="text-gray-600 text-sm">Total Pengajuan</p>
            <p class="text-2xl font-bold text-blue-600">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg border-l-4 border-yellow-500">
            <p class="text-gray-600 text-sm">Menunggu</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $summary['menunggu'] }}</p>
        </div>
        <div class="bg-purple-100 p-4 rounded-lg border-l-4 border-purple-500">
            <p class="text-gray-600 text-sm">Diproses</p>
            <p class="text-2xl font-bold text-purple-600">{{ $summary['diproses'] }}</p>
        </div>
        <div class="bg-green-100 p-4 rounded-lg border-l-4 border-green-500">
            <p class="text-gray-600 text-sm">Diterima</p>
            <p class="text-2xl font-bold text-green-600">{{ $summary['diterima'] }}</p>
        </div>
        <div class="bg-red-100 p-4 rounded-lg border-l-4 border-red-500">
            <p class="text-gray-600 text-sm">Ditolak</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['ditolak'] }}</p>
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
                        <td class="border p-2">{{ $app->university }}</td>
                        <td class="border p-2">{{ $app->department?->name ?? '-' }}</td>
                        <td class="border p-2">
                            <span class="px-2 py-1 text-sm rounded text-white
                                @if($app->status === 'menunggu') bg-yellow-500
                                @elseif($app->status === 'diproses') bg-purple-500
                                @elseif($app->status === 'diterima') bg-green-500
                                @elseif($app->status === 'ditolak') bg-red-500
                                @endif
                            ">
                                {{ ucfirst($app->status) }}
                            </span>
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
                        <td colspan="6" class="border p-4 text-center text-gray-500">
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
