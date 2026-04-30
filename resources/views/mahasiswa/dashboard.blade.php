@section('title', 'Dashboard Mahasiswa')
<x-app-layout>
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- BREADCRUMB -->
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <span class="font-semibold text-gray-800 flex items-center gap-1">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Mahasiswa
        </span>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-400">Dashboard</span>
    </div>

    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Pengajuan</h1>
            <p class="text-gray-500 mt-1">Selamat datang kembali, pantau progres magang Anda di sini.</p>
        </div>
        
        <!-- TAB NAVIGATION -->
        <div class="flex p-1 bg-gray-200/50 rounded-2xl w-fit">
            <a href="{{ route('mahasiswa.dashboard') }}" class="px-6 py-2.5 font-bold text-sm bg-white text-blue-600 rounded-xl shadow-sm">
                Dashboard
            </a>
            <a href="{{ route('mahasiswa.applications.index') }}" class="px-6 py-2.5 font-bold text-sm text-gray-500 hover:text-blue-600 transition">
                Riwayat
            </a>
        </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative">
                <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-1">Total Pengajuan</p>
                <p class="text-3xl font-black text-gray-800">{{ $summary['total'] }}</p>
                <div class="mt-4 flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-bold rounded uppercase tracking-tighter">{{ $summary['total_individual'] }} Individu</span>
                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[9px] font-bold rounded uppercase tracking-tighter">{{ $summary['total_group'] }} Kelompok</span>
                </div>
            </div>
        </div>
        
        <!-- Menunggu -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-yellow-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative">
                <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-1">Menunggu Review</p>
                <p class="text-3xl font-black text-yellow-600">{{ $summary['menunggu'] }}</p>
                <div class="mt-4 flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-yellow-50 text-yellow-600 text-[9px] font-bold rounded uppercase tracking-tighter">{{ $summary['menunggu_individual'] }} Individu</span>
                </div>
            </div>
        </div>

        <!-- Diterima -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative">
                <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-1">Diterima</p>
                <p class="text-3xl font-black text-green-600">{{ $summary['diterima'] }}</p>
                <div class="mt-4">
                    <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[9px] font-bold rounded uppercase tracking-tighter">Selamat! Cek detail</span>
                </div>
            </div>
        </div>

        <!-- Ditolak -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full opacity-50 group-hover:scale-110 transition-transform"></div>
            <div class="relative">
                <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-1">Ditolak</p>
                <p class="text-3xl font-black text-red-600">{{ $summary['ditolak'] }}</p>
                <div class="mt-4">
                    <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[9px] font-bold rounded uppercase tracking-tighter">Perbaiki & Ajukan Lagi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION SECTION -->
    <div class="mb-10 flex flex-col md:flex-row items-center gap-6 bg-gradient-to-r from-blue-600 to-indigo-700 p-8 rounded-3xl text-white shadow-xl shadow-blue-100">
        <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center flex-shrink-0 border border-white/30">
            <i data-lucide="sparkles" class="w-8 h-8"></i>
        </div>
        <div class="flex-1">
            <h3 class="text-xl font-bold">Siap Memulai Magang?</h3>
            <p class="text-blue-100 text-sm opacity-90 mt-1 font-medium">Unggah surat permohonan Anda dan biarkan AI kami membantu mengisi datanya secara otomatis.</p>
        </div>
        <a href="{{ route('apply.upload-surat', ['type' => 'individual']) }}" 
           class="px-8 py-4 bg-white text-blue-600 rounded-2xl font-black text-sm hover:bg-gray-50 transition-all hover:scale-105 active:scale-100 shadow-lg shadow-black/10">
            AJUKAN MAGANG BARU
        </a>
    </div>

    <!-- TABLE AREA: PENGUJUAN TERBARU -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">Pengajuan Terbaru</h2>
            <a href="{{ route('mahasiswa.applications.index') }}" class="text-xs font-bold text-blue-600 hover:underline tracking-tight">Lihat Semua Riwayat →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Informasi</th>
                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Institusi</th>
                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($applications as $app)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <p class="font-mono text-[12px] font-bold text-blue-600 tracking-tighter">{{ $app->registration_code }}</p>
                                <p class="text-sm font-semibold text-gray-800 mt-1">{{ $app->university }}</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">{{ $app->type }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-700">{{ $app->department?->name ?? '-' }}</p>
                                <p class="text-[10px] text-gray-400 mt-1 font-medium">{{ $app->created_at->format('d M Y') }}</p>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col items-center gap-1.5">
                                    @if ($app->type === 'individual')
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                            @if($app->leader_status === 'menunggu') bg-yellow-100 text-yellow-700
                                            @elseif($app->leader_status === 'diterima') bg-green-100 text-green-700
                                            @elseif($app->leader_status === 'ditolak') bg-red-100 text-red-700
                                            @endif">
                                            {{ $app->leader_status ?? 'menunggu' }}
                                        </span>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full 
                                                @if($app->leader_status === 'menunggu') bg-yellow-400
                                                @elseif($app->leader_status === 'diterima') bg-green-400
                                                @elseif($app->leader_status === 'ditolak') bg-red-400
                                                @endif"></span>
                                            @php
                                                $okCount = $app->members->where('status', 'diterima')->count();
                                                $totalCount = $app->members->count();
                                            @endphp
                                            <span class="text-[11px] font-bold text-gray-600">{{ $okCount }}/{{ $totalCount }} Anggota</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <a href="{{ route('mahasiswa.applications.show', $app->id) }}" 
                                   class="inline-flex items-center gap-1 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl text-xs font-bold hover:bg-blue-600 hover:text-white transition-all">
                                    Lihat
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-10 text-center text-gray-500 font-medium text-sm">
                                Belum ada pengajuan terbaru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
</x-app-layout>
