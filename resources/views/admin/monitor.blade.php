<x-layout title="Live Monitor — Admin">
    <div class="min-h-screen bg-slate-950 text-white" x-data="monitorApp()" x-init="init()">

        {{-- TOP NAVIGATION BAR --}}
        <div class="bg-slate-900/80 backdrop-blur border-b border-white/10 sticky top-0 z-20">
            <div class="max-w-screen-2xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Dashboard
                    </a>
                    <div class="w-px h-5 bg-white/20"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                        <h1 class="text-white font-bold text-lg tracking-tight">Live Queue Monitor</h1>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    {{-- Auto-refresh toggle --}}
                    <label class="flex items-center gap-2 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" class="sr-only peer" x-model="autoRefresh" @change="toggleAutoRefresh()">
                            <div class="w-10 h-5 bg-slate-700 peer-checked:bg-emerald-500 rounded-full transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm text-slate-400 peer-checked:text-emerald-400">Auto-refresh</span>
                    </label>

                    {{-- Last updated --}}
                    <div class="text-right hidden sm:block">
                        <p class="text-xs text-slate-500">Diperbarui</p>
                        <p class="text-sm font-mono text-slate-300" x-text="lastUpdated">—</p>
                    </div>

                    {{-- Manual refresh --}}
                    <button @click="refreshAll()" class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 border border-white/10 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all active:scale-95">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- GLOBAL STATS BAR --}}
        <div class="bg-slate-900/50 border-b border-white/5">
            <div class="max-w-screen-2xl mx-auto px-6 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @php
                        $totalWaiting   = $locations->sum('today_waiting');
                        $totalCompleted = $locations->sum('today_completed');
                        $totalToday     = $locations->sum('today_total');
                        $totalPriority  = $locations->sum('today_priority');
                        $activeOps      = $locations->sum(fn($l) => $l->counters->filter(fn($c) => $c->currentOperator)->count());
                    @endphp
                    <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 text-center">
                        <p class="text-amber-400 text-xs font-semibold uppercase tracking-widest mb-1">Menunggu</p>
                        <p class="text-4xl font-black text-amber-300" id="global-waiting">{{ $totalWaiting }}</p>
                    </div>
                    <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-4 text-center">
                        <p class="text-emerald-400 text-xs font-semibold uppercase tracking-widest mb-1">Dilayani Hari Ini</p>
                        <p class="text-4xl font-black text-emerald-300" id="global-completed">{{ $totalCompleted }}</p>
                    </div>
                    <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4 text-center">
                        <p class="text-red-400 text-xs font-semibold uppercase tracking-widest mb-1">Prioritas Menunggu</p>
                        <p class="text-4xl font-black text-red-300" id="global-priority">{{ $totalPriority }}</p>
                    </div>
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-2xl p-4 text-center">
                        <p class="text-blue-400 text-xs font-semibold uppercase tracking-widest mb-1">Operator Aktif</p>
                        <p class="text-4xl font-black text-blue-300" id="global-operators">{{ $activeOps }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- LOCATION CARDS GRID --}}
        <div id="monitor-grid" class="max-w-screen-2xl mx-auto px-6 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($locations as $location)
                @php
                    $activeCounters = $location->counters->filter(fn($c) => $c->currentOperator);
                    $callingTickets = $location->tickets->filter(fn($t) => $t->status === 'calling');
                    $servingTickets = $location->tickets->filter(fn($t) => $t->status === 'serving');
                    $waitingNow     = $location->today_waiting;
                    $priorityNow    = $location->today_priority;
                    $completionRate = $location->today_total > 0
                        ? round(($location->today_completed / $location->today_total) * 100)
                        : 0;
                    $isActive       = $location->isOpen();
                @endphp

                <div
                    id="loc-card-{{ $location->id }}"
                    class="bg-slate-900 border rounded-2xl overflow-hidden transition-all duration-300 {{ $isActive ? 'border-white/10 hover:border-white/20' : 'border-white/5 opacity-60' }}"
                >
                    {{-- Card Header --}}
                    <div class="p-5 border-b border-white/10 flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-2.5 h-2.5 rounded-full {{ $isActive ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></span>
                                <h2 class="font-bold text-white text-lg leading-tight">{{ $location->name }}</h2>
                            </div>
                            <p class="text-slate-500 text-sm">
                                Kode: <span class="font-mono text-slate-400">{{ $location->code }}</span>
                                · {{ $isActive ? 'Buka ' . $location->open_time->format('H:i') . '–' . $location->close_time->format('H:i') : 'Tutup' }}
                            </p>
                        </div>
                        <a href="{{ route('locations.display', $location) }}" target="_blank"
                           class="flex items-center gap-1 text-xs text-slate-500 hover:text-slate-300 border border-white/10 hover:border-white/20 px-3 py-1.5 rounded-lg transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Display
                        </a>
                    </div>

                    {{-- Stats Row --}}
                    <div class="grid grid-cols-3 divide-x divide-white/5 border-b border-white/5">
                        <div class="p-4 text-center">
                            <p class="text-amber-400 text-xs font-semibold uppercase tracking-wider mb-1">Menunggu</p>
                            <p class="text-3xl font-black {{ $waitingNow > 10 ? 'text-red-400' : 'text-amber-300' }}" id="loc-{{ $location->id }}-waiting">{{ $waitingNow }}</p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-1">Selesai</p>
                            <p class="text-3xl font-black text-emerald-300" id="loc-{{ $location->id }}-completed">{{ $location->today_completed }}</p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-blue-400 text-xs font-semibold uppercase tracking-wider mb-1">Operator</p>
                            <p class="text-3xl font-black text-blue-300">{{ $activeCounters->count() }}/{{ $location->counters->count() }}</p>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="px-5 py-3 border-b border-white/5">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs text-slate-500">Tingkat Penyelesaian</span>
                            <span class="text-xs font-bold text-white">{{ $completionRate }}%</span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-700
                                {{ $completionRate >= 80 ? 'bg-emerald-500' : ($completionRate >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                style="width: {{ $completionRate }}%">
                            </div>
                        </div>
                    </div>

                    {{-- Priority Alert --}}
                    @if($priorityNow > 0)
                    <div class="px-5 py-3 bg-red-500/10 border-b border-red-500/20 flex items-center gap-2">
                        <span class="text-red-400 text-sm">🚨</span>
                        <span class="text-sm text-red-300 font-semibold">{{ $priorityNow }} tiket prioritas menunggu</span>
                    </div>
                    @endif

                    {{-- Currently Calling/Serving --}}
                    @if($callingTickets->count() > 0 || $servingTickets->count() > 0)
                    <div class="p-5 border-b border-white/5">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-3">Sedang Dipanggil / Dilayani</p>
                        <div class="space-y-2">
                            @foreach($callingTickets as $t)
                            <div class="flex items-center justify-between bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-2.5">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                    <span class="font-black text-green-300 text-lg">{{ $t->ticket_number }}</span>
                                    @if($t->is_priority)<span class="text-xs bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full font-semibold">🚨 Prioritas</span>@endif
                                </div>
                                <span class="text-xs text-slate-400">Memanggil...</span>
                            </div>
                            @endforeach
                            @foreach($servingTickets as $t)
                            <div class="flex items-center justify-between bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-2.5">
                                <div class="flex items-center gap-3">
                                    <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                                    <span class="font-black text-blue-300 text-lg">{{ $t->ticket_number }}</span>
                                    @if($t->is_priority)<span class="text-xs bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full font-semibold">🚨 Prioritas</span>@endif
                                </div>
                                <span class="text-xs text-slate-400">Dilayani</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Counters Status --}}
                    <div class="p-5">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-3">Status Loket</p>
                        @if($location->counters->count() > 0)
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($location->counters as $counter)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-xl {{ $counter->currentOperator ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-slate-800/50 border border-white/5' }}">
                                <span class="w-2 h-2 rounded-full {{ $counter->currentOperator ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600' }}"></span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold truncate {{ $counter->currentOperator ? 'text-white' : 'text-slate-500' }}">{{ $counter->name }}</p>
                                    @if($counter->currentOperator)
                                        <p class="text-xs text-emerald-400 truncate">{{ $counter->currentOperator->name }}</p>
                                    @else
                                        <p class="text-xs text-slate-600">Kosong</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-slate-600 text-sm">Belum ada loket.</p>
                        @endif

                        {{-- Quick links --}}
                        <div class="flex gap-2 mt-4">
                            <a href="{{ route('locations.kiosk', $location) }}" target="_blank"
                               class="flex-1 flex items-center justify-center gap-1.5 text-xs font-semibold py-2 rounded-lg border border-indigo-500/30 text-indigo-400 hover:bg-indigo-500/10 transition-all">
                                🖥️ Kiosk
                            </a>
                            <a href="{{ route('admin.counters', $location) }}"
                               class="flex-1 flex items-center justify-center gap-1.5 text-xs font-semibold py-2 rounded-lg border border-white/10 text-slate-400 hover:bg-white/5 transition-all">
                                ⚙️ Kelola
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-24">
                    <div class="text-6xl mb-4">📭</div>
                    <p class="text-slate-500 text-xl font-semibold">Belum ada lokasi aktif.</p>
                    <a href="{{ route('admin.locations.create') }}" class="mt-4 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-6 rounded-xl transition-all">
                        + Tambah Lokasi
                    </a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Last refresh timestamp footer --}}
        <div class="fixed bottom-4 right-4 text-xs text-slate-600 bg-slate-900/80 border border-white/5 backdrop-blur px-3 py-1.5 rounded-full hidden sm:block">
            📡 Real-time via WebSocket · Auto-refresh setiap 30 detik
        </div>
    </div>

    @push('scripts')
    <script type="module">
        let refreshInterval;
        const locationIds = @json($locations->pluck('id'));

        function monitorApp() {
            return {
                autoRefresh: true,
                loading: false,
                lastUpdated: new Date().toLocaleTimeString('id-ID'),

                init() {
                    // Subscribe to all location channels via WebSocket
                    locationIds.forEach(id => {
                        window.Echo.channel(`location.${id}`)
                            .listen('.ticket.called',        () => this.scheduleRefresh())
                            .listen('.ticket.status.changed',() => this.scheduleRefresh())
                            .listen('.ticket.created',       () => this.scheduleRefresh())
                            .listen('.queue.updated',        () => this.scheduleRefresh());
                    });

                    // Start auto-refresh timer
                    this.startAutoRefresh();
                },

                scheduleRefresh() {
                    clearTimeout(this._debounce);
                    this._debounce = setTimeout(() => this.refreshAll(), 600);
                },

                startAutoRefresh() {
                    clearInterval(refreshInterval);
                    if (this.autoRefresh) {
                        refreshInterval = setInterval(() => this.refreshAll(), 30000);
                    }
                },

                toggleAutoRefresh() {
                    this.startAutoRefresh();
                },

                async refreshAll() {
                    if (this.loading) return;
                    this.loading = true;
                    try {
                        const resp = await fetch(window.location.href, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                        });
                        if (!resp.ok) return;
                        const html = await resp.text();
                        const doc = new DOMParser().parseFromString(html, 'text/html');

                        // Replace the whole grid
                        const newGrid = doc.getElementById('monitor-grid');
                        const oldGrid = document.getElementById('monitor-grid');
                        if (newGrid && oldGrid) oldGrid.innerHTML = newGrid.innerHTML;

                        // Update global stats
                        ['global-waiting', 'global-completed', 'global-priority', 'global-operators'].forEach(id => {
                            const newEl = doc.getElementById(id);
                            const oldEl = document.getElementById(id);
                            if (newEl && oldEl) {
                                if (oldEl.textContent !== newEl.textContent) {
                                    oldEl.textContent = newEl.textContent;
                                    oldEl.classList.add('scale-125');
                                    setTimeout(() => oldEl.classList.remove('scale-125'), 300);
                                }
                            }
                        });

                        this.lastUpdated = new Date().toLocaleTimeString('id-ID');
                    } catch(e) {
                        console.warn('Monitor refresh error:', e);
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }

        window.monitorApp = monitorApp;
    </script>
    <style>
        #global-waiting, #global-completed, #global-priority, #global-operators {
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .scale-125 { transform: scale(1.25); }
    </style>
    @endpush
</x-layout>
