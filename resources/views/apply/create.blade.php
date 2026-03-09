@section('title', 'Buat Pengajuan Magang Baru')
<x-layouts.guest-wide>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-10 px-4">
        <div class="bg-white shadow-2xl rounded-2xl w-full max-w-4xl px-10 py-12 relative">

            <!-- Header -->
            <div class="mb-10 pb-4 border-b border-gray-200">
                <h3 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i data-lucide="clipboard-list" class="w-7 h-7 text-gray-600 hidden md:inline-block"></i>
                    Form Pengajuan Magang
                </h3>
                <p class="text-gray-500 mt-2 text-sm sm:text-base">
                    Silakan lengkapi data berikut dengan benar untuk pengajuan magang Anda.
                </p>
            </div>

            <!-- FORM START -->
            <form id="applyForm" action="{{ route('apply.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Tipe -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="users" class="w-5 h-5 text-gray-500"></i>
                            Tipe Pendaftaran <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type"
                            class="border-gray-300 rounded-lg p-3 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            <option value="individual" {{ old('type') == 'individual' ? 'selected' : '' }}>Individu</option>
                            <option value="group" {{ old('type') == 'group' ? 'selected' : '' }}>Kelompok</option>
                        </select>
                    </div>

                    <!-- Departemen -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="building" class="w-5 h-5 text-gray-500"></i>
                            Departemen Tujuan (Opsional)
                        </label>
                        <select name="department_id" id="department_id"
                            class="border-gray-300 rounded-lg p-3 w-full focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            <option value="">-- Pilih --</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
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
                            Nama / Ketua <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="leader_name" id="leader_name"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('leader_name') border-red-500 @enderror"
                            value="{{ old('leader_name') }}">
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

                        <input type="text" name="major" id="major" value="{{ old('major') }}"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('major') border-red-500 @enderror">

                        @error('major')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Durasi -->
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700 flex items-center gap-2">
                            <i data-lucide="clock" class="w-5 h-5 text-gray-500"></i>
                            Durasi (max. 5 bulan) <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="duration" id="duration" value="{{ old('duration') }}"
                            class="border-gray-300 rounded-lg p-3 w-full shadow-sm @error('duration') border-red-500 @enderror">

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

                    <!-- Quota Info (client-side) -->
                    <div class="md:col-span-2">
                        <div id="quotaInfo" class="p-3 rounded-lg text-sm hidden"></div>
                    </div>

                    <!-- Upload PDF -->
                    <div class="md:col-span-2">
                        <label class="block font-semibold mb-2 text-gray-700 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-5 h-5 text-gray-500"></i>
                            Surat Permohonan (PDF) <span class="text-red-500">*</span>
                        </label>

                        <div id="dropzone"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-8 flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-400 transition relative bg-white @error('file') border-red-500 @enderror">

                            <i data-lucide="upload-cloud" class="w-10 h-10 text-gray-400 mb-2"></i>
                            <p class="text-gray-500 text-sm">Drag & drop file di sini atau klik untuk memilih file</p>
                            <p class="text-gray-400 text-xs mt-1">Hanya PDF, maksimal 5MB</p>

                            <input type="file" name="file" accept="application/pdf"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />

                            <span id="fileName" class="mt-2 text-sm font-medium text-gray-700"></span>
                        </div>

                        @error('file')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Anggota Kelompok (Dinamis) -->
                    <div class="md:col-span-2">
                        <div id="membersContainer" class="space-y-3 {{ old('type') == 'group' ? '' : 'hidden' }}">
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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();

            const typeSelect = document.getElementById('type');
            const labelLeader = document.getElementById('labelLeader');
            const membersContainer = document.getElementById('membersContainer');
            const membersList = document.getElementById('membersList');
            const addMemberBtn = document.getElementById('addMemberBtn');

            const departmentEl = document.getElementById('department_id');
            const periodStartEl = document.getElementById('period_start');
            const periodEndEl = document.getElementById('period_end');
            const quotaInfo = document.getElementById('quotaInfo');
            const submitBtn = document.getElementById('submitBtn');
            const applyForm = document.getElementById('applyForm');

            // toggle group members
            typeSelect.addEventListener('change', () => {
                if (typeSelect.value === 'group') {
                    labelLeader.textContent = 'Nama Ketua';
                    membersContainer.classList.remove('hidden');
                } else {
                    labelLeader.textContent = 'Nama';
                    membersContainer.classList.add('hidden');
                    membersList.innerHTML = '';
                }
            });

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
                `;

                membersList.appendChild(memberDiv);

                // Hapus anggota
                memberDiv.querySelector('.removeMemberBtn').addEventListener('click', () => {
                    membersList.removeChild(memberDiv);
                });
            });

            // Dropzone upload
            const dropzone = document.getElementById('dropzone');
            const fileInput = dropzone.querySelector('input[type="file"]');
            const fileName = document.getElementById('fileName');

            fileInput.addEventListener('change', function() {
                fileName.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : '';
            });

            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('border-blue-400', 'bg-blue-50');
            });

            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('border-blue-400', 'bg-blue-50');
            });

            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-blue-400', 'bg-blue-50');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileName.textContent = files[0].name;
                }
            });

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
                // reset
                submitBtn.disabled = false;
                clearQuotaMessage();

                const dep = departmentEl.value;
                const pstart = periodStartEl.value;
                const pend = periodEndEl.value;

                if (!dep || !pstart || !pend) return;

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


    <!-- Floating Pengumuman -->
    <a href="{{ route('pengumuman.index') }}"
        class="fixed bottom-6 right-6 bg-yellow-100 text-yellow-800 border border-yellow-300 shadow-lg rounded-full md:rounded-xl flex items-center gap-2 px-4 py-3 md:px-5 md:py-3 hover:bg-yellow-200 transition font-semibold animate-float z-50">
        <i data-lucide="megaphone" class="w-6 h-6"></i>
        <span class="hidden md:inline-block">Pengumuman</span>
    </a>

    <style>
        @keyframes floatUpDown {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-6px);
            }

            100% {
                transform: translateY(0);
            }
        }

        .animate-float {
            animation: floatUpDown 2.3s ease-in-out infinite;
        }
    </style>

</x-layouts.guest-wide>
