@section('title', 'Manage User')
<x-app-layout>
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Tambah User HRD</h1>

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block font-semibold">Nama</label>
            <input type="text" name="name" class="border p-2 w-full rounded-lg" required>
        </div>

        <div>
            <label class="block font-semibold">Email</label>
            <input type="email" name="email" class="border p-2 w-full rounded-lg" required>
        </div>

        <div>
            <label class="block font-semibold">Password</label>
            <input type="password" name="password" class="border p-2 w-full rounded-lg" required>
        </div>

        <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    </form>
</div>
</x-app-layout>
