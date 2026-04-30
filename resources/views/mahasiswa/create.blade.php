@section('title', 'Buat Pengajuan Magang Baru')
<x-app-layout>
    <div class="p-6 bg-gray-50 min-h-screen">
        <!-- BREADCRUMB -->
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-blue-600 transition flex items-center gap-1">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
            </a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-gray-800 font-semibold">Form Pengajuan</span>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold flex items-center gap-3">
                                <i data-lucide="clipboard-check" class="w-8 h-8"></i>
                                Form Pengajuan Magang
                            </h3>
                            <p class="text-blue-100 mt-2 opacity-90">
                                {{ $type === 'group' ? 'Pendaftaran Kelompok Mahasiswa' : 'Pendaftaran Individu' }}
                            </p>
                        </div>
                        <div class="hidden sm:block">
                            <span class="px-4 py-2 bg-white/20 backdrop-blur-md rounded-lg text-sm font-semibold border border-white/30">
                                {{ $type === 'group' ? '👥 Kelompok' : '👤 Individu' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Main Form -->
                <form id="applyForm" action="{{ route('apply.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-10">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="keahlian" value="{{ old('keahlian', $ocrData['keahlian'] ?? '') }}">
                    <input type="hidden" name="application_id" value="{{ $applicationId }}">

                    <!-- SECTION 1: INFORMASI UMUM -->
                    <section>
                        <div class="flex items-center gap-2 mb-6 border-b border-gray-100 pb-2">
                            <i data-lucide="building-2" class="w-5 h-5 text-blue-600"></i>
                            <h4 class="font-bold text-gray-800 uppercase tracking-wider text-sm">Informasi Institusi & Tujuan</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Departemen -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Departemen Tujuan</label>
                                <div class="relative">
                                    <select name="department_id" id="department_id"
                                        class="appearance-none w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d['id'] }}" data-duration="{{ $d['duration'] }}" {{ old('department_id') == $d['id'] ? 'selected' : '' }}>{{ $d['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <i data-lucide="briefcase" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5"></i>
                                    <i data-lucide="chevron-down" class="absolute right-4 top-3.5 text-gray-400 w-5 h-5 pointer-events-none"></i>
                                </div>
                                @error('department_id') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Universitas -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Asal Universitas</label>
                                <div class="relative">
                                    <input type="text" name="university" id="university" value="{{ old('university', $ocrData['university'] ?? '') }}"
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none" placeholder="Nama Universitas">
                                    <i data-lucide="school" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5"></i>
                                </div>
                                @error('university') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Jurusan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jurusan</label>
                                <div class="relative">
                                    <input type="text" name="major" id="major" value="{{ old('major', $ocrData['jurusan'] ?? $ocrData['major'] ?? '') }}"
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none" placeholder="Contoh: Teknik Elektro">
                                    <i data-lucide="book" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5"></i>
                                </div>
                                @error('major') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Program Studi -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Program Studi</label>
                                <div class="relative">
                                    <input type="text" name="program_studi" id="program_studi" value="{{ old('program_studi', $ocrData['program_studi'] ?? '') }}"
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none" placeholder="Contoh: D3 Teknik Mesin">
                                    <i data-lucide="graduation-cap" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5"></i>
                                </div>
                                @error('program_studi') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 2: DATA KETUA / INDIVIDU -->
                    <section>
                        <div class="flex items-center gap-2 mb-6 border-b border-gray-100 pb-2">
                            <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                            <h4 class="font-bold text-gray-800 uppercase tracking-wider text-sm">
                                Data {{ $type === 'group' ? 'Ketua Kelompok' : 'Mahasiswa' }}
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Lengkap</label>
                                <div class="relative">
                                    <input type="text" name="leader_name" id="leader_name" value="{{ old('leader_name', $ocrData['nama'] ?? '') }}"
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none">
                                    <i data-lucide="user-circle" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5"></i>
                                </div>
                                @error('leader_name') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- NIM -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">NIM</label>
                                <div class="relative">
                                    <input type="text" name="leader_nim" id="leader_nim" value="{{ old('leader_nim', $ocrData['members'][0]['NIM'] ?? '') }}"
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none">
                                    <i data-lucide="hash" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5"></i>
                                </div>
                                @error('leader_nim') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email Aktif</label>
                                <div class="relative">
                                    <input type="email" name="leader_email" id="leader_email" value="{{ old('leader_email') }}"
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none" placeholder="nama@email.com">
                                    <i data-lucide="mail" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5"></i>
                                </div>
                                @error('leader_email') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Telepon -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">No. WhatsApp</label>
                                <div class="relative">
                                    <input type="text" name="leader_phone" id="leader_phone" value="{{ old('leader_phone') }}"
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none" placeholder="0812...">
                                    <i data-lucide="phone" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5"></i>
                                </div>
                                @error('leader_phone') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 3: PERIODE MAGANG -->
                    <section class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                        <div class="flex items-center gap-2 mb-6">
                            <i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i>
                            <h4 class="font-bold text-gray-800 uppercase tracking-wider text-sm">Periode Magang</h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                            <!-- Tgl Mulai -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tanggal Mulai</label>
                                <div class="relative">
                                    <input type="date" name="period_start" id="period_start" value="{{ old('period_start', $ocrData['tanggal_masuk'] ?? '') }}"
                                        class="w-full bg-white border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                                    <i data-lucide="calendar-days" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5 pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Tgl Selesai -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tanggal Selesai</label>
                                <div class="relative">
                                    <input type="date" name="period_end" id="period_end" value="{{ old('period_end', $ocrData['tanggal_keluar'] ?? '') }}"
                                        class="w-full bg-white border border-gray-200 rounded-xl p-3.5 pl-11 focus:ring-2 focus:ring-blue-500 transition-all outline-none">
                                    <i data-lucide="calendar-check" class="absolute left-4 top-3.5 text-gray-400 w-5 h-5 pointer-events-none"></i>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="md:col-span-2 space-y-2">
                                <div id="periodInfo" class="p-3 rounded-xl text-xs font-medium hidden"></div>
                                <div id="quotaInfo" class="p-3 rounded-xl text-xs font-medium hidden"></div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 4: SURAT-SURAT -->
                    <section>
                        <div class="flex items-center gap-2 mb-6 border-b border-gray-100 pb-2">
                            <i data-lucide="files" class="w-5 h-5 text-blue-600"></i>
                            <h4 class="font-bold text-gray-800 uppercase tracking-wider text-sm">Dokumen Pendukung</h4>
                        </div>

                        <div class="space-y-6">
                            <!-- OCR Status (Success from previous step) -->
                            <div class="flex items-center gap-4 p-4 bg-green-50 border border-green-100 rounded-2xl">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 flex-shrink-0">
                                    <i data-lucide="check-check" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-green-800">Surat Permohonan Terverifikasi</p>
                                    <p class="text-xs text-green-600 opacity-80">Dokumen telah di-scan dan divalidasi pada tahap sebelumnya.</p>
                                </div>
                                <input type="hidden" name="surat_permohonan_path" value="{{ old('surat_permohonan_path', $ocrData['surat_permohonan_path'] ?? '') }}">
                            </div>

                            <!-- Upload Surat Laporan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Upload Surat Laporan (PDF)</label>
                                <div id="suratLaporanDropzone"
                                    class="border-2 border-dashed border-gray-200 rounded-2xl p-8 flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all relative group bg-gray-50/50">
                                    
                                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                        <i data-lucide="upload-cloud" class="w-6 h-6 text-blue-500"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-700">Tarik file ke sini atau klik untuk pilih</p>
                                    <p class="text-[11px] text-gray-400 mt-1 uppercase tracking-widest font-bold">PDF Max 5MB</p>

                                    <input type="file" id="suratLaporanFile" name="file" accept="application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                    <span id="suratLaporanFileName" class="mt-3 text-sm font-bold text-blue-600"></span>
                                </div>
                                <input type="hidden" name="surat_laporan_path" id="suratLaporanPath">
                                <div id="suratLaporanStatus" class="mt-3 hidden"></div>
                                @error('file') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 5: ANGGOTA KELOMPOK -->
                    @if($type === 'group')
                    <section class="border-t border-gray-100 pt-10">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-2">
                                <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                                <h4 class="font-bold text-gray-800 uppercase tracking-wider text-sm">Daftar Anggota Kelompok</h4>
                            </div>
                            <button type="button" id="addMemberBtn"
                                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-bold text-xs shadow-md">
                                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Anggota
                            </button>
                        </div>

                        <div id="membersList" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @php $memberCount = 0; @endphp
                            
                            <!-- Old Input Persistence -->
                            @if(old('members'))
                                @foreach(old('members') as $idx => $m)
                                    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4 relative group">
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-50">
                                            <span class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Anggota {{ $idx + 1 }}</span>
                                            <button type="button" class="text-red-400 hover:text-red-600 transition removeMemberBtn">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                        <div class="space-y-3">
                                            <input type="text" name="members[{{ $idx }}][name]" value="{{ $m['name'] ?? '' }}" placeholder="Nama Lengkap" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                            <input type="text" name="members[{{ $idx }}][nim]" value="{{ $m['nim'] ?? '' }}" placeholder="NIM" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                            <input type="hidden" name="members[{{ $idx }}][program_studi]" value="{{ $m['program_studi'] ?? '' }}">
                                            <input type="email" name="members[{{ $idx }}][email]" value="{{ $m['email'] ?? '' }}" placeholder="Email" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                            <input type="text" name="members[{{ $idx }}][phone]" value="{{ $m['phone'] ?? '' }}" placeholder="No. Telepon" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                        </div>
                                    </div>
                                @endforeach
                            
                            <!-- OCR Results Persistence -->
                            @elseif(!empty($ocrData['members']))
                                @foreach($ocrData['members'] as $idx => $m)
                                    @if($idx > 0) <!-- Skip leader -->
                                        @php $memberIdx = $idx - 1; @endphp
                                        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4 relative group hover:border-blue-200 transition-colors">
                                            <div class="flex justify-between items-center pb-2 border-b border-gray-50">
                                                <span class="text-[10px] font-black text-blue-200 uppercase tracking-[0.2em]">Anggota {{ $memberIdx + 1 }} (OCR)</span>
                                                <button type="button" class="text-gray-300 hover:text-red-500 transition removeMemberBtn">
                                                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                                                </button>
                                            </div>
                                            <div class="space-y-3">
                                                <div class="relative">
                                                    <input type="text" name="members[{{ $memberIdx }}][name]" value="{{ $m['Nama'] ?? '' }}" placeholder="Nama Lengkap" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 pl-10 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                                    <i data-lucide="user" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <div class="relative">
                                                    <input type="text" name="members[{{ $memberIdx }}][nim]" value="{{ $m['NIM'] ?? '' }}" placeholder="NIM" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 pl-10 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                                    <i data-lucide="hash" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <input type="hidden" name="members[{{ $memberIdx }}][program_studi]" value="{{ $m['Prodi'] ?? '' }}">
                                                <div class="relative">
                                                    <input type="email" name="members[{{ $memberIdx }}][email]" placeholder="Alamat Email" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 pl-10 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                                    <i data-lucide="mail" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                                                </div>
                                                <div class="relative">
                                                    <input type="text" name="members[{{ $memberIdx }}][phone]" placeholder="No. WhatsApp" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 pl-10 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                                    <i data-lucide="phone" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </section>
                    @endif

                    <!-- FOOTER BUTTONS -->
                    <div class="pt-8 flex flex-col sm:flex-row gap-4 border-t border-gray-100">
                        <a href="{{ route('mahasiswa.dashboard') }}" 
                           class="flex-1 px-6 py-4 border border-gray-200 text-gray-600 rounded-2xl font-bold hover:bg-gray-50 transition text-center">
                            Batal
                        </a>
                        <button type="submit" id="submitBtn"
                            class="flex-[2] flex items-center justify-center gap-3 bg-blue-600 text-white px-6 py-4 rounded-2xl font-bold hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all hover:-translate-y-1 active:translate-y-0">
                            <i data-lucide="send" class="w-5 h-5"></i>
                            Kirim Pengajuan Sekarang
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();

            const membersList = document.getElementById('membersList');
            const addMemberBtn = document.getElementById('addMemberBtn');
            const departmentEl = document.getElementById('department_id');
            const durationEl = document.getElementById('duration');
            const durationValueEl = document.getElementById('duration_value');
            const periodStartEl = document.getElementById('period_start');
            const periodEndEl = document.getElementById('period_end');
            const quotaInfo = document.getElementById('quotaInfo');
            const submitBtn = document.getElementById('submitBtn');
            const applyForm = document.getElementById('applyForm');
            const periodInfo = document.getElementById('periodInfo');

            // Handle Department Change
            departmentEl.addEventListener('change', () => {
                const selectedOption = departmentEl.options[departmentEl.selectedIndex];
                if (departmentEl.value && selectedOption.dataset.duration) {
                    durationValueEl.value = selectedOption.dataset.duration;
                    durationEl.value = selectedOption.dataset.duration + ' Bulan';
                } else {
                    durationValueEl.value = '';
                    durationEl.value = 'Pilih Departemen Dahulu';
                }
                checkQuota();
            });
            
            if (departmentEl.value) departmentEl.dispatchEvent(new Event('change'));

            // Handle Add Member
            if(addMemberBtn) {
                addMemberBtn.addEventListener('click', () => {
                    const idx = membersList.children.length;
                    const div = document.createElement('div');
                    div.className = "bg-white border border-gray-100 rounded-2xl p-5 shadow-sm space-y-4 relative group animate-in fade-in zoom-in duration-300";
                    div.innerHTML = `
                        <div class="flex justify-between items-center pb-2 border-b border-gray-50">
                            <span class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Anggota Baru</span>
                            <button type="button" class="text-red-400 hover:text-red-600 transition removeMemberBtn">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <input type="text" name="members[${idx}][name]" placeholder="Nama Lengkap" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" name="members[${idx}][nim]" placeholder="NIM" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="email" name="members[${idx}][email]" placeholder="Email" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" name="members[${idx}][phone]" placeholder="No. Telepon" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    `;
                    membersList.appendChild(div);
                    lucide.createIcons();
                    
                    div.querySelector('.removeMemberBtn').onclick = () => {
                        div.classList.add('fade-out', 'zoom-out');
                        setTimeout(() => div.remove(), 200);
                    };
                });
            }

            // Existing remove buttons
            document.querySelectorAll('.removeMemberBtn').forEach(btn => {
                btn.onclick = () => btn.closest('.group').remove();
            });

            // Calculate Period
            function calculatePeriodInfo() {
                periodInfo.className = 'p-3 rounded-xl text-xs font-medium hidden';
                submitBtn.disabled = false;

                const startStr = periodStartEl.value;
                const endStr = periodEndEl.value;
                const maxDuration = parseInt(durationValueEl.value) || 0;

                if (!startStr || !endStr) return;

                const startDate = new Date(startStr);
                const endDate = new Date(endStr);

                if (endDate < startDate) {
                    periodInfo.classList.remove('hidden');
                    periodInfo.classList.add('bg-red-100', 'text-red-700');
                    periodInfo.innerHTML = '<strong>❌ Eror:</strong> Tanggal selesai tidak valid.';
                    submitBtn.disabled = true;
                    return;
                }

                let months = endDate.getMonth() - startDate.getMonth() + (12 * (endDate.getFullYear() - startDate.getFullYear()));
                let days = endDate.getDate() - startDate.getDate();
                if (days < 0) { months--; const pm = new Date(endDate.getFullYear(), endDate.getMonth(), 0); days += pm.getDate(); }

                periodInfo.classList.remove('hidden');
                periodInfo.classList.add('bg-blue-100', 'text-blue-700');
                periodInfo.innerHTML = `<strong>✓ Durasi Pengajuan:</strong> ${months} bulan ${days} hari`;
            }

            periodStartEl.onchange = calculatePeriodInfo;
            periodEndEl.onchange = calculatePeriodInfo;

            // Quota Check
            let quotaCheckTimer = null;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            async function checkQuota() {
                quotaInfo.className = 'p-3 rounded-xl text-xs font-medium hidden';
                const dep = departmentEl.value;
                const pstart = periodStartEl.value;
                const pend = periodEndEl.value;

                if (!dep || !pstart || !pend || submitBtn.disabled) return;

                if (quotaCheckTimer) clearTimeout(quotaCheckTimer);
                quotaCheckTimer = setTimeout(async () => {
                    try {
                        const res = await fetch("{{ url('/quota/check') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ department_id: dep, period_start: pstart, period_end: pend })
                        });
                        if (res.ok) {
                            const data = await res.json();
                            quotaInfo.classList.remove('hidden');
                            if (data.ok) {
                                quotaInfo.classList.add('bg-green-100', 'text-green-700');
                                quotaInfo.innerHTML = `<strong>✓ Kuota Tersedia:</strong> Sisa ${data.remaining} kursi.`;
                            } else {
                                quotaInfo.classList.add('bg-red-100', 'text-red-700');
                                quotaInfo.innerHTML = `<strong>❌ Kuota Penuh:</strong> ${data.message}`;
                                submitBtn.disabled = true;
                            }
                        }
                    } catch (err) { console.error(err); }
                }, 500);
            }

            departmentEl.onchange = checkQuota;
            periodStartEl.onchange = () => { calculatePeriodInfo(); checkQuota(); };
            periodEndEl.onchange = () => { calculatePeriodInfo(); checkQuota(); };

            // Surat Laporan Upload
            const suratLaporanFile = document.getElementById('suratLaporanFile');
            const suratLaporanFileName = document.getElementById('suratLaporanFileName');
            const suratLaporanStatus = document.getElementById('suratLaporanStatus');
            const suratLaporanPath = document.getElementById('suratLaporanPath');

            suratLaporanFile.onchange = async function() {
                const file = this.files[0];
                if (!file) return;

                suratLaporanFileName.textContent = file.name;
                suratLaporanStatus.classList.remove('hidden');
                suratLaporanStatus.className = 'mt-3 p-3 rounded-xl bg-blue-100 text-blue-700 text-xs font-bold animate-pulse';
                suratLaporanStatus.innerHTML = 'SEDANG MENGUNGGAH...';

                const fd = new FormData();
                fd.append('file', file);

                try {
                    const res = await fetch('/api/surat-laporan/upload', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf },
                        body: fd
                    });
                    const data = await res.json();
                    if (data.success) {
                        suratLaporanPath.value = data.file_path;
                        suratLaporanStatus.className = 'mt-3 p-3 rounded-xl bg-green-100 text-green-700 text-xs font-bold';
                        suratLaporanStatus.innerHTML = '✓ UNGGAH BERHASIL';
                    } else {
                        suratLaporanStatus.className = 'mt-3 p-3 rounded-xl bg-red-100 text-red-700 text-xs font-bold';
                        suratLaporanStatus.innerHTML = '❌ GAGAL: ' + data.message;
                    }
                } catch (e) {
                    suratLaporanStatus.className = 'mt-3 p-3 rounded-xl bg-red-100 text-red-700 text-xs font-bold';
                    suratLaporanStatus.innerHTML = '❌ TERJADI KESALAHAN';
                }
            };
        });
    </script>
</x-app-layout>