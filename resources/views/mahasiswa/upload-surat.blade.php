@section('title', 'Upload Surat Permohonan')
<x-app-layout>
<script src="https://unpkg.com/lucide@latest"></script>

<div class="p-6 max-w-3xl mx-auto">
    <!-- BREADCRUMB -->
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('mahasiswa.dashboard') }}" class="hover:text-blue-600 transition">Mahasiswa</a>
        <span>/</span>
        <span class="text-gray-800 font-semibold">Upload Surat Permohonan</span>
    </div>

    <h1 class="text-3xl font-bold text-gray-800 mb-2">Upload Surat Permohonan</h1>
    <p class="text-gray-500 mb-8 text-sm">Upload dokumen PDF, sistem akan otomatis membaca dan mengisi data menggunakan OCR.</p>

    <div x-data="uploadFormData()" class="space-y-6">

        <!-- UPLOAD ZONE -->
        <div class="bg-white rounded-xl border-2 border-dashed border-gray-300 hover:border-blue-400 transition-colors cursor-pointer shadow-sm"
             @dragover.prevent="dragover = true" @dragleave="dragover = false" @drop.prevent="handleDrop"
             :class="dragover && 'border-blue-500 bg-blue-50'"
             @click="$refs.fileInput.click()">
            <input type="file" @change="handleFileChange" accept=".pdf" class="hidden" x-ref="fileInput">
            <div class="text-center py-12 px-6">
                <i data-lucide="file-up" class="w-14 h-14 mx-auto text-gray-300 mb-4"></i>
                <p class="text-lg font-semibold text-gray-700">Drag & drop atau klik untuk pilih file</p>
                <p class="text-sm text-gray-400 mt-1">Format: <strong>PDF</strong> | Maks: <strong>10MB</strong></p>
            </div>
        </div>

        <!-- FILE SELECTED -->
        <template x-if="file">
            <div class="bg-blue-50 px-5 py-4 rounded-xl border border-blue-200 flex items-center gap-4">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i data-lucide="file-text" class="w-6 h-6 text-red-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 truncate" x-text="file.name"></p>
                    <p class="text-xs text-gray-500" x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                </div>
                <button type="button" @click.stop="clearFile()" class="text-gray-400 hover:text-red-500 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </template>

        <!-- UPLOAD BUTTON -->
        <template x-if="file && !ocrResults">
            <button type="button" @click="uploadFile()"
                    :disabled="uploading"
                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 disabled:bg-gray-400 transition flex items-center justify-center gap-2 shadow">
                <template x-if="!uploading">
                    <span class="flex items-center gap-2">
                        <i data-lucide="scan" class="w-5 h-5"></i>
                        Upload & Scan OCR
                    </span>
                </template>
                <template x-if="uploading">
                    <span class="flex items-center gap-2">
                        <span class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        Memproses OCR...
                    </span>
                </template>
            </button>
        </template>

        <!-- OCR RESULTS -->
        <template x-if="ocrResults">
            <div class="bg-white rounded-xl border border-green-200 shadow-sm overflow-hidden">

                <!-- Header -->
                <div class="bg-green-50 px-6 py-4 border-b border-green-200 flex items-center gap-3">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-green-800">Hasil Scan OCR Berhasil</h2>
                        <p class="text-xs text-green-600">Data berikut diekstrak otomatis dari dokumen. Periksa sebelum melanjutkan.</p>
                    </div>
                    <div class="ml-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-bold"
                              :class="ocrResults.type === 'individual' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                              x-text="ocrResults.type === 'individual' ? '👤 Individu' : '👥 Kelompok'"></span>
                    </div>
                </div>

                <!-- Data Grid -->
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <!-- Universitas -->
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">🏫 Universitas</label>
                        <p class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm" x-text="ocrResults.university || '—'"></p>
                    </div>

                    <!-- Jurusan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">📚 Jurusan</label>
                        <p class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm" x-text="ocrResults.jurusan || '—'"></p>
                    </div>

                    <!-- Program Studi -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">🎓 Program Studi</label>
                        <p class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm" x-text="ocrResults.program_studi || '—'"></p>
                    </div>

                    <!-- Tanggal Masuk -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">📅 Tanggal Mulai Magang</label>
                        <p class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm" x-text="ocrResults.tanggal_masuk || '—'"></p>
                    </div>

                    <!-- Tanggal Keluar -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">📅 Tanggal Selesai Magang</label>
                        <p class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-800 text-sm" x-text="ocrResults.tanggal_keluar || '—'"></p>
                    </div>

                </div>

                <!-- Daftar Mahasiswa -->
                <template x-if="ocrResults.members && ocrResults.members.length > 0">
                    <div class="px-6 pb-5">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">👨‍🎓 Daftar Mahasiswa</label>
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                                    <tr>
                                        <th class="px-4 py-2 text-left">#</th>
                                        <th class="px-4 py-2 text-left">Nama</th>
                                        <th class="px-4 py-2 text-left">NIM</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <template x-for="(member, index) in ocrResults.members" :key="index">
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-gray-500" x-text="index + 1"></td>
                                            <td class="px-4 py-2 font-medium text-gray-800" x-text="member.Nama || '—'"></td>
                                            <td class="px-4 py-2 text-gray-600 font-mono text-xs" x-text="member.NIM || '—'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- Preview Teks Hasil Scan (Simplified) -->
                <div class="px-6 pb-6">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-green-500"></i>
                            Isi Dokumen Terbaca
                        </label>
                        <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold" 
                              x-text="ocrResults.extracted_text.length + ' Karakter'"></span>
                    </div>
                    <div class="bg-white border-2 border-dashed border-gray-200 rounded-lg p-4">
                        <div class="text-sm text-gray-700 whitespace-pre-wrap font-serif leading-relaxed italic" 
                             x-text="ocrResults.extracted_text"></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex gap-3">
                    <button type="button" @click="resetOCR()"
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-100 transition text-sm">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Upload Ulang
                    </button>

                    <!-- Hidden Form: kirim OCR data ke apply.prefill via POST -->
                    <form id="continueForm" action="{{ route('apply.prefill') }}" method="POST" class="flex-1 flex">
                        @csrf
                        <input type="hidden" name="type"                   :value="ocrResults.type">
                        <input type="hidden" name="ocr_nama"               :value="ocrResults.nama">
                        <input type="hidden" name="ocr_major"              :value="ocrResults.major">
                        <input type="hidden" name="ocr_keahlian"           :value="ocrResults.keahlian">
                        <input type="hidden" name="ocr_university"         :value="ocrResults.university">
                        <input type="hidden" name="ocr_jurusan"            :value="ocrResults.jurusan">
                        <input type="hidden" name="ocr_program_studi"      :value="ocrResults.program_studi">
                        <input type="hidden" name="ocr_tanggal_masuk"      :value="ocrResults.tanggal_masuk">
                        <input type="hidden" name="ocr_tanggal_keluar"     :value="ocrResults.tanggal_keluar">
                        <input type="hidden" name="ocr_extracted_text"     :value="ocrResults.extracted_text">
                        <input type="hidden" name="surat_permohonan_path"  :value="ocrResults.filepath">

                        <!-- Members -->
                        <template x-for="(member, index) in ocrResults.members" :key="'nm' + index">
                            <input type="hidden" :name="'ocr_members[' + index + '][Nama]'" :value="member.Nama">
                        </template>
                        <template x-for="(member, index) in ocrResults.members" :key="'ni' + index">
                            <input type="hidden" :name="'ocr_members[' + index + '][NIM]'" :value="member.NIM">
                        </template>
                        <template x-for="(member, index) in ocrResults.members" :key="'np' + index">
                            <input type="hidden" :name="'ocr_members[' + index + '][Prodi]'" :value="member.Prodi">
                        </template>

                        <button type="submit"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition text-sm shadow">
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            Lanjut ke Form Pengajuan
                        </button>
                    </form>
                </div>

            </div>
        </template>

        <!-- ERROR -->
        <template x-if="error">
            <div class="bg-red-50 px-5 py-4 rounded-xl border border-red-200 flex items-start gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-semibold text-red-800">Gagal Memproses Dokumen</p>
                    <p class="text-sm text-red-700 mt-1" x-text="error"></p>
                </div>
                <button type="button" @click="error = null" class="text-red-400 hover:text-red-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </template>

    </div>
