@section('title', 'Dashboard HRD')
<x-app-layout>

    @php
        $currentMonth = \Carbon\Carbon::now()->month;

        // Hitung lolos_count dari leader_status='diterima' + members dengan status='diterima'
        $lolos_count = 0;
        $tidak_lolos_count = 0;
        $pending_count = 0;

        $allApps = \App\Models\Application::whereMonth('created_at', $currentMonth)->get();
        
        foreach ($allApps as $app) {
            if ($app->type === 'individual') {
                // Individual: tergantung leader_status
                if ($app->leader_status == 'diterima') {
                    $lolos_count++;
                } elseif ($app->leader_status == 'ditolak') {
                    $tidak_lolos_count++;
                } elseif ($app->leader_status == 'menunggu') {
                    $pending_count++;
                }
            } else {
                // Group: hitung leader + members
                if ($app->leader_status == 'diterima') {
                    $lolos_count++;
                } elseif ($app->leader_status == 'ditolak') {
                    $tidak_lolos_count++;
                } elseif ($app->leader_status == 'menunggu') {
                    $pending_count++;
                }
                
                // Hitung members
                foreach ($app->members as $member) {
                    if ($member->status == 'diterima') {
                        $lolos_count++;
                    } elseif ($member->status == 'ditolak') {
                        $tidak_lolos_count++;
                    } elseif ($member->status == 'menunggu') {
                        $pending_count++;
                    }
                }
            }
        }

        // Batasi aplikasi terbaru 5 buah
        $latest_applications = $applications->take(5);

        // Ambil departemen pertama untuk default tampilan
        $firstDepartment = $departments->first();
        $otherDepartments = $departments->slice(1);
        $today = \Carbon\Carbon::today()->toDateString();
    @endphp

    <div class="px-2 md:px-4 py-2">

        <!-- Header Dashboard -->
        <div class="flex items-center gap-2 mb-4">
            <h3 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard HRD</h3>
        </div>

        <!-- CARDS STATISTIK + MINI CHART -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-6">
            <!-- Card Total Lolos -->
            <div
                class="shadow-md rounded-xl p-3 flex items-center justify-between border border-green-200 transform hover:scale-105 transition">
                <div>
                    <h4 class="text-green-800 text-xs md:text-sm font-semibold">Total Lolos Bulan Ini</h4>
                    <p class="text-2xl md:text-3xl font-bold text-green-900">{{ $lolos_count }}</p>
                </div>
                <div class="flex flex-col items-center">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-700 mb-1"></i>
                </div>
            </div>

            <!-- Card Total Tidak Lolos -->
            <div
                class="shadow-md rounded-xl p-3 flex items-center justify-between border border-red-200 transform hover:scale-105 transition">
                <div>
                    <h4 class="text-red-800 text-xs md:text-sm font-semibold">Total Tidak Lolos Bulan Ini</h4>
                    <p class="text-2xl md:text-3xl font-bold text-red-900">{{ $tidak_lolos_count }}</p>
                </div>
                <div class="flex flex-col items-center">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-700 mb-1"></i>
                </div>
            </div>

            <!-- Card Pending -->
            <div
                class="shadow-md rounded-xl p-3 flex items-center justify-between border border-yellow-200 transform hover:scale-105 transition">
                <div>
                    <h4 class="text-yellow-800 text-xs md:text-sm font-semibold">Total Menunggu Bulan Ini</h4>
                    <p class="text-2xl md:text-3xl font-bold text-yellow-900">{{ $pending_count }}</p>
                </div>
                <div class="flex flex-col items-center">
                    <i data-lucide="clock" class="w-6 h-6 text-yellow-700 mb-1"></i>
                </div>
            </div>

            <!-- Mini Chart Card -->
            <div
                class="bg-blue-50 shadow-md rounded-xl p-3 border border-blue-200 flex flex-col items-center justify-center">
                <h4 class="text-blue-700 text-xs md:text-sm font-semibold mb-1">Statistik Lolos / Tidak Lolos</h4>
                <div style="width: 80px; height: 80px;">
                    <canvas id="miniStatistikChart"></canvas>
                </div>
                <div class="flex gap-2 mt-2 text-xs md:text-sm font-medium text-blue-700">
                    <div class="flex items-center gap-1">
                        <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span> Lolos: {{ $lolos_count }}
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="inline-block w-2 h-2 bg-red-500 rounded-full"></span> Tidak Lolos:
                        {{ $tidak_lolos_count }}
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION: Departemen & Kuota -->
        <div class="bg-white shadow-sm rounded-lg p-3 mb-6 border border-gray-100">
            <div class="flex items-center gap-1 mb-2">
                <i data-lucide="building-2" class="w-5 h-5 text-gray-700"></i>
                <h4 class="text-lg font-semibold text-gray-800">Departemen & Kuota</h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs md:text-sm border border-gray-200 rounded-lg overflow-hidden table-fixed">
                    <thead class="bg-gray-50 text-gray-700 text-center">
                        <tr>
                            <th class="px-2 py-1 border w-1/4">Departemen</th>
                            <th class="px-2 py-1 border w-1/4">Kuota</th>
                            <th class="px-2 py-1 border w-1/4">Diterima</th>
                            <th class="px-2 py-1 border w-1/4">Sisa</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-center">
                        @if ($firstDepartment)
                            @php
                                // Ambil activeQuota untuk periode aktif
                                $activeQuota = $firstDepartment->quotas()
                                    ->where('period_start', '<=', $today)
                                    ->where('period_end', '>=', $today)
                                    ->orderBy('period_start', 'desc')
                                    ->first();

                                // Kuota numerik diambil langsung dari kolom departments
                                $quotaValue = (int) ($firstDepartment->quota ?? 0);

                                // Hitung accepted people berdasarkan leader_status dan application_members.status
                                if ($activeQuota) {
                                    $appsInPeriod = \App\Models\Application::where('department_id', $firstDepartment->id)
                                        ->where(function($q) use ($activeQuota) {
                                            $q->whereBetween('period_start', [$activeQuota->period_start, $activeQuota->period_end])
                                              ->orWhereBetween('period_end', [$activeQuota->period_start, $activeQuota->period_end])
                                              ->orWhere(function($qq) use ($activeQuota) {
                                                  $qq->where('period_start','<=',$activeQuota->period_start)
                                                     ->where('period_end','>=',$activeQuota->period_end);
                                              });
                                        })->get();
                                } else {
                                    $appsInPeriod = $firstDepartment->applications()->get();
                                }

                                $acceptedPeople = 0;
                                foreach ($appsInPeriod as $a) {
                                    if ($a->type === 'individual') {
                                        // Individual: hitung jika leader diterima
                                        if ($a->leader_status == 'diterima') {
                                            $acceptedPeople++;
                                        }
                                    } else {
                                        // Group: hitung ketua (jika diterima) + members yang diterima
                                        if ($a->leader_status == 'diterima') {
                                            $acceptedPeople++; // ketua
                                        }
                                        $acceptedPeople += $a->members->where('status', 'diterima')->count();
                                    }
                                }

                                $remainingQuota = max(0, $quotaValue - $acceptedPeople);
                            @endphp

                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-2 py-1 border font-medium text-left">{{ $firstDepartment->name }}</td>
                                <td class="px-2 py-1 border text-center">
                                    <span class="font-semibold ">{{ $quotaValue }}</span>
                                </td>
                                <td class="px-2 py-1 border text-blue-600 font-semibold">
                                    <i data-lucide="users" class="inline w-4 h-4"></i> {{ $acceptedPeople }}
                                </td>
                                <td class="px-2 py-1 border">
                                    <span class="inline-flex items-center gap-1 px-1 py-0.5 rounded-lg text-xs font-semibold {{ $remainingQuota == 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        <i data-lucide="activity" class="w-3 h-3"></i> {{ $remainingQuota }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if ($otherDepartments->count() > 0)
                <div id="expandedDeptWrapper" class="overflow-hidden transition-all duration-500 max-h-0 mt-1">
                    <div id="expandedDeptTable" class="overflow-x-auto">
                        <table class="w-full text-xs md:text-sm border border-gray-200 rounded-lg overflow-hidden table-fixed">
                            <tbody class="text-gray-700 text-center">
                                @foreach ($otherDepartments as $d)
                                    @php
                                        $activeQuota = $d->quotas()
                                            ->where('period_start','<=',$today)
                                            ->where('period_end','>=',$today)
                                            ->orderBy('period_start','desc')
                                            ->first();

                                        // tetap ambil kuota dari departments
                                        $quotaValue = (int) ($d->quota ?? 0);

                                        if ($activeQuota) {
                                            $appsInPeriod = \App\Models\Application::where('department_id', $d->id)
                                                ->where(function($q) use ($activeQuota) {
                                                    $q->whereBetween('period_start', [$activeQuota->period_start, $activeQuota->period_end])
                                                      ->orWhereBetween('period_end', [$activeQuota->period_start, $activeQuota->period_end])
                                                      ->orWhere(function($qq) use ($activeQuota) {
                                                          $qq->where('period_start','<=',$activeQuota->period_start)
                                                             ->where('period_end','>=',$activeQuota->period_end);
                                                      });
                                                })->get();
                                        } else {
                                            $appsInPeriod = $d->applications()->get();
                                        }

                                        $acceptedPeople = 0;
                                        foreach ($appsInPeriod as $a) {
                                            if ($a->type === 'individual') {
                                                // Individual: hitung jika leader diterima
                                                if ($a->leader_status == 'diterima') {
                                                    $acceptedPeople++;
                                                }
                                            } else {
                                                // Group: hitung ketua (jika diterima) + members yang diterima
                                                if ($a->leader_status == 'diterima') {
                                                    $acceptedPeople++; // ketua
                                                }
                                                $acceptedPeople += $a->members->where('status', 'diterima')->count();
                                            }
                                        }

                                        $remainingQuota = max(0, $quotaValue - $acceptedPeople);
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition border-b">
                                        <td class="px-2 py-1 border w-1/4 font-medium text-left">{{ $d->name }}</td>
                                        <td class="px-2 py-1 border w-1/4 text-center font-semibold">{{ $quotaValue }}</td>
                                        <td class="px-2 py-1 border w-1/4 text-blue-600 font-semibold">
                                            <i data-lucide="users" class="inline w-4 h-4"></i> {{ $acceptedPeople }}
                                        </td>
                                        <td class="px-2 py-1 border w-1/4">
                                            <span class="inline-flex items-center gap-1 px-1 py-0.5 rounded-lg text-xs font-semibold {{ $remainingQuota == 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                <i data-lucide="activity" class="w-3 h-3"></i> {{ $remainingQuota }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-center -mt-2">
                    <button id="expandDeptBtn" class="text-gray-600 text-lg transition-transform duration-300">
                        ▼
                    </button>
                </div>
            @endif

        </div>

        <!-- SECTION: Pengajuan Terbaru -->
        <div class="bg-white shadow-sm rounded-lg p-3 border border-gray-100 mt-4">
            <div class="flex items-center gap-2 mb-2 justify-between">
                <div class="flex items-center gap-1">
                    <i data-lucide="files" class="w-5 h-5 text-gray-700"></i>
                    <h4 class="text-lg font-semibold text-gray-800">Pengajuan Terbaru</h4>
                </div>
                <a href="{{ route('hrd.applications.index') }}" class="text-blue-600 hover:text-blue-800 p-1 rounded-full border border-blue-200 hover:bg-blue-50 transition">
                    <i data-lucide="list-check" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs md:text-sm border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50 text-center">
                        <tr>
                            <th class="px-1 py-1 border">No</th>
                            <th class="px-1 py-1 border">Nama</th>
                            <th class="px-1 py-1 border">Major</th>
                            <th class="px-1 py-1 border">Departemen</th>
                            <th class="px-1 py-1 border">Status</th>
                            <th class="px-1 py-1 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-center">
                        @foreach ($latest_applications as $a)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-1 py-1 border">{{ $a->id }}</td>
                                <td class="px-1 py-1 border font-medium text-left">{{ $a->leader_name }}</td>
                                <td class="px-1 py-1 border">{{ $a->major }}</td>
                                <td class="px-1 py-1 border">{{ $a->department->name ?? '-' }}</td>
                                <td class="px-1 py-0.5 border">
                                    <div class="flex flex-col items-center gap-1 py-1">
                                        @if ($a->type === 'individual')
                                            @php
                                                $statusColor = match($a->leader_status) {
                                                    'menunggu' => 'bg-yellow-100 text-yellow-700',
                                                    'diterima' => 'bg-green-100 text-green-700',
                                                    'ditolak' => 'bg-red-100 text-red-700',
                                                    default => 'bg-gray-100 text-gray-700'
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $statusColor }}">
                                                {{ strtoupper($a->leader_status) }}
                                            </span>
                                        @else
                                            <div class="flex items-center gap-2">
                                                {{-- Ketua Status --}}
                                                <div class="flex flex-col items-center">
                                                    <span class="text-[9px] text-gray-400 font-bold uppercase">K</span>
                                                    @php
                                                        $leaderColor = match($a->leader_status) {
                                                            'diterima' => 'bg-green-100 text-green-700',
                                                            'ditolak' => 'bg-red-100 text-red-700',
                                                            default => 'bg-yellow-100 text-yellow-700'
                                                        };
                                                    @endphp
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $leaderColor }}">
                                                        {{ strtoupper(substr($a->leader_status, 0, 1)) }}
                                                    </span>
                                                </div>
                                                
                                                <div class="w-px h-6 bg-gray-200"></div>

                                                {{-- Anggota Summary --}}
                                                <div class="flex flex-col items-center">
                                                    <span class="text-[9px] text-gray-400 font-bold uppercase">A</span>
                                                    @php
                                                        $accepted = $a->members->where('status', 'diterima')->count();
                                                        $total = $a->members->count();
                                                    @endphp
                                                    <span class="text-[10px] font-bold text-gray-700">{{ $accepted }}/{{ $total }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-1 py-0.5 border">
                                    <a href="{{ route('hrd.application.show', $a->id) }}" class="inline-flex items-center gap-1 text-blue-600 font-semibold hover:underline text-xs">
                                        <i data-lucide="eye" class="w-4 h-4"></i> Lihat
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- INIT LUCIDE -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();

            const expandBtn = document.getElementById('expandDeptBtn');
            const wrapper = document.getElementById('expandedDeptWrapper');

            if (expandBtn && wrapper) {
                expandBtn.addEventListener('click', () => {
                    if (wrapper.classList.contains('max-h-0')) {
                        wrapper.classList.remove('max-h-0');
                        wrapper.classList.add('max-h-screen');
                        expandBtn.innerText = '▲';
                        expandBtn.style.transform = 'rotate(180deg)';
                    } else {
                        wrapper.classList.add('max-h-0');
                        wrapper.classList.remove('max-h-screen');
                        expandBtn.innerText = '▼';
                        expandBtn.style.transform = 'rotate(0deg)';
                    }
                });
            }
        });
    </script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctxMini = document.getElementById('miniStatistikChart').getContext('2d');
        const miniStatistikChart = new Chart(ctxMini, {
            type: 'doughnut',
            data: {
                labels: ['Lolos', 'Tidak Lolos'],
                datasets: [{
                    data: [{{ $lolos_count }}, {{ $tidak_lolos_count }}],
                    backgroundColor: ['#22c55e', '#ef4444'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return context.label + ': ' + context.parsed + ' Orang'; }
                        }
                    }
                }
            }
        });
    </script>

</x-app-layout>
