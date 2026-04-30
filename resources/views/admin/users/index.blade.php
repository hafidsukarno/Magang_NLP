@section('title', 'Manajemen User')
<x-app-layout>

<script src="https://unpkg.com/lucide@latest"></script>

<div class="p-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
            <i data-lucide="users" class="w-8 h-8 text-blue-600"></i>
            Manajemen User
        </h1>
    </div>

    {{-- FLASH --}}
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-lg flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- TABS --}}
    <div class="flex gap-1 mb-0 border-b border-gray-200">
        <button id="tabHrd"
            onclick="switchTab('hrd')"
            class="tab-btn px-6 py-3 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 bg-white text-blue-700 flex items-center gap-2 active-tab">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            HRD
            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $hrdUsers->count() }}</span>
        </button>
        <button id="tabMahasiswa"
            onclick="switchTab('mahasiswa')"
            class="tab-btn px-6 py-3 text-sm font-semibold rounded-t-lg border border-b-0 border-gray-200 bg-gray-50 text-gray-500 flex items-center gap-2">
            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
            Mahasiswa
            <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ $mahasiswaUsers->count() }}</span>
        </button>
    </div>

    {{-- PANEL HRD --}}
    <div id="panelHrd" class="bg-white border border-gray-200 rounded-b-lg rounded-tr-lg shadow-sm overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
            <p class="text-sm text-gray-600">Kelola akun user HRD. Admin dapat menambah, mengedit, dan menghapus akun HRD.</p>
            <a href="{{ route('admin.users.create') }}"
               class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold shadow">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah HRD
            </a>
        </div>

        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($hrdUsers as $u)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                {{ $u->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $u->email }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <button type="button"
                                    onclick="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')"
                                    class="flex items-center gap-1 px-3 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-xs font-semibold">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                                </button>
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-xs font-semibold">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                            <i data-lucide="user-x" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Belum ada user HRD.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PANEL MAHASISWA --}}
    <div id="panelMahasiswa" class="hidden bg-white border border-gray-200 rounded-b-lg rounded-tr-lg shadow-sm overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
            <p class="text-sm text-gray-600">Kelola akun mahasiswa. Admin dapat melihat, mengedit, dan menghapus akun mahasiswa.</p>
            <span class="flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-500 rounded-lg text-sm font-semibold cursor-not-allowed" title="Mahasiswa mendaftar sendiri">
                <i data-lucide="lock" class="w-4 h-4"></i>
                Tidak bisa tambah
            </span>
        </div>

        {{-- SEARCH --}}
        <div class="px-6 py-3 border-b border-gray-100">
            <input type="text" id="mahasiswaSearch" oninput="filterMahasiswa(this.value)"
                placeholder="Cari nama atau email mahasiswa..."
                class="w-full sm:w-80 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
        </div>

        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="mahasiswaBody" class="divide-y divide-gray-100">
                @forelse($mahasiswaUsers as $u)
                    <tr class="mahasiswa-row hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-sm">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <span class="mahasiswa-name">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 mahasiswa-email">{{ $u->email }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex gap-2 justify-center">
                                <button type="button"
                                    onclick="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')"
                                    class="flex items-center gap-1 px-3 py-1.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-xs font-semibold">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                                </button>
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus akun mahasiswa {{ $u->name }}? Semua pengajuan terkait juga akan terpengaruh.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition text-xs font-semibold">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-400">
                            <i data-lucide="user-x" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Belum ada mahasiswa terdaftar.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ============== MODAL EDIT ============== --}}
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl p-6">

        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="edit" class="w-5 h-5 text-yellow-600"></i>
                Edit User
            </h2>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" id="editForm" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" name="name" id="editName"
                       class="border border-gray-300 px-3 py-2 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-300" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" id="editEmail"
                       class="border border-gray-300 px-3 py-2 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-300" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Password
                    <span class="text-xs text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>
                </label>
                <input type="password" name="password"
                       class="border border-gray-300 px-3 py-2 w-full rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-300">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeEditModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-sm font-semibold">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    // Tab switching
    function switchTab(tab) {
        const hrdPanel  = document.getElementById('panelHrd');
        const mahPanel  = document.getElementById('panelMahasiswa');
        const tabHrd    = document.getElementById('tabHrd');
        const tabMah    = document.getElementById('tabMahasiswa');

        if (tab === 'hrd') {
            hrdPanel.classList.remove('hidden');
            mahPanel.classList.add('hidden');
            tabHrd.classList.add('text-blue-700', 'bg-white');
            tabHrd.classList.remove('text-gray-500', 'bg-gray-50');
            tabMah.classList.add('text-gray-500', 'bg-gray-50');
            tabMah.classList.remove('text-blue-700', 'bg-white');
        } else {
            mahPanel.classList.remove('hidden');
            hrdPanel.classList.add('hidden');
            tabMah.classList.add('text-blue-700', 'bg-white');
            tabMah.classList.remove('text-gray-500', 'bg-gray-50');
            tabHrd.classList.add('text-gray-500', 'bg-gray-50');
            tabHrd.classList.remove('text-blue-700', 'bg-white');
        }
        lucide.createIcons();
    }

    // Filter mahasiswa search
    function filterMahasiswa(query) {
        const rows = document.querySelectorAll('.mahasiswa-row');
        const q = query.toLowerCase();
        rows.forEach(row => {
            const name  = row.querySelector('.mahasiswa-name')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.mahasiswa-email')?.textContent.toLowerCase() || '';
            row.style.display = (name.includes(q) || email.includes(q)) ? '' : 'none';
        });
    }

    // Modal edit
    function openEditModal(id, name, email) {
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editName').value  = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editForm').action =
            "{{ route('admin.users.update', ':id') }}".replace(':id', id);
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('editModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>

</x-app-layout>