</div>

<script>
function uploadFormData() {
    return {
        file: null,
        dragover: false,
        uploading: false,
        ocrResults: null,
        error: null,

        handleFileChange(event) {
            const files = event.target.files;
            if (files.length > 0) {
                this.file = files[0];
                this.error = null;
                this.ocrResults = null;
            }
        },

        handleDrop(event) {
            this.dragover = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.file = files[0];
                this.error = null;
                this.ocrResults = null;
            }
        },

        clearFile() {
            this.file = null;
            this.$refs.fileInput.value = '';
            this.ocrResults = null;
            this.error = null;
        },

        async uploadFile() {
            if (!this.file) return;

            this.uploading = true;
            this.error = null;

            const formData = new FormData();
            formData.append('file', this.file);

            try {
                const response = await fetch('/api/surat-permohonan/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error(`Server error (${response.status}): Response bukan JSON`);
                }

                const data = await response.json();
                console.log("📥 Data dari Laravel:", data); // Debug di console browser

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal scan OCR');
                }

                this.ocrResults = {
                    nama           : data.nama           || '',
                    university     : data.university     || '',
                    jurusan        : data.jurusan        || '',
                    program_studi  : data.program_studi  || '',
                    major          : data.major          || '',
                    keahlian       : data.keahlian       || '',
                    tanggal_masuk  : data.tanggal_masuk  || '',
                    tanggal_keluar : data.tanggal_keluar || '',
                    type           : data.type           || (data.members && data.members.length > 1 ? 'group' : 'individual'),
                    filepath       : data.file_path      || '',
                    members        : data.members        || [],
                    extracted_text : data.extracted_text ? decodeURIComponent(escape(window.atob(data.extracted_text))) : '',
                };

                // Scroll ke hasil
                this.$nextTick(() => lucide.createIcons());

            } catch (err) {
                this.error = err.message || 'Terjadi kesalahan saat upload file';
            } finally {
                this.uploading = false;
            }
        },

        resetOCR() {
            this.ocrResults = null;
            this.clearFile();
        }
    };
}
</script>

</x-app-layout>
