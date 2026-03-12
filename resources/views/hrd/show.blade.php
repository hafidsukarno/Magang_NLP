@section('title', 'Detail Pengajuan')
@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: `{!! addslashes($errors->first()) !!}`,
                confirmButtonColor: '#ef4444'
            });
        });
    </script>
@endif
<x-app-layout>
    <div class="container mx-auto px-6 py-8">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Detail Pengajuan</h1>
            <p class="text-gray-500 mt-1">Informasi lengkap tentang pengajuan ini</p>
        </div>


        <!-- Card Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <!-- Departemen & Status -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Informasi Umum</h2>
                <p><strong>Nama Departemen:</strong> {{ $app->department->name ?? '-' }}</p>
                <p><strong>Status:</strong>
                    <span
                        class="@if ($app->status == 'diterima') text-green-600
                                 @elseif($app->status == 'ditolak') text-red-600
                                 @elseif($app->status == 'selesai') text-blue-600
                                 @else text-yellow-600 @endif font-semibold">
                        {{ ucfirst($app->status) }}
                    </span>
                </p>
                <p><strong>Durasi Maksimal:</strong> {{ $app->duration }} Bulan</p>
                <p><strong>Periode Magang:</strong>
                    @if ($app->period_start && $app->period_end)
                        {{ \Carbon\Carbon::parse($app->period_start)->format('d M Y') }} —
                        {{ \Carbon\Carbon::parse($app->period_end)->format('d M Y') }}
                    @else
                        -
                    @endif
                </p>
                <p><strong>Durasi Pengajuan:</strong>
                    @if ($app->period_start && $app->period_end)
                        @php
                            $startDate = \Carbon\Carbon::parse($app->period_start);
                            $endDate = \Carbon\Carbon::parse($app->period_end);
                            $months = $endDate->diff($startDate)->m;
                            $years = $endDate->diff($startDate)->y;
                            $days = $endDate->diff($startDate)->d;
                            $totalMonths = ($years * 12) + $months;
                        @endphp
                        <span class="text-blue-600 font-semibold">{{ $totalMonths }} Bulan {{ $days }} Hari</span>
                    @else
                        -
                    @endif
                </p>
            </div>

            <!-- Members & Leader Table -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Member(s)</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 text-center rounded">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-4 py-2">Role</th>
                                <th class="border px-4 py-2">Nama</th>
                                <th class="border px-4 py-2">Email</th>
                                <th class="border px-4 py-2">Telepon</th>
                                <th class="border px-4 py-2">Status</th>
                                <th class="border px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($app->type === 'individual')
                                <!-- Individual: Tampilkan peserta dengan status dan aksi -->
                                <tr class="bg-gray-50">
                                    <td class="border px-4 py-2 font-medium">Peserta</td>
                                    <td class="border px-4 py-2">{{ $app->leader_name }}</td>
                                    <td class="border px-4 py-2">{{ $app->leader_email ?? '-' }}</td>
                                    <td class="border px-4 py-2">{{ $app->leader_phone ?? '-' }}</td>
                                    <td class="border px-4 py-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                            @if ($app->leader_status == 'menunggu') bg-yellow-100 text-yellow-700
                                            @elseif($app->leader_status == 'diterima') bg-green-100 text-green-700
                                            @elseif($app->leader_status == 'ditolak') bg-red-100 text-red-700
                                            @else bg-gray-100 text-gray-700 @endif">
                                            {{ ucfirst($app->leader_status) }}
                                        </span>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <div class="flex gap-1 justify-center flex-wrap">
                                            <button type="button" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition"
                                                onclick="approveLead()">Terima</button>
                                            <button type="button" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition"
                                                onclick="rejectLead()">Tolak</button>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <!-- Group: Tampilkan leader -->
                                @if ($app->leader_name)
                                    <tr class="bg-gray-50">
                                        <td class="border px-4 py-2 font-medium">Ketua Tim</td>
                                        <td class="border px-4 py-2">{{ $app->leader_name }}</td>
                                        <td class="border px-4 py-2">{{ $app->leader_email ?? '-' }}</td>
                                        <td class="border px-4 py-2">{{ $app->leader_phone ?? '-' }}</td>
                                        <td class="border px-4 py-2">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                                @if ($app->leader_status == 'menunggu') bg-yellow-100 text-yellow-700
                                                @elseif($app->leader_status == 'diterima') bg-green-100 text-green-700
                                                @elseif($app->leader_status == 'ditolak') bg-red-100 text-red-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                {{ ucfirst($app->leader_status) }}
                                            </span>
                                        </td>
                                        <td class="border px-4 py-2">
                                            <div class="flex gap-1 justify-center flex-wrap">
                                                <button type="button" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition"
                                                    onclick="approveLead()">Terima</button>
                                                <button type="button" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition"
                                                    onclick="rejectLead()">Tolak</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                <!-- Group: Tampilkan members -->
                                @foreach ($app->members as $member)
                                    <tr>
                                        <td class="border px-4 py-2 font-medium">Anggota</td>
                                        <td class="border px-4 py-2">{{ $member->name }}</td>
                                        <td class="border px-4 py-2">{{ $member->email ?? '-' }}</td>
                                        <td class="border px-4 py-2">{{ $member->phone ?? '-' }}</td>
                                        <td class="border px-4 py-2">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-lg
                                                @if ($member->status == 'menunggu') bg-yellow-100 text-yellow-700
                                                @elseif($member->status == 'diterima') bg-green-100 text-green-700
                                                @elseif($member->status == 'ditolak') bg-red-100 text-red-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                {{ ucfirst($member->status) }}
                                            </span>
                                        </td>
                                        <td class="border px-4 py-2">
                                            <div class="flex gap-1 justify-center flex-wrap">
                                                <button type="button" class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition"
                                                    onclick="approveMember({{ $member->id }})">Terima</button>
                                                <button type="button" class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition"
                                                    onclick="rejectMember({{ $member->id }})">Tolak</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- RPA SCORE CARD -->
        @if (isset($score))
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">RPA Score</h2>

                <p class="text-gray-700 mb-2">Skor:
                    <span
                        class="font-bold text-lg
                    @if ($score >= 80) text-green-600
                    @elseif($score >= 50) text-yellow-600
                    @else text-red-600 @endif">
                        {{ $score }}
                    </span>
                </p>

                <p class="text-gray-600 text-sm">
    Rekomendasi Untuk HRD:
    @if ($score > 80)
        <span class="text-green-600 font-semibold">Diterima</span>
    @elseif ($score < 50)
        <span class="text-red-600 font-semibold">Ditolak</span>
    @else
        <span class="text-yellow-600 font-semibold">Dipertimbangkan</span>
    @endif
