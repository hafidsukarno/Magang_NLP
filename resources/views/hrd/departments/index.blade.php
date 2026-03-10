 @section('title', 'Departements')
<x-app-layout>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    <div class="p-6 relative">

        <!-- HEADER + SEARCH -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                Manajemen Departemen
            </h2>

            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto items-start sm:items-center">
                <form method="GET" action="{{ route('departments.index') }}"
                    class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari Nama Departemen..."
                        class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition w-full sm:w-auto">
                        Cari
                    </button>
                    <!-- Tombol Reset -->
                    <a href="{{ route('departments.index') }}"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition w-full sm:w-auto text-center">
                        Reset
                    </a>
                </form>

                <!-- Tombol Tambah (Desktop) -->
                <button onclick="openAddModal()"
                    class="hidden sm:flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                    <i data-lucide="plus"></i>
                    Tambah
                </button>
            </div>
        </div>

        <!-- Floating Button (Mobile) -->
        <button onclick="openAddModal()"
            class="sm:hidden fixed bottom-6 right-6 bg-blue-600 text-white p-4 rounded-full shadow-xl hover:bg-blue-700 transition z-50">
            <i data-lucide="plus"></i>
        </button>


        <!-- CARD WRAPPER -->
        <div class="bg-white shadow rounded-lg overflow-x-auto border border-gray-100">

            <table class="min-w-full text-left border-collapse">
                <thead class="bg-gray-100 border-b">
                    <tr class="text-sm">
                        <th class="p-3 text-gray-700 font-semibold whitespace-nowrap">Departemen</th>
                        <th class="p-3 text-gray-700 font-semibold whitespace-nowrap text-center">Kuota</th>
                        <th class="p-3 text-gray-700 font-semibold whitespace-nowrap">Periode (Bulan)</th>
                        <th class="p-3 text-gray-700 font-semibold whitespace-nowrap">Jurusan Relevan</th>
                        <th class="p-3 text-gray-700 font-semibold whitespace-nowrap">Keahlian</th>
                        <th class="p-3 text-gray-700 font-semibold whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-sm">
                    @foreach ($departments as $d)
                        <tr class="border-b hover:bg-gray-50 transition">

                            <!-- NAMA -->
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="building-2" class="w-4 h-4 text-gray-600"></i>
                                    <span class="font-medium">{{ $d->name }}</span>
                                </div>
                            </td>

                            <!-- QUOTA (legacy) -->
                            <td class="p-3 text-center">
                                <div class="inline-flex items-center gap-2 justify-center">
                                    {{ $d->quota ?? 0 }}
                                </div>
                            </td>

                            <!-- PERIODE MAGANG -->
                            <td class="p-3">
                                @if($d->periods->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($d->periods as $period)
                                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">
                                                {{ $period->weeks }} bln
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>

                            <!-- JURUSAN RELEVAN -->
                            <td class="p-3">
                                @if($d->majors->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($d->majors as $major)
                                            <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                                {{ $major->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>

                            <!-- KEAHLIAN -->
                            <td class="p-3">
                                @if($d->skills->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($d->skills as $skill)
                                            <span class="inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">
                                                {{ $skill->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>

                            <!-- AKSI -->
                            <td class="p-3 text-center flex gap-3 justify-center items-center">

                                <!-- EDIT (modal) -->
                                <a href="javascript:void(0)"
                                    onclick="openEditModal({{ $d->id }}, '{{ addslashes($d->name) }}', {{ $d->quota ?? 0 }}, {{ json_encode($d->periods->pluck('weeks')->toArray()) }}, {{ json_encode($d->majors->pluck('name')->toArray()) }}, {{ json_encode($d->skills->pluck('name')->toArray()) }})"
                                    class="text-yellow-600 hover:text-yellow-800 transition" title="Edit Departemen">
                                    <i data-lucide="edit-2" class="w-5 h-5"></i>
                                </a>

                                <!-- SEE -->
                                <a href="{{ route('departments.accepted', $d->id) }}"
                                    class="text-green-600 hover:text-green-800 transition"
                                    title="Lihat mahasiswa diterima">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </a>

                                <!-- SETTINGS -->
                                <a href="{{ route('prodi-maps.index', $d->id) }}"
                                    class="text-blue-600 hover:text-blue-800 transition" title="Pengaturan Prodi">
                                    <i data-lucide="settings" class="w-5 h-5"></i>
                                </a>

                                <!-- DELETE -->
                                <form id="delete-form-{{ $d->id }}" action="{{ route('departments.destroy', $d->id) }}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <a href="javascript:void(0)" onclick="confirmDelete({{ $d->id }}, '{{ $d->name }}')"
                                    class="text-red-600 hover:text-red-800 transition" title="Hapus Departemen">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </a>

                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>

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

            <form method="POST" action="{{ route('departments.store') }}">>
                @csrf

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

                <!-- PERIODE MAGANG -->
                <div class="mb-4 p-3 bg-blue-50 rounded border border-blue-200">
                    <label class="block text-gray-700 font-medium mb-2">Waktu Periode Magang (Bulan)</label>
                    <input type="number" name="periods[]" min="1" placeholder="Contoh: 3, 4, 5, atau 6 bulan"
                        class="w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200">
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
                        <input type="text" name="majors[]" placeholder="Contoh: Sistem Informasi"
                            class="w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-green-200">
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
                        <input type="text" name="skills[]" placeholder="Contoh: PHP, Laravel"
                            class="w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-purple-200">
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

                <!-- PERIODE MAGANG -->
                <div class="mb-4 p-3 bg-blue-50 rounded border border-blue-200">
                    <label class="block text-gray-700 font-medium mb-2">Waktu Periode Magang (Bulan)</label>
                    <div id="periodsContainerEdit">
                    </div>
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

        // Close modal when clicking outside
        document.getElementById('addModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddModal();
            }
        });

        function addMajorField() {
            const container = document.getElementById('majorsContainer');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'majors[]';
            input.placeholder = 'Contoh: Sistem Informasi';
            input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-green-200';
            container.appendChild(input);
            lucide.createIcons();
        }

        function addSkillField() {
            const container = document.getElementById('skillsContainer');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'skills[]';
            input.placeholder = 'Contoh: PHP, Laravel';
            input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-purple-200';
            container.appendChild(input);
            lucide.createIcons();
        }

        function openEditModal(id, name, quota, periods, majors, skills) {
            document.getElementById('editModal').classList.remove('hidden');

            document.getElementById('editName').value = name;
            document.getElementById('editQuota').value = quota;

            // keep route consistent with controller (PATCH to /hrd/departments/{department}/update)
            document.getElementById('editForm').action =
                "{{ route('departments.update', ':id') }}".replace(':id', id);

            // Load existing data directly (no need for API fetch)
            populateEditFormData(periods, majors, skills);
        }

        function populateEditFormData(periods, majors, skills) {
            // Populate periods
            const periodsContainer = document.getElementById('periodsContainerEdit');
            periodsContainer.innerHTML = '';
            
            if (periods && periods.length > 0) {
                periods.forEach(weeks => {
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.name = 'periods[]';
                    input.value = weeks;
                    input.min = '1';
                    input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-blue-200';
                    periodsContainer.appendChild(input);
                });
            } else {
                const input = document.createElement('input');
                input.type = 'number';
                input.name = 'periods[]';
                input.min = '1';
                input.placeholder = 'Contoh: 3, 4, 5, 6 bulan';
                input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-blue-200';
                periodsContainer.appendChild(input);
            }

            // Populate majors
            const majorsContainer = document.getElementById('majorsContainerEdit');
            majorsContainer.innerHTML = '';
            
            if (majors && majors.length > 0) {
                majors.forEach(majorName => {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'majors[]';
                    input.value = majorName;
                    input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-green-200';
                    majorsContainer.appendChild(input);
                });
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'majors[]';
                input.placeholder = 'Contoh: Sistem Informasi';
                input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-green-200';
                majorsContainer.appendChild(input);
            }

            // Populate skills
            const skillsContainer = document.getElementById('skillsContainerEdit');
            skillsContainer.innerHTML = '';
            
            if (skills && skills.length > 0) {
                skills.forEach(skillName => {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'skills[]';
                    input.value = skillName;
                    input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-purple-200';
                    skillsContainer.appendChild(input);
                });
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'skills[]';
                input.placeholder = 'Contoh: PHP, Laravel';
                input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-purple-200';
                skillsContainer.appendChild(input);
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

        function addPeriodFieldEdit() {
            const container = document.getElementById('periodsContainerEdit');
            const input = document.createElement('input');
            input.type = 'number';
            input.name = 'periods[]';
            input.min = '1';
            input.placeholder = 'Contoh: 3, 4, 5, 6 bulan';
            input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-blue-200';
            container.appendChild(input);
            lucide.createIcons();
        }

        function addMajorFieldEdit() {
            const container = document.getElementById('majorsContainerEdit');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'majors[]';
            input.placeholder = 'Contoh: Sistem Informasi';
            input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-green-200';
            container.appendChild(input);
            lucide.createIcons();
        }

        function addSkillFieldEdit() {
            const container = document.getElementById('skillsContainerEdit');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'skills[]';
            input.placeholder = 'Contoh: PHP, Laravel';
            input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-purple-200';
            container.appendChild(input);
            lucide.createIcons();
        }

        // Load existing department data (periods, majors, skills)
        function loadDepartmentData(departmentId) {
            console.log('Loading department data for ID:', departmentId);
            
            fetch(`/api/departments/${departmentId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Department data received:', data);
                
                // Load periods
                const periodsContainer = document.getElementById('periodsContainerEdit');
                periodsContainer.innerHTML = '';
                if (data.periods && data.periods.length > 0) {
                    data.periods.forEach(period => {
                        const input = document.createElement('input');
                        input.type = 'number';
                        input.name = 'periods[]';
                        input.value = period.weeks;
                        input.min = '1';
                        input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-blue-200';
                        periodsContainer.appendChild(input);
                    });
                } else {
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.name = 'periods[]';
                    input.min = '1';
                    input.placeholder = 'Contoh: 3, 4, 5, 6 bulan';
                    input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-blue-200';
                    periodsContainer.appendChild(input);
                }

                // Load majors
                const majorsContainer = document.getElementById('majorsContainerEdit');
                majorsContainer.innerHTML = '';
                if (data.majors && data.majors.length > 0) {
                    data.majors.forEach(major => {
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = 'majors[]';
                        input.value = major.name;
                        input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-green-200';
                        majorsContainer.appendChild(input);
                    });
                } else {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'majors[]';
                    input.placeholder = 'Contoh: Sistem Informasi';
                    input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-green-200';
                    majorsContainer.appendChild(input);
                }

                // Load skills
                const skillsContainer = document.getElementById('skillsContainerEdit');
                skillsContainer.innerHTML = '';
                if (data.skills && data.skills.length > 0) {
                    data.skills.forEach(skill => {
                        const input = document.createElement('input');
                        input.type = 'text';
                        input.name = 'skills[]';
                        input.value = skill.name;
                        input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-purple-200';
                        skillsContainer.appendChild(input);
                    });
                } else {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'skills[]';
                    input.placeholder = 'Contoh: PHP, Laravel';
                    input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-purple-200';
                    skillsContainer.appendChild(input);
                }

                lucide.createIcons();
            })
            .catch(error => {
                console.error('Error loading department data:', error);
                // Fallback: add empty inputs
                const periodsContainer = document.getElementById('periodsContainerEdit');
                periodsContainer.innerHTML = '';
                let input = document.createElement('input');
                input.type = 'number';
                input.name = 'periods[]';
                input.min = '1';
                input.placeholder = 'Contoh: 3, 4, 5, 6 bulan';
                input.className = 'w-full border rounded px-3 py-2 mb-2 focus:ring focus:ring-blue-200';
                periodsContainer.appendChild(input);
                
                addMajorFieldEdit();
                addSkillFieldEdit();
                lucide.createIcons();
            });
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
