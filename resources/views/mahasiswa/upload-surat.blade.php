@section('title', 'Upload Surat Permohonan')
<x-app-layout>
<div class="p-6 max-w-2xl mx-auto">
    <!-- BREADCRUMB -->
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-600">
        <span class="font-semibold text-gray-800">Mahasiswa</span>
        <span>/</span>
        <span class="font-semibold text-gray-800">Buat Pengajuan</span>
        <span>/</span>
        <span class="font-semibold text-gray-800">Upload Surat Permohonan</span>
    </div>

    <h1 class="text-3xl font-bold mb-6">Upload Surat Permohonan Magang</h1>

    <div x-data="uploadFormData()" class="space-y-6">
        <!-- UPLOAD ZONE -->
        <div class="bg-white p-6 rounded-lg border-2 border-dashed border-gray-300 hover:border-blue-500 transition cursor-pointer"
             @dragover="dragover = true" @dragleave="dragover = false" @drop="handleDrop"
             :class="dragover && 'border-blue-500 bg-blue-50'">
            <input type="file" @change="handleFileChange" accept=".pdf" class="hidden" #fileInput x-ref="fileInput">
            <div @click="$refs.fileInput.click()" class="text-center py-8">
                <i data-lucide="cloud-upload" class="w-12 h-12 mx-auto text-gray-400 mb-2"></i>
                <p class="text-lg font-semibold text-gray-800">Drag & drop surat permohonan PDF</p>
                <p class="text-sm text-gray-600 mt-1">atau klik untuk pilih file</p>
                <p class="text-xs text-gray-500 mt-2">Format: PDF | Maks: 10MB</p>
            </div>
        </div>

        <!-- FILE STATUS -->
        <template x-if="file">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="flex items-center gap-3">
                    <i data-lucide="file-pdf" class="w-6 h-6 text-red-500"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-800" x-text="file.name"></p>
                        <p class="text-xs text-gray-600" x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                    </div>
                    <button type="button" @click="clearFile()" class="text-red-600 hover:text-red-800">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </template>

        <!-- UPLOAD BUTTON -->
        <template x-if="file">
            <button type="button" @click="uploadFile()" 
                    :disabled="uploading"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 disabled:bg-gray-400">
                <template x-if="!uploading">
                    <span>Upload & Scan OCR</span>
                </template>
                <template x-if="uploading">
                    <span class="flex items-center justify-center gap-2">
                        <span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        Scanning...
                    </span>
                </template>
            </button>
        </template>

        <!-- OCR RESULTS -->
        <template x-if="ocrResults">
            <div class="bg-green-50 p-6 rounded-lg border border-green-200 space-y-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    <h2 class="text-lg font-semibold text-green-800">Hasil Scan OCR</h2>
                </div>

                <!-- EXTRACTED DATA -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Nama</label>
                        <p class="mt-1 text-gray-800 p-2 bg-white rounded border" x-text="ocrResults.nama || '-'"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Program Studi</label>
                        <p class="mt-1 text-gray-800 p-2 bg-white rounded border" x-text="ocrResults.major || '-'"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Tipe Pengajuan</label>
                        <p class="mt-1">
                            <span class="px-3 py-1 rounded text-sm font-semibold"
                                  :class="(ocrResults.type === 'individual' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700')"
                                  x-text="ocrResults.type === 'individual' ? 'Individu' : 'Kelompok'"></span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Raw Text (Preview)</label>
                        <textarea readonly class="mt-1 w-full p-2 bg-white rounded border text-xs h-24"
                                  x-text="ocrResults.raw_text"></textarea>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="flex gap-2 pt-4 border-t border-green-200">
                    <button type="button" @click="resetOCR()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded font-semibold hover:bg-gray-50">
                        Upload File Lain
                    </button>
                    <a :href="'{{ route('apply.form') }}?type=' + type + '&ocr_nama=' + encodeURIComponent(ocrResults.nama) + '&ocr_major=' + encodeURIComponent(ocrResults.major) + '&ocr_type=' + ocrResults.type + '&surat_permohonan_path=' + encodeURIComponent(ocrResults.filepath)"
                       class="flex-1 px-4 py-2 bg-green-600 text-white text-center rounded font-semibold hover:bg-green-700">
                        Lanjut ke Form
                    </a>
                </div>
            </div>
        </template>

        <!-- ERROR MESSAGE -->
        <template x-if="error">
            <div class="bg-red-50 p-4 rounded-lg border border-red-200 flex items-start gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="font-semibold text-red-800">Kesalahan Upload</p>
                    <p class="text-sm text-red-700 mt-1" x-text="error"></p>
                </div>
                <button type="button" @click="error = null" class="ml-auto text-red-600 hover:text-red-800">
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
        type: '{{ $type }}',

        handleFileChange(event) {
            const files = event.target.files;
            if (files.length > 0) {
                this.file = files[0];
                this.error = null;
            }
        },

        handleDrop(event) {
            event.preventDefault();
            this.dragover = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.file = files[0];
                this.error = null;
            }
        },

        clearFile() {
            this.file = null;
            this.$refs.fileInput.value = '';
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

                // Check if response is HTML error page
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(`Server error (${response.status}): Response bukan JSON`);
                }

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal scan OCR');
                }

                this.ocrResults = {
                    nama: data.nama || '',
                    major: data.major || '',
                    type: data.type || this.type,
                    raw_text: data.extracted_text || '',
                    filepath: data.file_path || ''
                };
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
