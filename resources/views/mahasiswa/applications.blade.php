@section('title', 'Riwayat Pengajuan')
<x-app-layout>
<div class="p-6">
    <!-- BREADCRUMB -->
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-600">
        <span class="font-semibold text-gray-800">Mahasiswa</span>
        <span>/</span>
        <span class="font-semibold text-gray-800">Riwayat Pengajuan</span>
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-4">Riwayat Pengajuan Magang</h1>
        
        <!-- TAB NAVIGATION -->
        <div class="flex border-b border-gray-300">
            <a href="{{ route('mahasiswa.dashboard') }}" class="px-6 py-3 font-semibold text-gray-600 hover:text-blue-600 border-b-2 border-transparent hover:border-gray-300">
                Dashboard
            </a>
            <a href="{{ route('mahasiswa.applications.index') }}" class="px-6 py-3 font-semibold text-blue-600 border-b-2 border-blue-600">
                Riwayat Pengajuan
            </a>
        </div>
    </div>

    <!-- FILTER & SEARCH -->
    <div class="mb-6">
        <form method="GET" class="flex gap-2 mb-4">
            <input type="text" name="search" placeholder="Cari kode, universitas, atau nama..." 
                   class="border p-2 rounded flex-1 focus:ring-blue-500 focus:border-blue-500" value="{{ request('search') }}">
            
            <select name="status" class="border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                <option value="all">Semua Status</option>
                <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                <option value="diproses" @selected(request('status') === 'diproses')>Diproses</option>
                <option value="diterima" @selected(request('status') === 'diterima')>Diterima</option>
                <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
                Cari
            </button>
        </form>

        <!-- TOMBOL BUAT PENGAJUAN -->
        <a href="{{ route('apply.form') }}" class="px-4 py-2 bg-green-600 text-white rounded inline-block hover:bg-green-700 font-semibold">
            + Buat Pengajuan Baru
        </a>
    </div>

    <!-- TABEL PENGAJUAN -->
    <div class="mt-6 overflow-x-auto">
        <table class="w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2">Kode Pengajuan</th>
                    <th class="border p-2">Nama Leader</th>
                    <th class="border p-2">Universitas</th>
                    <th class="border p-2">Program Studi</th>
                    <th class="border p-2">Departemen</th>
                    <th class="border p-2">Periode</th>
                    <th class="border p-2">Status</th>
                    <th class="border p-2">Tgl Daftar</th>
                    <th class="border p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                    <tr>
                        <td class="border p-2 font-mono text-sm">{{ $app->registration_code }}</td>
                        <td class="border p-2">{{ $app->leader_name }}</td>
                        <td class="border p-2">{{ $app->university }}</td>
                        <td class="border p-2">{{ $app->major }}</td>
                        <td class="border p-2">{{ $app->department?->name ?? '-' }}</td>
                        <td class="border p-2 text-sm">
                            {{ $app->period_start->format('d/m/Y') }} - {{ $app->period_end->format('d/m/Y') }}
                        </td>
                        <td class="border p-2">
                            <span class="px-2 py-1 text-sm rounded text-white font-semibold
                                @if($app->status === 'menunggu') bg-yellow-500
                                @elseif($app->status === 'diproses') bg-purple-500
                                @elseif($app->status === 'diterima') bg-green-500
                                @elseif($app->status === 'ditolak') bg-red-500
                                @endif
                            ">
                                {{ ucfirst($app->status) }}
                            </span>
                        </td>
                        <td class="border p-2 text-sm">{{ $app->created_at->format('d/m/Y H:i') }}</td>
                        <td class="border p-2">
                            <a href="{{ route('mahasiswa.applications.show', $app->id) }}" 
                               class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="border p-4 text-center text-gray-500">
                            Tidak ada data pengajuan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $applications->links() }}
    </div>
</div>
</x-app-layout>
