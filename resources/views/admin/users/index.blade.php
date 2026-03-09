@section('title', 'Manage User')
<x-app-layout>

<div class="p-6">
    <h1 class="text-3xl font-bold mb-4">Manajemen User HRD</h1>

    <!-- TOMBOL TAMBAH -->
    <a href="{{ route('admin.users.create') }}"
       class="px-4 py-2 bg-blue-600 text-white rounded mb-4 inline-block">
        Tambah User HRD
    </a>

    <!-- TABLE -->
    <table class="w-full border mt-4 text-center">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Nama</th>
                <th class="border p-2">Email</th>
                <th class="border p-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                <tr>
                    <td class="border p-2">{{ $u->name }}</td>
                    <td class="border p-2">{{ $u->email }}</td>

                    <!-- AKSI -->
                    <td class="border p-2">
                        <div class="flex gap-2 justify-center">

                            <!-- EDIT -->
                            <button type="button"
                                onclick="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}')"
                                class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                Edit
                            </button>

                            <!-- DELETE -->
                            <form action="{{ route('admin.users.destroy', $u->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                    Hapus
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- ================= MODAL EDIT USER ================= -->
<div id="editModal"
     class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6">

        <h1 class="text-2xl font-bold mb-4">Edit User HRD</h1>

        <form method="POST" id="editForm" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block font-semibold">Nama</label>
                <input type="text" name="name" id="editName"
                       class="border p-2 w-full rounded-lg" required>
            </div>

            <div>
                <label class="block font-semibold">Email</label>
                <input type="email" name="email" id="editEmail"
                       class="border p-2 w-full rounded-lg" required>
            </div>

            <div>
                <label class="block font-semibold">
                    Password
                    <span class="text-sm text-gray-500">(kosongkan jika tidak diubah)</span>
                </label>
                <input type="password" name="password"
                       class="border p-2 w-full rounded-lg">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button"
                        onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Batal
                </button>

                <button class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
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
</script>

</x-app-layout>
