@section('title', 'Detail Pengajuan')
@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: `{!! addslashes($errors->first()) !!}`,
                confirmButtonColor: '#ef4444'
            });
        });
    </script>
@endif
<x-app-layout>
    <div class="container mx-auto px-6 py-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Detail Pengajuan</h1>
                <p class="text-gray-500 mt-1">Informasi lengkap tentang pengajuan ini</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hrd.dashboard') }}"
                    class="px-4 py-2 bg-white text-gray-700 rounded-xl border border-gray-200 hover:bg-gray-50 transition flex items-center gap-2 shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
            </div>
        </div>


        <!-- Unified Application Overview Card -->
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden mb-8">
            <div class="bg-gray-50/50 border-b border-gray-100 px-8 py-5 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 text-blue-600"></i>
                    Informasi & Ekstraksi Pengajuan
                </h2>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase tracking-wider border border-blue-200">AI OCR Active</span>
                </div>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                    
                    <!-- Left Column: Informasi Umum -->
                    <div class="lg:col-span-4 space-y-8">
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i data-lucide="info" class="w-3.5 h-3.5"></i> Informasi Utama
                            </h3>
                            <div class="space-y-5">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Departemen & Status</p>
                                    <p class="text-sm font-bold text-gray-800">{{ $app->department->name ?? '-' }}</p>
                                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                            @if ($app->status == 'diterima') bg-green-100 text-green-700
                                            @elseif($app->status == 'ditolak') bg-red-100 text-red-700
                                            @elseif($app->status == 'selesai') bg-blue-100 text-blue-700
                                            @else bg-yellow-100 text-yellow-700 @endif">
                                            {{ $app->status }}
                                        </span>
                                        
                                        @if($app->hrd_note)
                                            <div class="group relative flex items-center">
                                                <i data-lucide="info" class="w-3.5 h-3.5 text-blue-500 cursor-help"></i>
                                                <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block w-48 p-2 bg-gray-800 text-white text-[10px] rounded-lg shadow-xl z-50 leading-relaxed">
                                                    {{ $app->hrd_note }}
                                                    <div class="absolute top-full left-2 -mt-1 border-4 border-transparent border-t-gray-800"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($app->hrd_note)
                                        <div class="mt-3 p-3 {{ $app->status == 'ditolak' ? 'bg-red-50 border-red-100' : 'bg-blue-50/50 border-blue-100' }} border rounded-xl">
                                            <p class="text-[9px] {{ $app->status == 'ditolak' ? 'text-red-500' : 'text-blue-500' }} font-bold uppercase tracking-wider mb-1 flex items-center gap-1">
                                                <i data-lucide="{{ $app->status == 'ditolak' ? 'alert-circle' : 'message-square' }}" class="w-3 h-3"></i>
                                                {{ $app->status == 'ditolak' ? 'Alasan Penolakan' : 'Catatan HRD / Pemindahan' }}
                                            </p>
                                            <p class="text-xs {{ $app->status == 'ditolak' ? 'text-red-700' : 'text-blue-700' }} italic leading-relaxed">"{{ $app->hrd_note }}"</p>
                                        </div>
                                    @endif
                                </div>
                                
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Periode Magang</p>
                                    <p class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                        @if ($app->period_start && $app->period_end)
                                            {{ \Carbon\Carbon::parse($app->period_start)->format('d M Y') }} —
                                            {{ \Carbon\Carbon::parse($app->period_end)->format('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                    @if ($app->period_start && $app->period_end)
                                        @php
                                            $startDate = \Carbon\Carbon::parse($app->period_start);
                                            $endDate = \Carbon\Carbon::parse($app->period_end);
                                            $months = $endDate->diff($startDate)->m;
                                            $years = $endDate->diff($startDate)->y;
                                            $days = $endDate->diff($startDate)->d;
                                            $totalMonths = ($years * 12) + $months;
                                        @endphp
                                        <p class="text-xs text-blue-600 font-bold mt-1.5 bg-blue-50 px-2 py-0.5 rounded-md inline-block">
                                            {{ $totalMonths }} Bulan {{ $days }} Hari
                                        </p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Right Column: OCR Results & Documents -->
                    <div class="lg:col-span-8 space-y-8">
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i data-lucide="scan-text" class="w-3.5 h-3.5"></i> Hasil Ekstraksi Otomatis
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:bg-white transition group">
                                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1 group-hover:text-blue-500 transition">Universitas</p>
                                    <p class="text-sm font-bold text-gray-800">{{ $app->university ?? '-' }}</p>
                                </div>
                                <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:bg-white transition group">
                                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1 group-hover:text-blue-500 transition">Jurusan</p>
                                    <p class="text-sm font-bold text-gray-800">{{ $app->major ?? '-' }}</p>
                                </div>
                                <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:bg-white transition group">
                                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1 group-hover:text-blue-500 transition">Program Studi</p>
                                    <p class="text-sm font-bold text-gray-800">{{ $app->program_studi ?? '-' }}</p>
                                </div>
                                <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:bg-white transition group">
                                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1 group-hover:text-blue-500 transition">Keahlian / Skill</p>
                                    <p class="text-sm font-bold text-blue-700">{{ $app->keahlian && $app->keahlian !== '-' ? $app->keahlian : '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-gray-600 flex items-center gap-2">
                                <i data-lucide="link" class="w-3.5 h-3.5"></i> Dokumen Pendukung
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @if($app->surat_permohonan_path)
                                    <a href="{{ route('hrd.application.viewFile', ['id' => $app->id, 'type' => 'permohonan']) }}" target="_blank" 
                                        class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-2xl hover:border-blue-400 transition-all hover:shadow-md hover:-translate-y-0.5 group">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 bg-red-50 rounded-xl group-hover:bg-red-500 transition-colors">
                                                <i data-lucide="file-text" class="w-5 h-5 text-red-500 group-hover:text-white transition-colors"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-800">Surat Pengajuan</p>
                                                <p class="text-[10px] text-gray-400 italic truncate max-w-[120px]">
                                                    {{ $app->surat_permohonan_nama ?? 'Dokumen Pengajuan' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="p-2 bg-gray-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white rounded-xl transition shadow-sm">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </div>
                                    </a>
                                @else
                                    <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl opacity-60">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 bg-gray-200 rounded-xl">
                                                <i data-lucide="file-text" class="w-5 h-5 text-gray-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-500">Surat Pengajuan</p>
                                                <p class="text-[10px] text-gray-400 italic">File tidak tersedia</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($app->file_path)
                                    <a href="{{ route('hrd.application.viewFile', ['id' => $app->id, 'type' => 'main']) }}" target="_blank" 
                                        class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-2xl hover:border-blue-400 transition-all hover:shadow-md hover:-translate-y-0.5 group">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 bg-blue-50 rounded-xl group-hover:bg-blue-500 transition-colors">
                                                <i data-lucide="file-check" class="w-5 h-5 text-blue-500 group-hover:text-white transition-colors"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-800">Surat Pelaporan</p>
                                                <p class="text-[10px] text-gray-400 italic truncate max-w-[120px]">
                                                    {{ $app->surat_laporan_title ?? 'Dokumen Pelaporan' }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="p-2 bg-gray-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white rounded-xl transition shadow-sm">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </div>
                                    </a>
                                @else
                                    <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl opacity-60">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 bg-gray-200 rounded-xl">
                                                <i data-lucide="file-check" class="w-4 h-4 text-gray-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-500">Surat Pelaporan</p>
                                                <p class="text-[10px] text-gray-400 italic">File tidak tersedia</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
            </div>
        </div>

        <!-- OCR Raw Text Section -->
        @if($app->surat_permohonan_extracted_text)
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div class="bg-gray-50/50 border-b border-gray-100 px-8 py-4 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <i data-lucide="file-search" class="w-4 h-4 text-blue-600"></i>
                    Isi Dokumen Terbaca (OCR Raw Text)
                </h3>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider border border-blue-100">
                        {{ strlen($app->surat_permohonan_extracted_text) }} Karakter Terbaca
                    </span>
                </div>
            </div>
            <div class="p-8">
                <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-200 max-h-80 overflow-y-auto shadow-inner relative group">
                    <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                        <i data-lucide="scroll-text" class="w-5 h-5 text-gray-300"></i>
                    </div>
                    <p class="text-xs text-gray-600 font-serif leading-relaxed whitespace-pre-wrap italic selection:bg-blue-100 selection:text-blue-900">
                        {{ $app->surat_permohonan_extracted_text }}
                    </p>
                </div>
                <p class="text-[10px] text-gray-400 mt-3 italic flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3 h-3 text-green-500"></i>
                    Data di atas adalah representasi teks langsung dari dokumen PDF yang diunggah.
                </p>
            </div>
        </div>
        @endif

        <!-- Clean & Simple AI Recommendation -->
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 mb-10">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                <div class="flex items-center gap-6">
                    <!-- Progress Circle -->
                    <div class="relative flex-shrink-0 w-20 h-20">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-gray-100" stroke-width="3"></circle>
                            <circle cx="18" cy="18" r="16" fill="none" class="stroke-current {{ $score >= 80 ? 'text-green-500' : ($score >= 50 ? 'text-yellow-500' : 'text-red-500') }}" 
                                    stroke-width="3" stroke-dasharray="{{ $score }}, 100" stroke-linecap="round"></circle>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-black text-gray-800">{{ $score }}%</span>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <h2 class="text-lg font-bold text-gray-800 tracking-tight">Analisis Rekomendasi AI</h2>
                            <span class="px-2.5 py-1 {{ $score >= 80 ? 'bg-green-100 text-green-700 border-green-200' : ($score >= 50 ? 'bg-yellow-100 text-yellow-700 border-yellow-200' : 'bg-red-100 text-red-700 border-red-200') }} text-[10px] font-bold rounded-lg uppercase border">
                                {{ $score >= 80 ? 'Sangat Cocok' : ($score >= 50 ? 'Dipertimbangkan' : 'Kurang Cocok') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 leading-relaxed">
                            Mahasiswa dari latar belakang <span class="font-bold text-gray-700 italic">{{ $app->major }}</span> dinilai memiliki korelasi kompetensi yang {{ $score >= 80 ? 'sangat baik' : ($score >= 50 ? 'cukup' : 'rendah') }} dengan departemen tujuan.
                        </p>
                    </div>
                </div>

                <!-- Comparison Box -->
                <div class="flex items-center gap-8 bg-gray-50/80 px-8 py-5 rounded-2xl border border-gray-100 shadow-inner">
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1.5">Input Mahasiswa</p>
                        <p class="text-xs font-bold text-gray-800">{{ $app->major ?: '-' }}</p>
                    </div>
                    <div class="w-px h-10 bg-gray-200"></div>
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1.5">Syarat Utama</p>
                        <div class="flex gap-1 justify-center items-center">
                            @foreach($app->department->majors->take(1) as $m)
                                <span class="text-xs font-bold text-blue-600 uppercase">{{ $m->name }}</span>
                            @endforeach
                            @if($app->department->majors->count() > 1)
                                <span class="text-[9px] text-gray-400 font-bold ml-1">+{{ $app->department->majors->count() - 1 }} Syarat Lain</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Cross-Department Recommendations -->
        @if($app->status == 'menunggu' || $app->status == 'diproses')
        <div class="mb-10">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="shuffle" class="w-4 h-4 text-indigo-600"></i>
                Rekomendasi Departemen Lain
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @php $otherDeptsCount = 0; @endphp
                @foreach($deptRecommendations as $rec)
                        @php $otherDeptsCount++; @endphp
                        <div class="bg-white p-4 rounded-xl border {{ $rec['is_current'] ? 'border-indigo-500 ring-2 ring-indigo-100' : 'border-gray-100 shadow-sm' }} flex items-center justify-between hover:border-indigo-200 transition-all group relative">
                            @if($rec['is_current'])
                                <div class="absolute -top-2 -right-2 px-2 py-0.5 bg-indigo-600 text-white text-[8px] font-bold rounded-full uppercase tracking-tighter shadow-sm z-10">Pilihan Saat Ini</div>
                            @endif
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-[11px] font-black {{ $rec['score'] >= 80 ? 'text-green-600 bg-green-50' : ($rec['score'] >= 50 ? 'text-yellow-600 bg-yellow-50' : 'text-gray-400 bg-gray-50') }} group-hover:scale-110 transition-transform shadow-sm">
                                    {{ $rec['score'] }}%
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold text-gray-800 truncate max-w-[150px]">{{ $rec['name'] }}</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @if($rec['can_fit'])
                                            <span class="text-[8px] px-1 bg-green-50 text-green-600 rounded border border-green-100 font-bold uppercase">Sisa {{ $rec['available_slots'] }} Slot</span>
                                        @else
                                            <span class="text-[8px] px-1 bg-red-50 text-red-600 rounded border border-red-100 font-bold uppercase italic">Slot Penuh</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button type="button" 
                                onclick="quickChangeDept({{ $rec['id'] }}, '{{ $rec['name'] }}')" 
                                class="p-1.5 {{ $rec['can_fit'] || $rec['is_current'] ? 'bg-gray-50 text-gray-400 hover:bg-indigo-600 hover:text-white' : 'bg-gray-50 text-gray-300 cursor-not-allowed opacity-50' }} rounded-lg transition shadow-sm" 
                                title="{{ $rec['can_fit'] || $rec['is_current'] ? 'Pindahkan ke departemen ini' : 'Kuota Penuh' }}"
                                {{ !$rec['can_fit'] && !$rec['is_current'] ? 'disabled' : '' }}>
                                <i data-lucide="{{ $rec['is_current'] ? 'check-circle' : 'arrow-right-left' }}" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                @endforeach

                @if($otherDeptsCount === 0)
                    <div class="col-span-full p-6 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-center">
                        <p class="text-xs text-gray-400 font-medium italic">Tidak ada departemen lain yang tersedia.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <script>
            function quickChangeDept(deptId, deptName) {
                Swal.fire({
                    title: 'Memproses...',
                    text: `Sedang memindahkan ke ${deptName}`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Update the form values and submit directly
                const form = document.getElementById('hrdActionForm');
                const deptSelect = form.querySelector('select[name="department_id"]');
                const statusInput = document.getElementById('modalStatusInput');
                const noteInput = document.getElementById('modal_hrd_note');

                if (deptSelect && statusInput && noteInput) {
                    deptSelect.value = deptId;
                    statusInput.value = 'menunggu'; // Kembalikan ke menunggu jika pindah departemen
                    noteInput.value = `Otomatis pindah ke ${deptName} via Quick Recommendation.`;
                    form.submit();
                }
            }
        </script>

            <!-- Members & Leader Table -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Member(s)</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nama & Identitas</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Kontak</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-[11px] font-bold text-gray-400 uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if ($app->type === 'individual')
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-bold rounded-md uppercase">Peserta</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-800">{{ $app->leader_name }}</p>
                                        <p class="text-[11px] text-gray-500">{{ $app->leader_nim ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs text-gray-600">{{ $app->leader_email ?? '-' }}</p>
                                        <p class="text-xs text-gray-500">{{ $app->leader_phone ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase
                                            @if ($app->leader_status == 'menunggu') bg-yellow-100 text-yellow-700
                                            @elseif($app->leader_status == 'diterima') bg-green-100 text-green-700
                                            @elseif($app->leader_status == 'ditolak') bg-red-100 text-red-700
                                            @else bg-gray-100 text-gray-700 @endif">
                                            {{ $app->leader_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($app->leader_status == 'menunggu')
                                            <div class="flex gap-2 justify-center">
                                                <button type="button" onclick="approveLead()" class="p-1.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-600 hover:text-white transition shadow-sm border border-green-100" title="Terima">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                                <button type="button" onclick="openActionModal('diproses')" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-100" title="Pindahkan Departemen">
                                                    <i data-lucide="shuffle" class="w-4 h-4"></i>
                                                </button>
                                                <button type="button" onclick="rejectLead()" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm border border-red-100" title="Tolak">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        @else
                                            <div class="flex items-center justify-center gap-1 text-gray-400 opacity-50">
                                                <i data-lucide="lock" class="w-3 h-3"></i>
                                                <span class="text-[10px] font-bold uppercase tracking-tighter">Keputusan Final</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                @if ($app->leader_name)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-md uppercase">Ketua</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-gray-800">{{ $app->leader_name }}</p>
                                            <p class="text-[11px] text-gray-500">{{ $app->leader_nim ?? '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-xs text-gray-600">{{ $app->leader_email ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $app->leader_phone ?? '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase
                                                @if ($app->leader_status == 'menunggu') bg-yellow-100 text-yellow-700
                                                @elseif($app->leader_status == 'diterima') bg-green-100 text-green-700
                                                @elseif($app->leader_status == 'ditolak') bg-red-100 text-red-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                {{ $app->leader_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($app->leader_status == 'menunggu')
                                                <div class="flex gap-2 justify-center">
                                                    <button type="button" onclick="approveLead()" class="p-1.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-600 hover:text-white transition shadow-sm border border-green-100" title="Terima">
                                                        <i data-lucide="check" class="w-4 h-4"></i>
                                                    </button>
                                                    <button type="button" onclick="openActionModal('diproses')" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-100" title="Pindahkan Departemen">
                                                        <i data-lucide="shuffle" class="w-4 h-4"></i>
                                                    </button>
                                                    <button type="button" onclick="rejectLead()" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm border border-red-100" title="Tolak">
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-center gap-1 text-gray-400 opacity-50">
                                                    <i data-lucide="lock" class="w-3 h-3"></i>
                                                    <span class="text-[10px] font-bold uppercase tracking-tighter">Keputusan Final</span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endif

                                @foreach ($app->members as $member)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-gray-50 text-gray-600 text-[10px] font-bold rounded-md uppercase">Anggota</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-gray-800">{{ $member->name }}</p>
                                            <p class="text-[11px] text-gray-500">{{ $member->nim ?? '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-xs text-gray-600">{{ $member->email ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $member->phone ?? '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase
                                                @if ($member->status == 'menunggu') bg-yellow-100 text-yellow-700
                                                @elseif($member->status == 'diterima') bg-green-100 text-green-700
                                                @elseif($member->status == 'ditolak') bg-red-100 text-red-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                {{ $member->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($member->status == 'menunggu')
                                                <div class="flex gap-2 justify-center">
                                                    <button type="button" onclick="approveMember({{ $member->id }})" class="p-1.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-600 hover:text-white transition shadow-sm border border-green-100" title="Terima">
                                                        <i data-lucide="check" class="w-4 h-4"></i>
                                                    </button>
                                                    <button type="button" onclick="openActionModal('diproses')" class="p-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-100" title="Pindahkan Departemen">
                                                        <i data-lucide="shuffle" class="w-4 h-4"></i>
                                                    </button>
                                                    <button type="button" onclick="rejectMember({{ $member->id }})" class="p-1.5 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition shadow-sm border border-red-100" title="Tolak">
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-center gap-1 text-gray-400 opacity-50">
                                                    <i data-lucide="lock" class="w-3 h-3"></i>
                                                    <span class="text-[10px] font-bold uppercase tracking-tighter">Keputusan Final</span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>




        <!-- MAIN ACTION MODAL -->
        <div id="mainActionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeActionModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <form id="hrdActionForm" action="{{ route('hrd.application.update', $app->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" id="modalStatusInput">
                        <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div id="modalIconContainer" class="p-3 rounded-xl bg-blue-50">
                                    <i data-lucide="check-circle" id="modalIcon" class="w-6 h-6 text-blue-600"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Konfirmasi Aksi</h3>
                            </div>
                            <div class="space-y-4">
                                <div id="deptSelectContainer">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Departemen Tujuan:</label>
                                    <select name="department_id" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-3 bg-gray-50 border" required>
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach ($deptRecommendations as $d)
                                            <option value="{{ $d['id'] }}" @selected($app->department_id == $d['id']) {{ !$d['can_fit'] && $app->department_id != $d['id'] ? 'disabled' : '' }}>
                                                {{ $d['name'] }} (Sisa: {{ $d['available_slots'] }} Slot) {{ !$d['can_fit'] && $app->department_id != $d['id'] ? '[PENUH]' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Peninjauan:</label>
                                    <textarea name="hrd_note" id="modal_hrd_note" rows="4" class="block w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm p-3 bg-gray-50 border" placeholder="Berikan alasan atau catatan tambahan..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" id="modalConfirmBtn" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-6 py-2.5 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm transition">
                                Simpan Perubahan
                            </button>
                            <button type="button" onclick="closeActionModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();
        });

        function openActionModal(status) {
            const modal = document.getElementById('mainActionModal');
            const statusInput = document.getElementById('modalStatusInput');
            const titleEl = document.getElementById('modalTitle');
            const iconContainer = document.getElementById('modalIconContainer');
            const iconEl = document.getElementById('modalIcon');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const noteEl = document.getElementById('modal_hrd_note');

            statusInput.value = status;
            modal.classList.remove('hidden');

            if (status === 'diterima') {
                titleEl.innerText = 'Terima Pengajuan Magang';
                iconContainer.className = 'p-3 rounded-xl bg-green-50';
                iconEl.setAttribute('data-lucide', 'check-circle');
                iconEl.className = 'w-6 h-6 text-green-600';
                confirmBtn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-6 py-2.5 bg-green-600 text-base font-bold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto sm:text-sm transition';
                noteEl.placeholder = 'Berikan catatan (opsional)...';
            } else if (status === 'diproses') {
                titleEl.innerText = 'Pindahkan Departemen';
                iconContainer.className = 'p-3 rounded-xl bg-blue-50';
                iconEl.setAttribute('data-lucide', 'shuffle');
                iconEl.className = 'w-6 h-6 text-blue-600';
                confirmBtn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-6 py-2.5 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm transition';
                noteEl.placeholder = 'Alasan pemindahan departemen...';
            } else {
                titleEl.innerText = 'Tolak Pengajuan Magang';
                iconContainer.className = 'p-3 rounded-xl bg-red-50';
                iconEl.setAttribute('data-lucide', 'x-circle');
                iconEl.className = 'w-6 h-6 text-red-600';
                confirmBtn.className = 'w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-6 py-2.5 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm transition';
                noteEl.placeholder = 'Berikan alasan penolakan (wajib)...';
                // Clear note if it was an automatic move note
                if (noteEl.value.includes('Otomatis pindah') || noteEl.value.includes('dipindahkan ke departemen')) {
                    noteEl.value = '';
                }
            }
            
            // Re-render icons inside modal
            lucide.createIcons();
        }

        function closeActionModal() {
            document.getElementById('mainActionModal').classList.add('hidden');
        }

        // Form validation on submit
        document.getElementById('hrdActionForm').addEventListener('submit', function(e) {
            const status = document.getElementById('modalStatusInput').value;
            const note = document.getElementById('modal_hrd_note').value.trim();
            
            if (status === 'ditolak' && !note) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Catatan dibutuhkan',
                    text: 'Silakan isi keterangan penolakan sebelum mengirim.',
                    confirmButtonColor: '#ef4444'
                });
            }
        });

        // Leader approval functions
        function approveLead() {
            Swal.fire({
                icon: 'question',
                title: 'Terima Leader',
                text: 'Apakah Anda yakin ingin menerima leader ini?',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/hrd/leader') }}/{{ $app->id }}/update`;
                    form.innerHTML = `{{ csrf_field() }}<input type="hidden" name="status" value="diterima">`;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function rejectLead() {
            Swal.fire({
                icon: 'warning',
                title: 'Tolak Leader',
                html: `<textarea id="leadNote" rows="3" class="w-full p-2 border border-gray-300 rounded" placeholder="Masukkan alasan penolakan..."></textarea>`,
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                didOpen: () => document.getElementById('leadNote').focus()
            }).then((result) => {
                if (result.isConfirmed) {
                    const note = document.getElementById('leadNote').value.trim();
                    if (!note) {
                        Swal.fire({ icon: 'error', title: 'Catatan Diperlukan', text: 'Silakan masukkan alasan penolakan', confirmButtonColor: '#ef4444' });
                        return;
                    }
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/hrd/leader') }}/{{ $app->id }}/update`;
                    form.innerHTML = `{{ csrf_field() }}<input type="hidden" name="status" value="ditolak"><input type="hidden" name="hrd_note" value="${note}">`;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function approveMember(memberId) {
            Swal.fire({
                icon: 'question',
                title: 'Terima Member',
                text: 'Apakah Anda yakin ingin menerima member ini?',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/hrd/member') }}/${memberId}/update`;
                    form.innerHTML = `{{ csrf_field() }}<input type="hidden" name="status" value="diterima">`;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function rejectMember(memberId) {
            Swal.fire({
                icon: 'warning',
                title: 'Tolak Member',
                html: `<textarea id="memberNote" rows="3" class="w-full p-2 border border-gray-300 rounded" placeholder="Masukkan alasan penolakan..."></textarea>`,
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                didOpen: () => document.getElementById('memberNote').focus()
            }).then((result) => {
                if (result.isConfirmed) {
                    const note = document.getElementById('memberNote').value.trim();
                    if (!note) {
                        Swal.fire({ icon: 'error', title: 'Catatan Diperlukan', text: 'Silakan masukkan alasan penolakan', confirmButtonColor: '#ef4444' });
                        return;
                    }
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/hrd/member') }}/${memberId}/update`;
                    form.innerHTML = `{{ csrf_field() }}<input type="hidden" name="status" value="ditolak"><input type="hidden" name="hrd_note" value="${note}">`;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>


</x-app-layout>
