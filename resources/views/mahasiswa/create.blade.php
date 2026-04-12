@section('title', 'Buat Pengajuan Magang Baru')
<x-app-layout>
    <div class="p-6">
        <!-- BREADCRUMB -->
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('mahasiswa.dashboard') }}" class="text-blue-600 hover:underline">Dashboard</a>
            <span> | </span>
            <span class="font-semibold text-gray-800">Buat Pengajuan</span>
        </div>

        <div class="min-h-screen bg-gray-100 flex items-center justify-center py-10 px-4">
            <div class="bg-white shadow-2xl rounded-2xl w-full max-w-4xl px-10 py-12 relative">

                <!-- Header -->
                <div class="mb-10 pb-4 border-b border-gray-200">
                    <h3 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                        <i data-lucide="clipboard-list" class="w-7 h-7 text-gray-600 hidden md:inline-block"></i>
                        Form Pengajuan Magang - {{ $type === 'group' ? 'Kelompok' : 'Individu' }}
                    </h3>
                <p class="text-gray-500 mt-2 text-sm sm:text-base">
                    Silakan lengkapi data berikut dengan benar untuk pengajuan magang Anda.
                </p>
                </div>

            <!-- FORM START -->
            <form id="applyForm" action="{{ route('apply.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Hidden type field -->
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Departemen -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="building" class="w-5 h-5 text-gray-500"></i>
                            Departemen Tujuan
                        </label>
                        <select name="department_id" id="department_id"
                            class="border-gray-300 rounded-lg p-3 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d['id'] }}" data-duration="{{ $d['duration'] }}" {{ old('department_id') == $d['id'] ? 'selected' : '' }}>{{ $d['name'] }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama / Ketua -->
                    <div>
                        <label id="labelLeader" class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="user" class="w-5 h-5 text-gray-500"></i>
                            {{ $type === 'group' ? 'Nama Ketua' : 'Nama' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="leader_name" id="leader_name"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('leader_name') border-red-500 @enderror"
                            value="{{ old('leader_name', $ocrData['nama'] ?? '') }}">
                        @error('leader_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="mail" class="w-5 h-5 text-gray-500"></i>
                            Email <span class="text-red-500">*</span>
                        </label>

                        <input type="email" name="leader_email" id="leader_email" value="{{ old('leader_email') }}"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('leader_email') border-red-500 @enderror">

                        @error('leader_email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor Telepon -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="phone" class="w-5 h-5 text-gray-500"></i>
                            Nomor Telepon / WhatsApp <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="leader_phone" id="leader_phone" value="{{ old('leader_phone') }}"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('leader_phone') border-red-500 @enderror">

                        @error('leader_phone')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Universitas -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="school" class="w-5 h-5 text-gray-500"></i>
                            Universitas <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="university" id="university" value="{{ old('university') }}"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('university') border-red-500 @enderror">

                        @error('university')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Program Studi -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="book-open" class="w-5 h-5 text-gray-500"></i>
                            Program Studi <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="major" id="major" value="{{ old('major', $ocrData['major'] ?? '') }}"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('major') border-red-500 @enderror">

                        @error('major')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Durasi -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="clock" class="w-5 h-5 text-gray-500"></i>
                            Durasi Maks Magang <span class="text-red-500">*</span>
                        </label>

                        <!-- Hidden input untuk submit nilai numerik -->
                        <input type="hidden" name="duration" id="duration_value">

                        <!-- Display field -->
                        <input type="text" id="duration" value="{{ old('duration') }}"
                            readonly
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm bg-gray-100 cursor-not-allowed @error('duration') border-red-500 @enderror">

                        @error('duration')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PERIODE MAGANG (FINAL) -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="calendar" class="w-5 h-5 text-gray-500"></i>
                            Tanggal Mulai Magang <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="period_start" id="period_start" value="{{ old('period_start') }}"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('period_start') border-red-500 @enderror">
                        @error('period_start')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="calendar-check" class="w-5 h-5 text-gray-500"></i>
                            Tanggal Selesai Magang <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="period_end" id="period_end" value="{{ old('period_end') }}"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('period_end') border-red-500 @enderror">
                        @error('period_end')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Periode Info (client-side calculation) -->
                    <div class="md:col-span-2">
                        <div id="periodInfo" class="p-3 rounded-lg text-sm hidden"></div>
                    </div>

                    <!-- Quota Info (client-side) -->
                    <div class="md:col-span-2">
                        <div id="quotaInfo" class="p-3 rounded-lg text-sm hidden"></div>
                    </div>

                    <!-- Hidden field untuk file path dari OCR di step sebelumnya -->
                    <input type="hidden" name="surat_permohonan_path" id="suratPermohonanPath" value="{{ old('surat_permohonan_path', $ocrData['surat_permohonan_path'] ?? '') }}">

                    <!-- OCR Status Display -->
                    <div class="md:col-span-2">
                        <div id="ocrStatus" class="p-3 rounded-lg border bg-green-50 border-green-200 text-green-700">
                            <i data-lucide="check-circle" class="w-4 h-4 inline"></i> Surat Permohonan telah berhasil di-scan pada tahap sebelumnya
                        </div>
                    </div>

                    <!-- Upload Surat Laporan -->
                    <div class="md:col-span-2">
                        <label class="block font-semibold mb-2 text-gray-700 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-5 h-5 text-gray-500"></i>
                            Surat Laporan (PDF) <span class="text-red-500">*</span>
                        </label>

                        <div id="suratLaporanDropzone"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-8 flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-400 transition relative bg-white @error('file') border-red-500 @enderror">

                            <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-400 mb-2"></i>
                            <p class="text-gray-500 text-sm">Drag & drop file surat laporan di sini atau klik untuk memilih file</p>
                            <p class="text-gray-400 text-xs mt-1">Hanya PDF, maksimal 5MB</p>

                            <input type="file" id="suratLaporanFile" name="file" accept="application/pdf"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />

                            <span id="suratLaporanFileName" class="mt-2 text-sm font-medium text-gray-700"></span>
                        </div>

                        <!-- Hidden field untuk file path -->
                        <input type="hidden" name="surat_laporan_path" id="suratLaporanPath">

                        <!-- Upload Status -->
                        <div id="suratLaporanStatus" class="mt-3 hidden"></div>

                        @error('file')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Anggota Kelompok (Dinamis) -->
                    <div class="md:col-span-2">
                        <div id="membersContainer" class="space-y-3 {{ $type === 'group' ? '' : 'hidden' }}">
                            <button type="button" id="addMemberBtn"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition font-semibold mb-2">
                                + Tambah Anggota
                            </button>
                            <div id="membersList">
                                <!-- if old members exist, render them -->
                                @if(old('members'))
                                    @foreach(old('members') as $idx => $m)
                                        <div class="border p-4 rounded-lg space-y-2 bg-gray-50">
                                            <div class="flex justify-between items-center">
                                                <h4 class="font-semibold text-gray-700">Anggota {{ $idx + 1 }}</h4>
                                                <button type="button" class="text-red-600 hover:text-red-800 removeMemberBtn">Hapus</button>
                                            </div>
                                            <input type="text" name="members[{{ $idx }}][name]" value="{{ $m['name'] ?? '' }}" placeholder="Nama" class="border-gray-300 rounded-lg p-2 w-full">
                                            <input type="email" name="members[{{ $idx }}][email]" value="{{ $m['email'] ?? '' }}" placeholder="Email" class="border-gray-300 rounded-lg p-2 w-full">
                                            <input type="text" name="members[{{ $idx }}][phone]" value="{{ $m['phone'] ?? '' }}" placeholder="No. Telepon" class="border-gray-300 rounded-lg p-2 w-full">
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tombol -->
                <button type="submit" id="submitBtn"
                    class="flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 shadow-lg transition w-full justify-center">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    Kirim Pengajuan
                </button>

            </form>
        </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();

            const typeField = document.querySelector('input[name="type"]');
            const appType = typeField.value;
            const membersContainer = document.getElementById('membersContainer');
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
            departmentEl.addEventListener('change', () => {
                const selectedOption = departmentEl.options[departmentEl.selectedIndex];
                if (departmentEl.value && selectedOption.dataset.duration) {
                    durationValueEl.value = selectedOption.dataset.duration; // hidden field: just number
                    durationEl.value = selectedOption.dataset.duration + ' bulan'; // display: with "bulan"
                } else {
                    durationValueEl.value = '';
                    durationEl.value = '';
                }
            });
            
            // Trigger change event saat load halaman jika ada old value
            if (departmentEl.value) {
                departmentEl.dispatchEvent(new Event('change'));
            }

            // Tambah anggota kelompok
            addMemberBtn.addEventListener('click', () => {
                const idx = membersList.children.length;
                const memberDiv = document.createElement('div');
                memberDiv.classList.add('border', 'p-4', 'rounded-lg', 'space-y-2', 'bg-gray-50');

                memberDiv.innerHTML = `
                    <div class="flex justify-between items-center">
                        <h4 class="font-semibold text-gray-700">Anggota ${idx + 1}</h4>
                        <button type="button" class="text-red-600 hover:text-red-800 removeMemberBtn">Hapus</button>
                    </div>
                    <input type="text" name="members[${idx}][name]" placeholder="Nama" class="border-gray-300 rounded-lg p-2 w-full">
                    <input type="email" name="members[${idx}][email]" placeholder="Email" class="border-gray-300 rounded-lg p-2 w-full">
                    <input type="text" name="members[${idx}][phone]" placeholder="No. Telepon" class="border-gray-300 rounded-lg p-2 w-full">
                `;

                membersList.appendChild(memberDiv);

                // Hapus anggota
                memberDiv.querySelector('.removeMemberBtn').addEventListener('click', () => {
                    membersList.removeChild(memberDiv);
                });
            });

            // Calculate and validate period (months and days)
            const periodInfo = document.getElementById('periodInfo');

            function calculatePeriodInfo() {
                periodInfo.classList.add('hidden');
                periodInfo.innerHTML = '';
                submitBtn.disabled = false;

                const startStr = periodStartEl.value;
                const endStr = periodEndEl.value;
                const maxDuration = parseInt(durationValueEl.value) || 0;

                if (!startStr || !endStr) return;

                const startDate = new Date(startStr);
                const endDate = new Date(endStr);

                // Validasi: end date harus >= start date
                if (endDate < startDate) {
                    periodInfo.classList.remove('hidden');
                    periodInfo.classList.add('bg-red-50', 'border', 'border-red-200', 'text-red-700');
                    periodInfo.innerHTML = '<strong>❌ Kesalahan:</strong> Tanggal selesai harus sama atau setelah tanggal mulai.';
                    submitBtn.disabled = true;
                    return;
                }

                // Hitung bulan dan hari
                let months = endDate.getMonth() - startDate.getMonth() + (12 * (endDate.getFullYear() - startDate.getFullYear()));
                let days = endDate.getDate() - startDate.getDate();

                if (days < 0) {
                    months--;
                    const prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
                    days += prevMonth.getDate();
                }

                // Validasi: durasi tidak boleh melebihi maksimal
                if (maxDuration > 0 && months > maxDuration) {
                    periodInfo.classList.remove('hidden');
                    periodInfo.classList.add('bg-red-50', 'border', 'border-red-200', 'text-red-700');
                    periodInfo.innerHTML = `<strong>❌ Exceeds Max Duration:</strong> Durasi maksimal ${maxDuration} bulan, Anda memilih ${months} bulan ${days} hari.`;
                    submitBtn.disabled = true;
                    return;
                }

                // Durasi OK
                periodInfo.classList.remove('hidden');
                periodInfo.classList.add('bg-green-50', 'border', 'border-green-200', 'text-green-700');
                periodInfo.innerHTML = `<strong>✓ Durasi Pengajuan:</strong> ${months} bulan ${days} hari (Maks: ${maxDuration} bulan)`;
                submitBtn.disabled = false;
            }

            periodStartEl.addEventListener('change', calculatePeriodInfo);
            periodEndEl.addEventListener('change', calculatePeriodInfo);

            // If page loaded with values, calculate once
            if (periodStartEl.value && periodEndEl.value) {
                calculatePeriodInfo();
            }

            // Quota check helper (AJAX)
            let quotaCheckTimer = null;
            const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

            function showQuotaMessage(type, html) {
                quotaInfo.classList.remove('hidden', 'bg-green-50', 'bg-yellow-50', 'bg-red-50');
                quotaInfo.classList.add(type === 'ok' ? 'bg-green-50' : (type === 'warn' ? 'bg-yellow-50' : 'bg-red-50'));
                quotaInfo.innerHTML = html;
            }

            function clearQuotaMessage() {
                quotaInfo.classList.add('hidden');
                quotaInfo.innerHTML = '';
            }

            async function checkQuota() {
                // Reset only quota message
                clearQuotaMessage();

                const dep = departmentEl.value;
                const pstart = periodStartEl.value;
                const pend = periodEndEl.value;

                if (!dep || !pstart || !pend) return;

                // Skip quota check if period validation already failed
                if (submitBtn.disabled) return;

                // Simple client-side validation: period_end >= period_start
                if (new Date(pend) < new Date(pstart)) {
                    showQuotaMessage('error', '<strong>Kesalahan tanggal:</strong> Tanggal selesai harus sama atau setelah tanggal mulai.');
                    submitBtn.disabled = true;
                    return;
                }

                // debounce to avoid many requests
                if (quotaCheckTimer) clearTimeout(quotaCheckTimer);
                quotaCheckTimer = setTimeout(async () => {
                    try {
                        // Endpoint: /quota/check (POST)
                        // NOTE: implement this route on server to return JSON:
                        // { ok: true/false, remaining: int, quota: int, message: '...' }
                        const res = await fetch("{{ url('/quota/check') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({
                                department_id: dep,
                                period_start: pstart,
                                period_end: pend
                            })
                        });

                        if (!res.ok) {
                            // if endpoint missing or returns error, fallback to server-side check on submit
                            return;
                        }

                        const data = await res.json();
                        // expected: { ok: boolean, remaining: int, quota: int, message: string }
                        if (data.ok) {
                            showQuotaMessage('ok', `<strong>Kuota tersedia:</strong> ${data.remaining} dari ${data.quota} tersisa untuk periode ini.`);
                            submitBtn.disabled = false;
                        } else {
                            showQuotaMessage('error', `<strong>Kuota penuh:</strong> ${data.message || 'Tidak tersedia kursi pada periode ini.'}`);
                            submitBtn.disabled = true;
                        }
                    } catch (err) {
                        // network error: ignore and let backend validate when submit
                        console.warn('Quota check failed (network):', err);
                    }
                }, 450);
            }

            // attach listeners
            departmentEl.addEventListener('change', checkQuota);
            periodStartEl.addEventListener('change', checkQuota);
            periodEndEl.addEventListener('change', checkQuota);

            // if page loaded with values, check once
            if (departmentEl.value && periodStartEl.value && periodEndEl.value) {
                checkQuota();
            }

            // Prevent multiple submit clicks (UX)
            applyForm.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
            });

            // ========== SURAT LAPORAN UPLOAD HANDLER ==========
            const suratLaporanDropzone = document.getElementById('suratLaporanDropzone');
            const suratLaporanFile = document.getElementById('suratLaporanFile');
            const suratLaporanFileName = document.getElementById('suratLaporanFileName');
            const suratLaporanStatus = document.getElementById('suratLaporanStatus');
            const suratLaporanPath = document.getElementById('suratLaporanPath');

            // Handle file upload
            const handleSuratLaporanUpload = async function(file) {
                if (!file || file.type !== 'application/pdf') {
                    suratLaporanStatus.textContent = 'File harus PDF';
                    suratLaporanStatus.className = 'mt-3 p-3 rounded-lg border bg-red-50 border-red-200 text-red-700';
                    return;
                }

                suratLaporanFileName.textContent = file.name;
                suratLaporanStatus.classList.remove('hidden');
                suratLaporanStatus.className = 'mt-3 p-3 rounded-lg border bg-blue-50 border-blue-200 text-blue-700';
                suratLaporanStatus.innerHTML = '<i data-lucide="loader" class="w-4 h-4 inline animate-spin"></i> Uploading surat laporan...';
                lucide.createIcons();

                const formData = new FormData();
                formData.append('file', file);

                try {
                    const response = await fetch('/api/surat-laporan/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: formData
                    });

                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error(`Server error (${response.status}): Response tidak valid JSON`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        suratLaporanPath.value = data.file_path;
                        suratLaporanStatus.className = 'mt-3 p-3 rounded-lg border bg-green-50 border-green-200 text-green-700';
                        suratLaporanStatus.innerHTML = '✓ Surat laporan berhasil tergupload.';
                    } else {
                        suratLaporanStatus.className = 'mt-3 p-3 rounded-lg border bg-red-50 border-red-200 text-red-700';
                        suratLaporanStatus.innerHTML = '❌ ' + (data.message || 'Upload surat laporan gagal');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    suratLaporanStatus.className = 'mt-3 p-3 rounded-lg border bg-red-50 border-red-200 text-red-700';
                    suratLaporanStatus.innerHTML = '❌ Terjadi kesalahan: ' + error.message;
                }
            };

            suratLaporanFile.addEventListener('change', function() {
                if (this.files.length > 0) {
                    handleSuratLaporanUpload(this.files[0]);
                }
            });

            suratLaporanDropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                suratLaporanDropzone.classList.add('border-blue-400', 'bg-blue-50');
            });

            suratLaporanDropzone.addEventListener('dragleave', () => {
                suratLaporanDropzone.classList.remove('border-blue-400', 'bg-blue-50');
            });

            suratLaporanDropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                suratLaporanDropzone.classList.remove('border-blue-400', 'bg-blue-50');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    suratLaporanFile.files = files;
                    handleSuratLaporanUpload(files[0]);
                }
            });
        });
    </script>

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `
                        <div>{{ session('success') }}</div>
                        <div style="margin-top:10px; font-size:20px; font-weight:bold;">
                            {{ session('code') }}
                        </div>
                    `,
                    confirmButtonColor: '#3b82f6',
                });
            });
        </script>
    @endif
    @if($errors->has('quota') || $errors->has('general'))
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

</x-app-layout>