</p>

            </div>
        @endif

        @if (isset($deptRecommendations))
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Rekomendasi Departemen</h2>

                <table class="min-w-full border border-gray-300 text-center rounded">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">Departemen</th>
                            <th class="border px-4 py-2">Skor Simulasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deptRecommendations as $rec)
                            <tr>
                                <td class="border px-4 py-2 font-medium">{{ $rec['name'] }}</td>
                                <td class="border px-4 py-2">{{ $rec['score'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- RPA Breakdown -->
        @if (isset($breakdown))
            <div class="bg-white shadow rounded-lg p-6 mb-6">

                <h2 class="text-xl font-semibold mb-4">RPA Scoring Breakdown</h2>

                <table class="min-w-full border text-center border-gray-300 rounded">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-4 py-2">Komponen</th>
                            <th class="border px-4 py-2">Skor</th>
                            <th class="border px-4 py-2">Detail</th>
                        </tr>
                    </thead>
                    <tbody>

                        {{-- Info Required --}}
                        <tr>
                            <td class="border px-4 py-2 font-medium">Info Required</td>
                            <td class="border px-4 py-2">{{ $breakdown['info_required'] }} / 50</td>
                            <td class="border px-4 py-2 text-sm">
                                Terisi: {{ $breakdown['explanations']['info_required']['found'] }}
                                dari {{ $breakdown['explanations']['info_required']['required'] }}
                                <br>
                                Fields: {{ implode(', ', $breakdown['explanations']['info_required']['fields']) }}
                            </td>
                        </tr>

                        {{-- Major Match --}}
                        <tr>
                            <td class="border px-4 py-2 font-medium">Major Match</td>
                            <td class="border px-4 py-2">{{ $breakdown['major_match'] }} / 30</td>
                            <td class="border px-4 py-2 text-sm">
                                {{ is_string($breakdown['explanations']['major_match'])
                                    ? $breakdown['explanations']['major_match']
                                    : 'Matched with: ' . $breakdown['explanations']['major_match'] }}
                            </td>
                        </tr>

                        {{-- Quota Check --}}
                        <tr>
                            <td class="border px-4 py-2 font-medium">Quota Check</td>
                            <td class="border px-4 py-2">{{ $breakdown['quota_check'] }} / 20</td>
                            <td class="border px-4 py-2 text-sm">
                                @php
                                    $department = $app->department;
                                    $acceptedPeople = $department
                                        ? $department
                                            ->applications()
                                            ->where('status', 'diterima')
                                            ->where('id', '!=', $app->id)
                                            ->get()
                                            ->sum(function ($a) {
                                                return $a->type === 'group' ? $a->members->count() + 1 : 1;
                                            })
                                        : 0;

                                    $currentPeople = $app->type === 'group' ? $app->members->count() + 1 : 1;

                                    $remainingQuota = $department ? ($department->quota ?? 0) - $acceptedPeople : null;
                                    $quotaValid = $remainingQuota >= $currentPeople;
                                @endphp

                                Kuota tersisa: {{ $remainingQuota ?? '-' }}<br>
                                Kebutuhan group ini: {{ $currentPeople }}<br>
                                Valid: {{ $quotaValid ? 'Ya' : 'Tidak' }}
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        @endif

        <!-- RPA Result (Dokumen) -->
        @if ($app->rpaResult)
            <div x-data="{ openText: false, openFields: false }" class="bg-white shadow rounded-lg p-6 mb-6">

                <h2 class="text-xl font-semibold mb-4">RPA Result</h2>

                <!-- Extracted Text -->
                @if ($app->rpaResult->extracted_text)
                    <div class="mb-6">
                        <button @click="openText = !openText"
                            class="w-full flex justify-between items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md transition">
                            <span class="font-semibold text-gray-800">Teks Hasil OCR</span>
                            <span x-text="openText ? '▲' : '▼'" class="text-sm"></span>
                        </button>

                        <div x-show="openText" x-transition
                            class="mt-3 border rounded p-4 bg-gray-50 leading-relaxed text-gray-700 whitespace-pre-line overflow-auto max-h-[400px]">
                            {{ $app->rpaResult->extracted_text }}
                        </div>
                    </div>
                @endif

                <!-- Fields Detected -->
                @if ($app->rpaResult->fields)
                    <div class="mb-6">
                        <button @click="openFields = !openFields"
                            class="w-full flex justify-between items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md transition">
                            <span class="font-semibold text-gray-800">Field yang Terdeteksi</span>
                            <span x-text="openFields ? '▲' : '▼'" class="text-sm"></span>
                        </button>

                        <div x-show="openFields" x-transition class="mt-3 overflow-auto max-h-[600px]">
                            <table class="min-w-full table-auto border border-gray-300 rounded">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-4 py-2 text-left">Field</th>
                                        <th class="border px-4 py-2 text-left">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($app->rpaResult->fields as $key => $value)
                                        <tr class="align-top">
                                            <td class="border px-4 py-2 font-medium text-gray-800">
                                                {{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                            <td class="border px-4 py-2 text-gray-700 break-words leading-relaxed">
                                                @if (is_array($value))
                                                    @foreach ($value as $idx => $v)
                                                        @if (is_array($v))
                                                            <div class="mb-2">
                                                                @foreach ($v as $subKey => $subVal)
                                                                    <p><strong>{{ ucfirst(str_replace('_', ' ', $subKey)) }}:</strong>
                                                                        {{ $subVal }}</p>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p>{{ $v }}</p>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <p>{{ $value }}</p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        @endif

        <!-- PDF Viewer -->
        @if ($app->file_path)
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Dokumen PDF</h2>
                <div class="w-full border rounded overflow-hidden" style="height:90vh;">
                    <iframe src="{{ route('hrd.application.viewPdf', $app->id) }}" class="w-full h-full"
                        frameborder="0"></iframe>
                </div>
            </div>
        @endif


        <!-- Actions + UPDATE STATUS FORM -->
        <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">

            <a href="{{ route('hrd.dashboard') }}"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                Kembali ke Dashboard
            </a>

            @if ($app->status == 'pending')
                <div class="flex flex-col gap-4 bg-white shadow p-4 rounded-lg">

                    <form id="hrdActionForm" action="{{ route('hrd.application.update', $app->id) }}" method="POST"
                        class="flex flex-col">
                        @csrf

                        <label class="font-semibold mb-1">Pilih Departemen:</label>
                        <select name="department_id" class="border p-2 rounded w-full mb-4" required>
                            <option value="">-- Pilih Departemen --</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" @selected($app->department_id == $d->id)>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>

                        <label class="font-semibold mb-1">Catatan (wajib jika menolak)</label>
                        <textarea name="hrd_note" id="hrd_note" rows="3" class="border p-2 rounded w-full mb-3"
                            placeholder="Isi keterangan jika menolak..."></textarea>

                        <div class="flex gap-2">
                            <button type="button" id="acceptBtn"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                                Tandai Diterima
                            </button>

                            <button type="button" id="rejectBtn"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                                Tandai Ditolak
                            </button>
                        </div>
                    </form>

                </div>
            @endif



        </div>

    </div>
    @if ($errors->has('quota') || $errors->has('general'))
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();

            const acceptBtn = document.getElementById('acceptBtn');
            const rejectBtn = document.getElementById('rejectBtn');
            const form = document.getElementById('hrdActionForm');
            const noteEl = document.getElementById('hrd_note');

            if (acceptBtn) {
                acceptBtn.addEventListener('click', () => {
                    // set status to diterima and submit
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'status';
                    input.value = 'diterima';
                    form.appendChild(input);
                    form.submit();
                });
            }

            if (rejectBtn) {
                rejectBtn.addEventListener('click', () => {
                    // require note
                    if (!noteEl.value.trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Catatan dibutuhkan',
                            text: 'Silakan isi keterangan penolakan sebelum mengirim.',
                            confirmButtonColor: '#d33'
                        });
                        return;
                    }
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'status';
                    input.value = 'ditolak';
                    form.appendChild(input);
                    form.submit();
                });
            }
        });

        // Leader approval functions
        function approveLead() {
            Swal.fire({
                icon: 'question',
                title: 'Terima Leader',
                text: 'Apakah Anda yakin ingin menerima leader ini?',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/hrd/leader') }}/{{ $app->id }}/update`;
                    form.innerHTML = `{{ csrf_field() }}<input type="hidden" name="status" value="diterima">`;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function rejectLead() {
            Swal.fire({
                icon: 'warning',
                title: 'Tolak Leader',
                html: `<textarea id="leadNote" rows="3" class="w-full p-2 border border-gray-300 rounded" placeholder="Masukkan alasan penolakan..."></textarea>`,
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                didOpen: () => document.getElementById('leadNote').focus()
            }).then((result) => {
                if (result.isConfirmed) {
                    const note = document.getElementById('leadNote').value.trim();
                    if (!note) {
                        Swal.fire({ icon: 'error', title: 'Catatan Diperlukan', text: 'Silakan masukkan alasan penolakan', confirmButtonColor: '#ef4444' });
                        return;
                    }
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/hrd/leader') }}/{{ $app->id }}/update`;
                    form.innerHTML = `{{ csrf_field() }}<input type="hidden" name="status" value="ditolak"><input type="hidden" name="hrd_note" value="${note}">`;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function approveMember(memberId) {
            Swal.fire({
                icon: 'question',
                title: 'Terima Member',
                text: 'Apakah Anda yakin ingin menerima member ini?',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/hrd/member') }}/${memberId}/update`;
                    form.innerHTML = `{{ csrf_field() }}<input type="hidden" name="status" value="diterima">`;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function rejectMember(memberId) {
            Swal.fire({
                icon: 'warning',
                title: 'Tolak Member',
                html: `<textarea id="memberNote" rows="3" class="w-full p-2 border border-gray-300 rounded" placeholder="Masukkan alasan penolakan..."></textarea>`,
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                didOpen: () => document.getElementById('memberNote').focus()
            }).then((result) => {
                if (result.isConfirmed) {
                    const note = document.getElementById('memberNote').value.trim();
                    if (!note) {
                        Swal.fire({ icon: 'error', title: 'Catatan Diperlukan', text: 'Silakan masukkan alasan penolakan', confirmButtonColor: '#ef4444' });
                        return;
                    }
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('/hrd/member') }}/${memberId}/update`;
                    form.innerHTML = `{{ csrf_field() }}<input type="hidden" name="status" value="ditolak"><input type="hidden" name="hrd_note" value="${note}">`;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>


</x-app-layout>
