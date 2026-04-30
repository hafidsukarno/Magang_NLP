 @section('title', 'Departements')
<x-app-layout>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    <div class="p-6 relative">

        <!-- HEADER + ACTIONS -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                Manajemen Departemen
            </h2>

            <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                <form method="GET" action="{{ route('departments.index') }}"
                    class="flex flex-1 sm:flex-none gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari Departemen, Jurusan, Skill..."
                        class="w-full sm:w-64 px-4 py-2 bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none transition-all text-sm">
                    <button type="submit"
                        class="px-4 py-2 bg-gray-800 text-white font-semibold rounded-lg hover:bg-black transition text-sm">
                        Cari
                    </button>
                </form>

                <button onclick="openAddModal()"
                    class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition text-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Tambah
                </button>
            </div>
        </div>

        <!-- Floating Button (Mobile) -->
        <button onclick="openAddModal()"
            class="sm:hidden fixed bottom-8 right-8 bg-blue-600 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 transition z-50">
            <i data-lucide="plus" class="w-6 h-6"></i>
        </button>


        <!-- DATA TABLE -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-widest border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Departemen</th>
                            <th class="px-6 py-4 text-center">Kuota</th>
                            <th class="px-6 py-4">Jurusan</th>
                            <th class="px-6 py-4">Keahlian</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm divide-y divide-gray-50">
                        @foreach ($departments as $d)
                            <tr class="hover:bg-gray-50 transition-colors">

                                <!-- DEPT NAME -->
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-gray-900">{{ $d->name }}</span>
                                </td>

                                <!-- QUOTA -->
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-bold">
                                        {{ $d->quota ?? 0 }}
                                    </span>
                                </td>

                                <!-- MAJORS -->
                                <td class="px-6 py-4">
                                    @if($d->majors->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5 max-w-xs">
                                            @foreach($d->majors as $major)
                                                <span class="text-[11px] text-gray-600 bg-gray-50 px-2 py-0.5 rounded border border-gray-100">
                                                    {{ $major->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <!-- SKILLS -->
                                <td class="px-6 py-4">
                                    @if($d->skills->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5 max-w-xs">
                                            @foreach($d->skills as $skill)
                                                <span class="text-[11px] text-gray-600 bg-gray-50 px-2 py-0.5 rounded border border-gray-100">
                                                    {{ $skill->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <!-- ACTIONS -->
                                <td class="px-6 py-4">
                                    <div class="flex justify-center items-center gap-4 text-gray-400">
                                        <!-- VIEW ACCEPTED -->
                                        <a href="{{ route('departments.accepted', $d->id) }}"
                                            class="hover:text-green-600 transition"
                                            title="Mahasiswa Diterima">
                                            <i data-lucide="users" class="w-4 h-4"></i>
                                        </a>

                                        <!-- EDIT -->
                                        <button type="button"
                                            onclick="openEditModal({{ $d->id }}, '{{ addslashes($d->name) }}', {{ $d->quota ?? 0 }}, {{ json_encode($d->majors->pluck('name')->toArray()) }}, {{ json_encode($d->skills->pluck('name')->toArray()) }})"
                                            class="hover:text-blue-600 transition" 
                                            title="Edit">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <!-- DELETE -->
                                        <form id="delete-form-{{ $d->id }}" action="{{ route('departments.destroy', $d->id) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button type="button" onclick="confirmDelete({{ $d->id }}, '{{ $d->name }}')"
                                            class="hover:text-red-600 transition" 
                                            title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH -->
    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="pointer-events: auto;">
        <div class="bg-white w-full max-w-5xl rounded-lg shadow-2xl p-8 animate-fade max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-7 h-7 text-blue-600"></i>
                    Tambah Departemen
                </h3>
                <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('departments.store') }}" id="addForm">
                @csrf

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded">
                        <p class="text-red-700 font-semibold text-sm mb-2">❌ Gagal menambah departemen:</p>
                        <ul class="text-red-600 text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Nama Departemen</label>
                    <input type="text" name="name"
                        class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200" required autofocus>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Kuota</label>
                    <input type="number" name="quota" min="0"
                        class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200" required>
                </div>



                <!-- JURUSAN YANG RELEVAN -->
                <div class="mb-4 p-3 bg-green-50 rounded border border-green-200">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-gray-700 font-medium">Jurusan yang Relevan</label>
                        <button type="button" onclick="addMajorField()" class="text-green-600 hover:text-green-700 text-sm font-semibold flex items-center gap-1">
                            <i data-lucide="plus" class="w-4 h-4"></i> Tambah
                        </button>
                    </div>
                    <div id="majorsContainer">
                        <div class="flex gap-2 mb-2">
                            <input type="text" name="majors[]" placeholder="Contoh: Sistem Informasi"
                                class="flex-1 border rounded px-3 py-2 focus:ring focus:ring-green-200">
                            <button type="button" class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition font-semibold" onclick="this.parentElement.remove()">✕</button>
                        </div>
                    </div>
                </div>

                <!-- KEAHLIAN -->
                <div class="mb-4 p-3 bg-purple-50 rounded border border-purple-200">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-gray-700 font-medium">Keahlian</label>
                        <button type="button" onclick="addSkillField()" class="text-purple-600 hover:text-purple-700 text-sm font-semibold flex items-center gap-1">
                            <i data-lucide="plus" class="w-4 h-4"></i> Tambah
                        </button>
                    </div>
                    <div id="skillsContainer">
                        <div class="flex gap-2 mb-2">
                            <input type="text" name="skills[]" placeholder="Contoh: PHP, Laravel"
                                class="flex-1 border rounded px-3 py-2 focus:ring focus:ring-purple-200">
                            <button type="button" class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition font-semibold" onclick="this.parentElement.remove()">✕</button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT  -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="pointer-events: auto;">

        <div class="bg-white w-full max-w-5xl rounded-lg shadow-2xl p-8 animate-fade max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold flex items-center gap-2">
                    <i data-lucide="pencil" class="w-7 h-7 text-yellow-600"></i>
                    Edit Departemen
                </h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form method="POST" id="editForm">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Nama Departemen</label>
                    <input type="text" name="name" id="editName"
                        class="w-full border rounded px-3 py-2 focus:ring focus:ring-yellow-200" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Kuota</label>
                    <input type="number" name="quota" id="editQuota" min="0"
                        class="w-full border rounded px-3 py-2 focus:ring focus:ring-yellow-200" required>
                </div>



                <!-- JURUSAN YANG RELEVAN -->
                <div class="mb-4 p-3 bg-green-50 rounded border border-green-200">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-gray-700 font-medium">Jurusan yang Relevan</label>
                        <button type="button" onclick="addMajorFieldEdit()" class="text-green-600 hover:text-green-700 text-sm font-semibold flex items-center gap-1">
                            <i data-lucide="plus" class="w-4 h-4"></i> Tambah
                        </button>
                    </div>
                    <div id="majorsContainerEdit">
                    </div>
                </div>

                <!-- KEAHLIAN -->
                <div class="mb-4 p-3 bg-purple-50 rounded border border-purple-200">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-gray-700 font-medium">Keahlian</label>
                        <button type="button" onclick="addSkillFieldEdit()" class="text-purple-600 hover:text-purple-700 text-sm font-semibold flex items-center gap-1">
                            <i data-lucide="plus" class="w-4 h-4"></i> Tambah
                        </button>
                    </div>
                    <div id="skillsContainerEdit">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition">
                        Update
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- SCRIPT -->
    <script>
        lucide.createIcons();



        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            @if($errors->any())
                openAddModal();
            @endif
        });

        // Close modal when clicking outside
        document.getElementById('addModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddModal();
            }
        });

        function addMajorField() {
            const container = document.getElementById('majorsContainer');
            
            const wrapper = document.createElement('div');
            wrapper.className = 'flex gap-2 mb-2';
            
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'majors[]';
            input.placeholder = 'Contoh: Sistem Informasi';
            input.className = 'flex-1 border rounded px-3 py-2 focus:ring focus:ring-green-200';
            wrapper.appendChild(input);
            
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition font-semibold';
            deleteBtn.innerHTML = '✕';
            deleteBtn.onclick = () => wrapper.remove();
            wrapper.appendChild(deleteBtn);
            
            container.appendChild(wrapper);
            lucide.createIcons();
        }

        function addSkillField() {
            const container = document.getElementById('skillsContainer');
            
            const wrapper = document.createElement('div');
            wrapper.className = 'flex gap-2 mb-2';
            
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'skills[]';
            input.placeholder = 'Contoh: PHP, Laravel';
            input.className = 'flex-1 border rounded px-3 py-2 focus:ring focus:ring-purple-200';
            wrapper.appendChild(input);
            
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition font-semibold';
            deleteBtn.innerHTML = '✕';
            deleteBtn.onclick = () => wrapper.remove();
            wrapper.appendChild(deleteBtn);
            
            container.appendChild(wrapper);
            lucide.createIcons();
        }

        function openEditModal(id, name, quota, majors, skills) {
            document.getElementById('editModal').classList.remove('hidden');

            document.getElementById('editName').value = name;
            document.getElementById('editQuota').value = quota;

            // keep route consistent with controller (PATCH to /hrd/departments/{department}/update)
            document.getElementById('editForm').action =
                "{{ route('departments.update', ':id') }}".replace(':id', id);

            // Load existing data directly (no need for API fetch)
            populateEditFormData(majors, skills);
        }

        function populateEditFormData(majors, skills) {

            // Populate majors
            const majorsContainer = document.getElementById('majorsContainerEdit');
            majorsContainer.innerHTML = '';
            
            if (majors && majors.length > 0) {
                majors.forEach((majorName, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex gap-2 mb-2';
                    
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'majors[]';
                    input.value = majorName;
                    input.className = 'flex-1 border rounded px-3 py-2 focus:ring focus:ring-green-200';
                    wrapper.appendChild(input);
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600';
                    deleteBtn.innerHTML = '✕';
                    deleteBtn.onclick = () => wrapper.remove();
                    wrapper.appendChild(deleteBtn);
                    
                    majorsContainer.appendChild(wrapper);
                });
            } else {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex gap-2 mb-2';
                
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'majors[]';
                input.placeholder = 'Contoh: Sistem Informasi';
                input.className = 'flex-1 border rounded px-3 py-2 focus:ring focus:ring-green-200';
                wrapper.appendChild(input);
                
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600';
                deleteBtn.innerHTML = '✕';
                deleteBtn.onclick = () => wrapper.remove();
                wrapper.appendChild(deleteBtn);
                
                majorsContainer.appendChild(wrapper);
            }

            // Populate skills
            const skillsContainer = document.getElementById('skillsContainerEdit');
            skillsContainer.innerHTML = '';
            
            if (skills && skills.length > 0) {
                skills.forEach((skillName, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex gap-2 mb-2';
                    
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'skills[]';
                    input.value = skillName;
                    input.className = 'flex-1 border rounded px-3 py-2 focus:ring focus:ring-purple-200';
                    wrapper.appendChild(input);
                    
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600';
                    deleteBtn.innerHTML = '✕';
                    deleteBtn.onclick = () => wrapper.remove();
                    wrapper.appendChild(deleteBtn);
                    
                    skillsContainer.appendChild(wrapper);
                });
            } else {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex gap-2 mb-2';
                
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'skills[]';
                input.placeholder = 'Contoh: PHP, Laravel';
                input.className = 'flex-1 border rounded px-3 py-2 focus:ring focus:ring-purple-200';
                wrapper.appendChild(input);
                
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600';
                deleteBtn.innerHTML = '✕';
                deleteBtn.onclick = () => wrapper.remove();
                wrapper.appendChild(deleteBtn);
                
                skillsContainer.appendChild(wrapper);
            }

            lucide.createIcons();
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('editModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });



        function addMajorFieldEdit() {
            const container = document.getElementById('majorsContainerEdit');
            
            const wrapper = document.createElement('div');
            wrapper.className = 'flex gap-2 mb-2';
            
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'majors[]';
            input.placeholder = 'Contoh: Sistem Informasi';
            input.className = 'flex-1 border rounded px-3 py-2 focus:ring focus:ring-green-200';
            wrapper.appendChild(input);
            
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600';
            deleteBtn.innerHTML = '✕';
            deleteBtn.onclick = () => wrapper.remove();
            wrapper.appendChild(deleteBtn);
            
            container.appendChild(wrapper);
            lucide.createIcons();
        }

        function addSkillFieldEdit() {
            const container = document.getElementById('skillsContainerEdit');
            
            const wrapper = document.createElement('div');
            wrapper.className = 'flex gap-2 mb-2';
            
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'skills[]';
            input.placeholder = 'Contoh: PHP, Laravel';
            input.className = 'flex-1 border rounded px-3 py-2 focus:ring focus:ring-purple-200';
            wrapper.appendChild(input);
            
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600';
            deleteBtn.innerHTML = '✕';
            deleteBtn.onclick = () => wrapper.remove();
            wrapper.appendChild(deleteBtn);
            
            container.appendChild(wrapper);
            lucide.createIcons();
        }



        function confirmDelete(departmentId, departmentName) {
            Swal.fire({
                title: 'Hapus Departemen?',
                html: `Are you sure you want to delete <strong>${departmentName}</strong>? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${departmentId}`).submit();
                }
            });
        }
    </script>

</x-app-layout>
