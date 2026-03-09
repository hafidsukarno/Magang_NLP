@section('title', 'Pengumuman')
<x-layouts.guest-wide>

    <div class="min-h-screen bg-gray-100 py-10 px-4">

        <div class="max-w-3xl mx-auto bg-white shadow-2xl rounded-2xl p-8">

            <!-- Header -->
            <div class="mb-6 pb-4 border-b border-gray-300">
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i data-lucide="megaphone" class="w-8 h-8 text-gray-600 hidden md:inline-block"></i>
                    Pengumuman Penerimaan Magang
                </h2>
                <p class="text-gray-500 mt-2">Masukkan kode pendaftaran Anda untuk melihat hasil seleksi.</p>
            </div>

            <!-- Cari dengan kode -->
            <form method="GET" class="mb-6">
                <div
                    class="bg-gray-100 border border-gray-300 shadow-sm flex items-center gap-3 px-4 py-3 rounded-xl shadow-inner">
                    <i data-lucide="search" class="text-gray-500 w-6 h-6"></i>
                    <input type="text" name="code" value="{{ request('code') }}"
                        placeholder="Masukkan kode pendaftaran"
                        class="w-full bg-transparent focus:outline-none text-gray-700">
                </div>
                <button class="w-full mt-3 bg-blue-600 text-white py-3 rounded-xl shadow hover:bg-blue-700">
                    Cek Pengumuman
                </button>
            </form>

            <!-- Hasil -->
            @if (request('code') && !$data)
                <div class="p-6 bg-yellow-50 border border-yellow-300 rounded-lg text-center">
                    <p class="text-lg font-semibold text-yellow-700">
                        Kode "<span class="font-bold">{{ request('code') }}</span>" tidak ditemukan.
                    </p>
                </div>
            @elseif($data)
                <div class="border rounded-xl p-5 shadow bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">{{ $data->leader_name }}</h3>
                            <p class="text-gray-600">{{ $data->university }} — {{ $data->major }}</p>
                        </div>
                        <div>
                            @if ($data->status === 'diterima')
                                <span class="px-5 py-2 rounded-lg bg-green-100 text-green-700 font-bold">
                                    DITERIMA
                                </span>
                            @elseif($data->status === 'ditolak')
                                <span class="px-5 py-2 rounded-lg bg-red-100 text-red-700 font-bold">
                                    DITOLAK
                                </span>
                            @else
                                <span class="px-5 py-2 rounded-lg bg-yellow-100 text-yellow-700 font-bold">
                                    MENUNGGU
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- DETAIL -->
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-700">
                        <p><strong>Departemen:</strong> {{ $data->department->name ?? '-' }}</p>
                        <p><strong>Durasi:</strong> {{ $data->duration }} Bulan</p>
                        <p><strong>Diperbarui:</strong> {{ $data->updated_at->format('d M Y') }}</p>
                    </div>

                    <!-- HRD Note (show if rejected) -->
                   @if($data->hrd_note)
    <div class="mt-4 p-4 border rounded-lg text-sm
        {{ $data->status === 'diterima' ? 'bg-green-50 border-green-200 text-green-700' : '' }}
        {{ $data->status === 'ditolak' ? 'bg-red-50 border-red-200 text-red-700' : '' }}
    ">
        <strong>Catatan: </strong>
        <p class="mt-2">{{ $data->hrd_note }}</p>
    </div>
@endif


                </div>
            @endif

            <div class="mt-8 text-left">
                <a href="{{ route('apply.form') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl shadow hover:bg-blue-700 hover:text-white">
                    <i data-lucide="arrow-left"></i>
                    Kembali ke Form Pendaftaran
                </a>
            </div>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</x-layouts.guest-wide>
