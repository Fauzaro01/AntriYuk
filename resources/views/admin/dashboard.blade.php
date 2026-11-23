<x-layout title="Dashboard Admin">
    <div class="min-h-screen bg-slate-100 dark:bg-slate-900">
        <!-- Admin Header -->
        <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-slate-900 dark:text-white">Dashboard Admin</h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola sistem antrian AntriYuk</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.audit-logs') }}" class="flex items-center gap-2 bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors">
                            📜 Audit Log
                        </a>
                        <a href="{{ route('admin.monitor') }}" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                            <span class="w-2 h-2 bg-emerald-300 rounded-full animate-pulse"></span>
                            Live Monitor
                        </a>
                        <a href="{{ route('home') }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white px-3 py-2 text-sm font-medium">
                            Lihat Situs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Total Lokasi</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1" data-stat="total_locations">{{ $stats['total_locations'] }}</p>
                            <p class="text-sm text-green-600 dark:text-green-400 mt-1">{{ $stats['active_locations'] }} aktif</p>
                        </div>
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Total Operator</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['total_operators'] }}</p>
                            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">pengguna</p>
                        </div>
                        <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Tiket Hari Ini</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['today_tickets'] }}</p>
                            <p class="text-sm text-green-600 dark:text-green-400 mt-1">{{ $stats['today_completed'] }} selesai</p>
                        </div>
                        <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Rata-rata Waktu</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">~5</p>
                            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">menit/layanan</p>
                        </div>
                        <div class="w-14 h-14 bg-amber-100 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <a href="{{ route('admin.locations') }}" class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-700 transition-all">
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-blue-600 transition-colors">
                        <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Kelola Lokasi</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Tambah, edit, atau hapus lokasi layanan dan loket</p>
                    <div class="mt-4 flex items-center text-blue-600 font-medium text-sm">
                        Kelola
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </a>

                <a href="{{ route('admin.users') }}" class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 hover:shadow-lg hover:border-green-200 dark:hover:border-green-700 transition-all">
                    <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-green-600 transition-colors">
                        <svg class="w-7 h-7 text-green-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Kelola Pengguna</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Atur role pengguna dan assign operator ke lokasi</p>
                    <div class="mt-4 flex items-center text-green-600 font-medium text-sm">
                        Kelola
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </a>

                <a href="{{ route('locations.index') }}" class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 hover:shadow-lg hover:border-purple-200 dark:hover:border-purple-700 transition-all">
                    <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-purple-600 transition-colors">
                        <svg class="w-7 h-7 text-purple-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Lihat Halaman Publik</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Preview tampilan untuk pengunjung dan customer</p>
                    <div class="mt-4 flex items-center text-purple-600 font-medium text-sm">
                        Lihat
                        <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                </a>
            </div>

            {{-- ============================================================ --}}
            {{-- CHARTS SECTION --}}
            {{-- ============================================================ --}}
            <div class="mb-8">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
                    <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center text-base">📊</span>
                    Grafik & Analitik Hari Ini
                </h2>
                <div class="grid lg:grid-cols-3 gap-6">

                    {{-- Chart 1: Donut — status distribution --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h3 class="font-bold text-slate-900 dark:text-white mb-1">Distribusi Status Tiket</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Hari ini secara keseluruhan</p>
                        <div class="relative h-52 flex items-center justify-center">
                            <canvas id="donut-chart"></canvas>
                            <div class="absolute text-center pointer-events-none" id="donut-center">
                                <p class="text-3xl font-black text-slate-900 dark:text-white">{{ array_sum($donutData) }}</p>
                                <p class="text-xs text-slate-500">Total</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4 text-xs">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Selesai ({{ $donutData['completed'] }})</span></div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400 flex-shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Menunggu ({{ $donutData['waiting'] }})</span></div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-400 flex-shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Dilewati ({{ $donutData['skipped'] }})</span></div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-slate-400 flex-shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Dibatalkan ({{ $donutData['cancelled'] }})</span></div>
                        </div>
                    </div>

                    {{-- Chart 2: Hourly bar --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h3 class="font-bold text-slate-900 dark:text-white mb-1">Tiket Per Jam</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Volume antrian sepanjang hari</p>
                        <div class="h-52">
                            <canvas id="hourly-chart"></canvas>
                        </div>
                    </div>

                    {{-- Chart 3: Per-location bar --}}
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
                        <h3 class="font-bold text-slate-900 dark:text-white mb-1">Perbandingan Lokasi</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Selesai vs Menunggu per lokasi</p>
                        <div class="h-52">
                            <canvas id="location-chart"></canvas>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Locations Overview Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Ringkasan Lokasi</h2>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Status semua lokasi layanan hari ini</p>
                    </div>
                    <a href="{{ route('admin.locations.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-medium text-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Lokasi
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Lokasi</th>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tiket Hari Ini</th>
                                <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Menunggu</th>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($locations as $location)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mr-4">
                                                <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">{{ substr($location->code, 0, 2) }}</span>
                                            </div>
                                            <span class="font-semibold text-slate-900 dark:text-white">{{ $location->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-3 py-1.5 rounded-lg text-sm font-medium">{{ $location->code }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-slate-900 dark:text-white font-semibold">{{ $location->today_tickets_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($location->waiting_count > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">
                                                {{ $location->waiting_count }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 dark:text-slate-500">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($location->is_active)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                                <span class="w-2 h-2 bg-slate-400 rounded-full mr-1.5"></span>
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-3">
                                            <a href="{{ route('admin.counters', $location) }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                                Loket
                                            </a>
                                            <a href="{{ route('admin.locations.edit', $location) }}" class="text-slate-600 dark:text-slate-400 hover:text-slate-700 font-medium text-sm">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        Belum ada lokasi. <a href="{{ route('admin.locations.create') }}" class="text-blue-600 hover:underline">Tambah lokasi baru</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor  = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
        const labelColor = isDark ? '#94a3b8' : '#64748b';

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.animation.duration = 800;

        // ── Chart 1: Donut ────────────────────────────────────────────
        new Chart(document.getElementById('donut-chart'), {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Menunggu', 'Dilewati', 'Dibatalkan'],
                datasets: [{
                    data: [
                        {{ $donutData['completed'] }},
                        {{ $donutData['waiting'] }},
                        {{ $donutData['skipped'] }},
                        {{ $donutData['cancelled'] }},
                    ],
                    backgroundColor: ['#10b981', '#fbbf24', '#f87171', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                cutout: '72%',
                plugins: { legend: { display: false }, tooltip: { callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed} tiket`
                }}},
                responsive: true,
                maintainAspectRatio: false,
            }
        });

        // ── Chart 2: Hourly Bar ───────────────────────────────────────
        const hourlyLabels = @json($hourlyLabels);
        const hourlyValues = @json($hourlyValues);

        new Chart(document.getElementById('hourly-chart'), {
            type: 'bar',
            data: {
                labels: hourlyLabels,
                datasets: [{
                    label: 'Tiket',
                    data: hourlyValues,
                    backgroundColor: 'rgba(99,102,241,0.75)',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 10 } } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, precision: 0 }, beginAtZero: true },
                }
            }
        });

        // ── Chart 3: Per-Location Bar ─────────────────────────────────
        new Chart(document.getElementById('location-chart'), {
            type: 'bar',
            data: {
                labels: @json($locationChartData['labels']),
                datasets: [
                    {
                        label: 'Selesai',
                        data: @json($locationChartData['completed']),
                        backgroundColor: 'rgba(16,185,129,0.8)',
                        borderRadius: 6,
                        borderSkipped: false,
                    },
                    {
                        label: 'Menunggu',
                        data: @json($locationChartData['waiting']),
                        backgroundColor: 'rgba(251,191,36,0.8)',
                        borderRadius: 6,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: labelColor, boxWidth: 12, padding: 12 } } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 10 } } },
                    y: { grid: { color: gridColor }, ticks: { color: labelColor, precision: 0 }, beginAtZero: true },
                }
            }
        });
    </script>
    @endpush
</x-layout>
