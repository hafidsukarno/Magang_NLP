@section('title', 'Detail Pengajuan - ' . $app->registration_code)
<x-app-layout>
    <div class="p-6 bg-gray-50 min-h-screen">
        <!-- BREADCRUMB -->
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
            </a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <a href="{{ route('mahasiswa.applications.index') }}" class="hover:text-blue-600 transition">Riwayat</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-gray-800 font-semibold font-mono">{{ $app->registration_code }}</span>
        </div>

        <div class="max-w-6xl mx-auto space-y-6">
            
            <!-- HEADER CARD -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 bg-gradient-to-br from-white to-gray-50">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-100">
                            <i data-lucide="file-text" class="w-8 h-8"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan Magang</h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-gray-500 text-sm font-mono tracking-wider">{{ $app->registration_code }}</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest {{ $app->type === 'group' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $app->type }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <a href="{{ route('mahasiswa.applications.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-50 transition shadow-sm">
                            Kembali
                        </a>
                        <div class="h-10 w-px bg-gray-200 mx-2 hidden md:block"></div>
                        
                        @php
                            $statusColors = [
                                'menunggu' => 'bg-yellow-500 shadow-yellow-100',
                                'diterima' => 'bg-green-500 shadow-green-100',
                                'ditolak'  => 'bg-red-500 shadow-red-100'
                            ];
                            $statusLabel = $app->leader_status ?? 'menunggu';
                        @endphp
                        
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] font-bold text-gray-400 uppercase mb-1">Status Utama</span>
                            <div class="px-5 py-2 {{ $statusColors[$statusLabel] ?? 'bg-gray-500' }} text-white rounded-xl font-bold text-sm shadow-lg capitalize">
                                {{ $statusLabel }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- LEFT COLUMN: PRIMARY INFO -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- DATA MAHASISWA -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center gap-3 mb-8 pb-4 border-b border-gray-50">
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <h2 class="text-lg font-bold text-gray-800 uppercase tracking-tight">Informasi Pemohon</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase">Nama Lengkap</p>
                                <p class="font-semibold text-gray-800 text-lg">{{ $app->leader_name }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase">NIM</p>
                                <p class="font-mono font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-md inline-block">{{ $app->leader_nim ?? '—' }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase">Universitas</p>
                                <p class="font-semibold text-gray-700">{{ $app->university }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase">Email & No. HP</p>
                                <p class="font-semibold text-gray-700">{{ $app->leader_email }}</p>
                                <p class="text-sm text-gray-500">{{ $app->leader_phone }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase">Jurusan</p>
                                <p class="font-semibold text-gray-700">{{ $app->major }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase">Program Studi</p>
                                <p class="font-semibold text-gray-700">{{ $app->program_studi ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- DATA ANGGOTA (IF GROUP) -->
                    @if($app->type === 'group' && $app->members->count() > 1)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-8 pb-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600">
                                    <i data-lucide="users" class="w-5 h-5"></i>
                                </div>
                                <h2 class="text-lg font-bold text-gray-800 uppercase">Anggota Kelompok ({{ $app->members->count() - 1 }})</h2>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 border-y border-gray-100">
                                    <tr>
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Identitas</th>
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Program Studi</th>
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($app->members as $member)
                                        @if($member->name !== $app->leader_name) <!-- Simple check to skip leader -->
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-8 py-5">
                                                <p class="font-bold text-gray-800">{{ $member->name }}</p>
                                                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $member->nim ?? 'NIM —' }}</p>
                                                <p class="text-xs text-blue-500 mt-1">{{ $member->email }}</p>
                                            </td>
                                            <td class="px-8 py-5">
                                                <p class="text-sm text-gray-600">{{ $member->major }}</p>
                                                <p class="text-[11px] text-gray-400 italic">{{ $member->program_studi ?? '' }}</p>
                                            </td>
                                            <td class="px-8 py-5">
                                                <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                                                    @if($member->status === 'menunggu') bg-yellow-100 text-yellow-700
                                                    @elseif($member->status === 'diterima') bg-green-100 text-green-700
                                                    @elseif($member->status === 'ditolak') bg-red-100 text-red-700
                                                    @endif">
                                                    {{ $member->status }}
                                                </span>
                                                @if($member->status === 'ditolak' && $member->hrd_note)
                                                    <p class="text-[10px] text-red-400 mt-2 italic font-medium">"{{ $member->hrd_note }}"</p>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- CATATAN DARI HRD -->
                    @if($app->hrd_note)
                    <div class="rounded-2xl border p-6 flex gap-4 {{ $app->status === 'ditolak' ? 'bg-red-50 border-red-100' : 'bg-blue-50 border-blue-100' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $app->status === 'ditolak' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                            <i data-lucide="{{ $app->status === 'ditolak' ? 'alert-circle' : 'message-square' }}" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg uppercase tracking-tight {{ $app->status === 'ditolak' ? 'text-red-800' : 'text-blue-800' }}">
                                {{ $app->status === 'ditolak' ? 'Keterangan Penolakan' : 'Catatan dari HRD' }}
                            </h3>
                            <p class="{{ $app->status === 'ditolak' ? 'text-red-700/80' : 'text-blue-700/80' }} mt-1 leading-relaxed italic">
                                "{{ $app->hrd_note }}"
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- RIGHT COLUMN: SIDEBAR -->
                <div class="space-y-6">
                    
                    <!-- DETAIL MAGANG -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-2 mb-6">
                            <i data-lucide="briefcase" class="w-5 h-5 text-blue-600"></i>
                            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Detail Penempatan</h3>
                        </div>
                        
                        <div class="space-y-5">
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Departemen</p>
                                <p class="font-bold text-gray-700">{{ $app->department?->name ?? 'Belum Dipilih' }}</p>
                            </div>
                            
                            <div class="p-4 bg-gray-50 rounded-xl">
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Periode Magang</p>
                                <p class="font-bold text-gray-700 text-sm">
                                    {{ $app->period_start->format('d M Y') }} - {{ $app->period_end->format('d M Y') }}
                                </p>
                                <p class="text-blue-600 font-black text-xs mt-2 uppercase tracking-widest">
                                    @php
                                        $diff = $app->period_end->diff($app->period_start);
                                        $m = $diff->m + ($diff->y * 12);
                                        $d = $diff->d;
                                    @endphp
                                    {{ $m }} BULAN {{ $d }} HARI
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- DOKUMEN -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-2 mb-6">
                            <i data-lucide="file-check" class="w-5 h-5 text-blue-600"></i>
                            <h3 class="font-bold text-gray-800 text-sm uppercase tracking-wider">Lampiran Dokumen</h3>
                        </div>
                        
                        <div class="space-y-3">
                            @if($app->file_path)
                            <a href="{{ Storage::disk('public')->url($app->file_path) }}" target="_blank"
                                class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:border-blue-200 hover:bg-blue-50 transition-all group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-red-50 text-red-500 rounded-lg flex items-center justify-center group-hover:bg-red-100 transition-colors">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-700">Surat Laporan</p>
                                        <p class="text-[10px] text-gray-400">PDF Document</p>
                                    </div>
                                </div>
                                <i data-lucide="external-link" class="w-4 h-4 text-gray-300 group-hover:text-blue-500"></i>
                            </a>
                            @endif

                            @if($app->surat_permohonan_path)
                            <a href="{{ Storage::disk('public')->url($app->surat_permohonan_path) }}" target="_blank"
                                class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:border-blue-200 hover:bg-blue-50 transition-all group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                        <i data-lucide="file-up" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-700">Surat Permohonan</p>
                                        <p class="text-[10px] text-gray-400">PDF (OCR Result)</p>
                                    </div>
                                </div>
                                <i data-lucide="external-link" class="w-4 h-4 text-gray-300 group-hover:text-blue-500"></i>
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- TIMELINE & LOG -->
                    <div class="bg-gray-800 rounded-2xl shadow-sm p-6 text-white">
                        <div class="flex items-center gap-2 mb-6">
                            <i data-lucide="clock-rewind" class="w-5 h-5 text-blue-400"></i>
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider">Log Aktivitas</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="relative pl-6 border-l-2 border-gray-700 pb-2">
                                <div class="absolute -left-[9px] top-0 w-4 h-4 bg-blue-500 rounded-full border-4 border-gray-800"></div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Update Terakhir</p>
                                <p class="text-sm font-semibold">{{ $app->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="relative pl-6 border-l-2 border-gray-700">
                                <div class="absolute -left-[9px] top-0 w-4 h-4 bg-gray-600 rounded-full border-4 border-gray-800"></div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Dibuat Pada</p>
                                <p class="text-sm font-semibold">{{ $app->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
