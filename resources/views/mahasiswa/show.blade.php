@section('title', 'Detail Pengajuan')
<x-app-layout>
<div class="p-6">
    <!-- BREADCRUMB -->
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('mahasiswa.dashboard') }}" class="text-blue-600 hover:underline">Dashboard</a>
        <span>/</span>
        <a href="{{ route('mahasiswa.applications.index') }}" class="text-blue-600 hover:underline">Riwayat</a>
        <span>/</span>
        <span class="font-semibold text-gray-800">{{ $app->registration_code }}</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Detail Pengajuan Magang</h1>
        <a href="{{ route('mahasiswa.applications.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 font-semibold">
            ← Kembali
        </a>
    </div>

    <div class="grid grid-cols-12 gap-4">
        <!-- MAIN CONTENT -->
        <div class="col-span-8">
            <!-- STATUS BADGE -->
            <div class="mb-4 p-4 rounded-lg" style="border-left: 4px solid;">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-gray-600 text-sm">Kode Pengajuan</p>
                        <p class="text-xl font-bold font-mono">{{ $app->registration_code }}</p>
                    </div>
                    @if($app->type === 'individual')
                        <span class="px-4 py-2 text-lg rounded text-white font-semibold
                            @if($app->leader_status === 'menunggu') bg-yellow-500
                            @elseif($app->leader_status === 'diterima') bg-green-500
                            @elseif($app->leader_status === 'ditolak') bg-red-500
                            @endif
                        ">
                            {{ ucfirst($app->leader_status ?? 'menunggu') }}
                        </span>
                    @else
                        <div class="text-sm space-y-1 text-center">
                            <div>
                                <span class="text-gray-600">Ketua:</span>
                                <span class="px-3 py-1 rounded text-white font-semibold
                                    @if($app->leader_status === 'menunggu') bg-yellow-500
                                    @elseif($app->leader_status === 'diterima') bg-green-500
                                    @elseif($app->leader_status === 'ditolak') bg-red-500
                                    @endif inline-block">
                                    {{ ucfirst($app->leader_status ?? 'menunggu') }}
                                </span>
                            </div>
                            <div class="text-gray-700">
                                Anggota: <span class="font-semibold">{{ $app->members->where('status', 'diterima')->count() }}/{{ $app->members->count() }} diterima</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- DATA PEMOHON -->
            <div class="bg-white p-4 rounded-lg border mb-4">
                <h2 class="text-lg font-bold mb-3">Data Pemohon</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 text-sm">Nama</p>
                        <p class="font-semibold">{{ $app->leader_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Email</p>
                        <p class="font-semibold">{{ $app->leader_email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">No. Hp</p>
                        <p class="font-semibold">{{ $app->leader_phone }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Universitas</p>
                        <p class="font-semibold">{{ $app->university }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Program Studi</p>
                        <p class="font-semibold">{{ $app->major }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Tipe Pengajuan</p>
                        <p class="font-semibold">{{ ucfirst($app->type) }}</p>
                    </div>
                </div>
            </div>

            <!-- DATA MAGANG -->
            <div class="bg-white p-4 rounded-lg border mb-4">
                <h2 class="text-lg font-bold mb-3">Detail Magang</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 text-sm">Departemen</p>
                        <p class="font-semibold">{{ $app->department?->name ?? 'Belum dipilih' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Durasi Maksimal</p>
                        <p class="font-semibold">{{ $app->duration }} Bulan</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-600 text-sm">Periode</p>
                        <p class="font-semibold">{{ $app->period_start->format('d-m-Y') }} hingga {{ $app->period_end->format('d-m-Y') }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-600 text-sm">Durasi Pengajuan</p>
                        <p class="font-semibold text-blue-600">
                            @php
                                $months = $app->period_end->diff($app->period_start)->m;
                                $years = $app->period_end->diff($app->period_start)->y;
                                $days = $app->period_end->diff($app->period_start)->d;
                                $totalMonths = ($years * 12) + $months;
                            @endphp
                            {{ $totalMonths }} Bulan {{ $days }} Hari
                        </p>
                    </div>
                </div>
            </div>

            <!-- ANGGOTA GRUP (Jika type = group) -->
            @if($app->type === 'group' && $app->members->count() > 1)
            <div class="bg-white p-4 rounded-lg border mb-4">
                <h2 class="text-lg font-bold mb-3">Anggota Grup ({{ $app->members->count() }})</h2>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border p-2">Nama</th>
                            <th class="border p-2">Email</th>
                            <th class="border p-2">Program Studi</th>
                            <th class="border p-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($app->members as $member)
                        <tr>
                            <td class="border p-2">{{ $member->name }}</td>
                            <td class="border p-2">{{ $member->email }}</td>
                            <td class="border p-2">{{ $member->major }}</td>
                            <td class="border p-2">
                                <span class="px-2 py-1 text-xs rounded text-white font-semibold
                                    @if($member->status === 'menunggu') bg-yellow-500
                                    @elseif($member->status === 'diterima') bg-green-500
                                    @elseif($member->status === 'ditolak') bg-red-500
                                    @endif
                                ">
                                    {{ ucfirst($member->status ?? 'menunggu') }}
                                </span>
                                @if($member->status === 'ditolak' && $member->hrd_note)
                                    <div class="text-xs text-gray-600 mt-1">{{ $member->hrd_note }}</div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- FILE DOKUMEN -->
            <div class="bg-white p-4 rounded-lg border mb-4">
                <h2 class="text-lg font-bold mb-3">Dokumen</h2>
                @if($app->file_path)
                    <a href="{{ route('apply.form') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        📄 Lihat/Download PDF
                    </a>
                @else
                    <p class="text-gray-500">File tidak tersedia</p>
                @endif
            </div>

            <!-- CATATAN HRD (Jika ada & ditolak) -->
            @if($app->leader_status === 'ditolak' && $app->leader_note)
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500 mb-4">
                <h2 class="text-lg font-bold mb-2 text-red-700">Alasan Penolakan (Ketua)</h2>
                <p class="text-gray-700">{{ $app->leader_note }}</p>
            </div>
            @endif

            @if($app->type === 'group' && $app->members->where('status', 'ditolak')->count() > 0)
            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-500">
                <h2 class="text-lg font-bold mb-2 text-red-700">Alasan Penolakan Anggota</h2>
                @foreach($app->members->where('status', 'ditolak') as $rejectedMember)
                    <div class="mb-3 pb-3 border-b border-red-200">
                        <p class="font-semibold text-red-700">{{ $rejectedMember->name }}</p>
                        <p class="text-gray-700">{{ $rejectedMember->hrd_note ?? 'Tidak ada keterangan' }}</p>
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- SIDEBAR INFO -->
        <div class="col-span-4">
            <!-- STATUS TIMELINE -->
            <div class="bg-white p-4 rounded-lg border mb-4">
                <h3 class="font-bold mb-3">Timeline</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex gap-2">
                        <span class="text-gray-600">Dibuat:</span>
                        <span class="font-semibold">{{ $app->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-600">Update terakhir:</span>
                        <span class="font-semibold">{{ $app->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- RPA SCORE (Jika ada) -->
            @if($app->leader_status !== 'menunggu')
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <h3 class="font-bold text-blue-700 mb-2">Status Pengajuan</h3>
                <p class="text-sm text-blue-700">Pengajuan Anda sudah direview oleh HRD. Silakan lihat detail status di atas.</p>
            </div>
            @elseif($app->rpaResult)
            <div class="bg-white p-4 rounded-lg border">
                <h3 class="font-bold mb-3">Hasil Scan Dokumen</h3>
                <p class="text-gray-600 text-sm">Score RPA:</p>
                <p class="text-2xl font-bold text-blue-600">{{ $app->rpaResult->score ?? 'N/A' }}</p>
                <p class="text-gray-600 text-sm mt-2">Status: <span class="font-semibold">Sudah Diproses</span></p>
            </div>
            @else
            <div class="bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-500">
                <h3 class="font-bold text-yellow-700 mb-2">Proses Scanning</h3>
                <p class="text-sm text-yellow-700">Dokumen sedang dalam proses scan. Silakan tunggu beberapa saat...</p>
            </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
