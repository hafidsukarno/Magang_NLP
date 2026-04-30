@section('title', 'Riwayat Pengajuan Magang')
<x-app-layout>
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- BREADCRUMB -->
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
        </a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-800 font-semibold">Riwayat Pengajuan</span>
    </div>

    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Riwayat Pengajuan</h1>
            <p class="text-gray-500 mt-1">Kelola dan pantau status pengajuan magang Anda.</p>
        </div>
        
        <div class="relative">
            <a href="{{ route('apply.upload-surat', ['type' => 'individual']) }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-2xl font-bold shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all hover:-translate-y-1 active:translate-y-0">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                Buat Pengajuan Baru
            </a>
        </div>
    </div>

    <!-- FILTER & SEARCH -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('mahasiswa.applications.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <i data-lucide="search" class="absolute left-3 top-2.5 w-5 h-5 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Cari kode, universitas, atau nama..." 
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
            </div>
            
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="diterima" {{ request('status') == 'diterima' ? 'selected' : '' }}>Diterima</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            
            <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-xl font-bold hover:bg-gray-900 transition shadow-md">
                Filter
            </button>
        </form>
    </div>

    <!-- TABLE AREA -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Informasi Pengajuan</th>
                        <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Institusi</th>
                        <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Periode</th>
                        <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($applications as $app)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs shadow-sm">
                                        {{ $app->type === 'group' ? '👥' : '👤' }}
                                    </div>
                                    <div>
                                        <p class="font-mono text-[13px] font-bold text-blue-600 tracking-tighter">{{ $app->registration_code }}</p>
                                        <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $app->leader_name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">{{ $app->type }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <p class="text-sm font-bold text-gray-700">{{ $app->university }}</p>
                                <p class="text-[11px] text-gray-500 mt-1 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i> {{ $app->department?->name ?? 'Belum Ditentukan' }}
                                </p>
                            </td>
                            <td class="px-6 py-5">
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-600 flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        {{ $app->period_start->format('d/m/y') }} - {{ $app->period_end->format('d/m/y') }}
                                    </p>
                                    @php
                                        $diff = $app->period_end->diff($app->period_start);
                                        $totalMonths = ($diff->y * 12) + $diff->m;
                                    @endphp
                                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">{{ $totalMonths }} Bulan {{ $diff->d }} Hari</p>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col items-center gap-1">
                                    @if($app->type === 'individual')
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider
                                            @if($app->leader_status === 'menunggu') bg-yellow-100 text-yellow-700
                                            @elseif($app->leader_status === 'diterima') bg-green-100 text-green-700
                                            @elseif($app->leader_status === 'ditolak') bg-red-100 text-red-700
                                            @endif">
                                            {{ $app->leader_status ?? 'Menunggu' }}
                                        </span>
                                    @else
                                        <!-- Group Status Summary -->
                                        <div class="flex flex-col items-center gap-1">
                                            <div class="flex items-center gap-1">
                                                <span class="text-[9px] font-bold text-gray-400 uppercase">Ketua:</span>
                                                <span class="w-2 h-2 rounded-full 
                                                    @if($app->leader_status === 'menunggu') bg-yellow-400
                                                    @elseif($app->leader_status === 'diterima') bg-green-400
                                                    @elseif($app->leader_status === 'ditolak') bg-red-400
                                                    @endif"></span>
                                            </div>
                                            @php
                                                $accepted = $app->members->where('status', 'diterima')->count();
                                                $total = $app->members->count();
                                            @endphp
                                            <span class="px-2 py-0.5 bg-gray-100 rounded text-[9px] font-bold text-gray-600">
                                                Anggota: {{ $accepted }}/{{ $total }} OK
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <a href="{{ route('mahasiswa.applications.show', $app->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-blue-600 rounded-xl text-xs font-bold hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm">
                                    Detail
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mb-4">
                                        <i data-lucide="folder-open" class="w-8 h-8"></i>
                                    </div>
                                    <p class="text-gray-500 font-semibold">Belum ada riwayat pengajuan.</p>
                                    <p class="text-sm text-gray-400 mt-1">Silakan buat pengajuan magang baru untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
            {{ $applications->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
</x-app-layout>
