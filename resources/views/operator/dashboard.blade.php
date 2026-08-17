<x-layout title="Dashboard Operator">
    <div class="min-h-screen bg-slate-100 dark:bg-slate-900">
        <!-- Top Bar -->
        <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-slate-900 dark:text-white">Dashboard Operator</h1>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $location->name }}</p>
                        </div>
                    </div>

                    @if($counter)
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $counter->name }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400">● Aktif</p>
                            </div>
                            <form method="POST" action="{{ route('operator.leave-counter') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                    Keluar Loket
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Stats Cards -->
            <div id="stats-section" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Dilayani Hari Ini</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ $todayStats['served'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Menunggu</p>
                            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $todayStats['waiting'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Dilewati</p>
                            <p class="text-3xl font-bold text-red-600 mt-1">{{ $todayStats['skipped'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Loket Aktif</p>
                            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $counters->filter(fn($c) => $c->currentOperator)->count() }}/{{ $counters->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Main Panel -->
                <div class="lg:col-span-2 space-y-6">
                    @if(!$counter)
                        <!-- Counter Selection -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Pilih Loket</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1">Pilih loket untuk mulai melayani antrian</p>
                            </div>
                            <div class="p-6">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    @foreach($counters as $c)
                                        <form method="POST" action="{{ route('operator.assign-counter') }}">
                                            @csrf
                                            <input type="hidden" name="counter_id" value="{{ $c->id }}">
                                            <button
                                                type="submit"
                                                class="w-full p-5 border-2 rounded-xl text-left transition-all {{ $c->currentOperator ? 'bg-slate-50 dark:bg-slate-700 border-slate-200 dark:border-slate-600 cursor-not-allowed opacity-60' : 'bg-white dark:bg-slate-700 border-slate-200 dark:border-slate-600 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20' }}"
                                                {{ $c->currentOperator ? 'disabled' : '' }}
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <span class="text-lg font-bold text-slate-900">{{ $c->name }}</span>
                                                        @if($c->currentOperator)
                                                            <span class="block text-sm text-slate-500 mt-1">{{ $c->currentOperator->name }}</span>
                                                        @else
                                                            <span class="block text-sm text-green-600 font-medium mt-1">✓ Tersedia</span>
                                                        @endif
                                                    </div>
                                                    @if(!$c->currentOperator)
                                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                                        </svg>
                                                    @endif
                                                </div>
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Current Ticket Panel -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                            <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Tiket Saat Ini</h2>
                            </div>
                            <div class="p-6">
                                @if($currentTicket)
                                    <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-8 text-center mb-6">
                                        @if($currentTicket->is_priority)
                                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full mb-3">
                                                🚨 PRIORITAS — {{ match($currentTicket->priority_reason) { 'elderly' => 'Lansia', 'pregnant' => 'Ibu Hamil', 'disability' => 'Disabilitas', default => 'Prioritas' } }}
                                            </span>
                                        @endif
                                        <p class="text-blue-600 font-medium mb-2">
                                            @if($currentTicket->status === 'calling')
                                                <span class="inline-flex items-center">
                                                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                                    Memanggil...
                                                </span>
                                            @else
                                                <span class="inline-flex items-center">
                                                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                                                    Sedang Melayani
                                                </span>
                                            @endif
                                        </p>
                                        <p class="text-6xl font-black text-blue-900">{{ $currentTicket->ticket_number }}</p>
                                        @if($currentTicket->customer_name)
                                            <p class="text-blue-600 mt-3 text-lg">{{ $currentTicket->customer_name }}</p>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        @if($currentTicket->status === 'calling')
                                            <form method="POST" action="{{ route('operator.start-serving', $currentTicket) }}">
                                                @csrf
                                                <button type="submit" class="w-full flex items-center justify-center bg-green-600 hover:bg-green-700 text-white py-4 rounded-xl font-semibold transition-colors">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Mulai Layani
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('operator.recall', $currentTicket) }}">
                                                @csrf
                                                <button type="submit" class="w-full flex items-center justify-center bg-amber-500 hover:bg-amber-600 text-white py-4 rounded-xl font-semibold transition-colors">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                    </svg>
                                                    Panggil Ulang
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('operator.complete', $currentTicket) }}">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-semibold transition-colors">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Selesai
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('operator.skip', $currentTicket) }}">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center justify-center bg-red-600 hover:bg-red-700 text-white py-4 rounded-xl font-semibold transition-colors">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                </svg>
                                                Lewati
                                            </button>
                                        </form>
                                    </div>

                                    @if(isset($serviceCategories) && $serviceCategories->count() > 0)
                                        <!-- Transfer Section -->
                                        <div x-data="{ openTransfer: false }" class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
                                            <button @click="openTransfer = !openTransfer" type="button" class="w-full flex items-center justify-center bg-slate-700 hover:bg-slate-800 text-white py-3 rounded-xl font-medium transition-colors text-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                                Transfer Antrian ke Kategori Lain
                                            </button>

                                            <div x-show="openTransfer" x-cloak class="mt-4 p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                                                <form method="POST" action="{{ route('operator.transfer', $currentTicket) }}">
                                                    @csrf
                                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Kategori Tujuan:</label>
                                                    <select name="service_category_id" required class="w-full text-sm border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg mb-3">
                                                        @foreach($serviceCategories as $cat)
                                                            @if($cat->id !== $currentTicket->service_category_id)
                                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <input type="text" name="transfer_reason" placeholder="Alasan transfer (opsional)" class="w-full text-sm border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-lg mb-3">
                                                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg transition-colors">
                                                        Konfirmasi Transfer Tiket
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-12">
                                        <div class="w-20 h-20 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                            <svg class="w-10 h-10 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                        </div>
                                        <p class="text-slate-500 dark:text-slate-400 mb-6">Tidak ada tiket yang sedang dilayani</p>
                                        <form method="POST" action="{{ route('operator.call-next') }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-10 py-5 rounded-2xl font-bold text-lg transition-colors shadow-lg shadow-blue-200">
                                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                </svg>
                                                Panggil Antrian Berikutnya
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Waiting Queue -->
                    <div id="waiting-queue-panel" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Antrian Menunggu</h2>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ $waitingTickets->count() }} tiket dalam antrian</p>
                            </div>
                            @if($waitingTickets->count() > 0)
                                <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-sm font-semibold">
                                    {{ $waitingTickets->count() }}
                                </span>
                            @endif
                        </div>
                        <div class="p-6">
                            @if($waitingTickets->isEmpty())
                                <div class="text-center py-8">
                                    <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-slate-500 dark:text-slate-400">Tidak ada antrian menunggu</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach($waitingTickets->take(10) as $ticket)
                                        <div class="flex items-center justify-between p-4 {{ $ticket->is_priority ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-slate-50 dark:bg-slate-700/60 hover:bg-slate-100 dark:hover:bg-slate-700' }} rounded-xl transition-colors">
                                            <div class="flex items-center space-x-4">
                                                <span class="w-10 h-10 {{ $ticket->is_priority ? 'bg-red-600' : 'bg-blue-600' }} text-white rounded-xl flex items-center justify-center text-sm font-bold">
                                                    {{ $loop->iteration }}
                                                </span>
                                                <div>
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                         <span class="font-bold text-slate-900 dark:text-white text-lg">{{ $ticket->ticket_number }}</span>
                                                         @if($ticket->is_priority)
                                                             <span class="text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 font-semibold px-2 py-0.5 rounded-full">🚨 {{ match($ticket->priority_reason) { 'elderly' => 'Lansia', 'pregnant' => 'Hamil', 'disability' => 'Disabilitas', default => 'Prioritas' } }}</span>
                                                         @endif
                                                         @if($ticket->sla_status === 'overdue')
                                                             <span class="text-xs bg-red-600 text-white font-bold px-2 py-0.5 rounded-full animate-pulse">⚠️ SLA Overdue ({{ $ticket->wait_duration_minutes }}m)</span>
                                                         @elseif($ticket->sla_status === 'warning')
                                                             <span class="text-xs bg-amber-500 text-white font-bold px-2 py-0.5 rounded-full">⏳ SLA Alert ({{ $ticket->wait_duration_minutes }}m)</span>
                                                         @endif
                                                         @if($ticket->transferredFromCategory)
                                                             <span class="text-xs bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 font-medium px-2 py-0.5 rounded-full">↩️ Ditolak/Ditransfer dari {{ $ticket->transferredFromCategory->name }}</span>
                                                         @endif
                                                    </div>
                                                    @if($ticket->customer_name)
                                                        <span class="block text-slate-500 dark:text-slate-400 text-sm">{{ $ticket->customer_name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $ticket->created_at->diffForHumans() }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                @if($waitingTickets->count() > 10)
                                    <p class="text-center text-slate-500 dark:text-slate-400 text-sm mt-4">
                                        +{{ $waitingTickets->count() - 10 }} tiket lainnya
                                    </p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div id="sidebar-panel" class="space-y-6">
                    <!-- Counters Status -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                            <h3 class="font-bold text-slate-900 dark:text-white">Status Loket</h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                @foreach($counters as $c)
                                    <div class="flex items-center justify-between p-3 rounded-xl {{ $c->currentOperator ? 'bg-green-50 dark:bg-green-900/20' : 'bg-slate-50 dark:bg-slate-700/50' }}">
                                        <div>
                                            <span class="font-semibold text-slate-900 dark:text-white">{{ $c->name }}</span>
                                            @if($c->currentOperator)
                                                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $c->currentOperator->name }}</span>
                                            @else
                                                <span class="block text-xs text-slate-400 dark:text-slate-500">Tidak aktif</span>
                                            @endif
                                        </div>
                                        @if($c->currentOperator)
                                            <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                                        @else
                                            <span class="w-3 h-3 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                            <h3 class="font-bold text-slate-900 dark:text-white">Aksi Cepat</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <a href="{{ route('locations.display', $location) }}" target="_blank" class="flex items-center justify-center w-full bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white py-3 rounded-xl font-semibold transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Buka Display
                            </a>
                            <a href="{{ route('locations.show', $location) }}" target="_blank" class="flex items-center justify-center w-full border-2 border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500 text-slate-700 dark:text-slate-300 py-3 rounded-xl font-semibold transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat Halaman Publik
                            </a>

                            <!-- Tombol Darurat Supervisor -->
                            <button id="btn-supervisor" onclick="document.getElementById('modal-supervisor').classList.remove('hidden')" class="flex items-center justify-center w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-semibold transition-colors shadow-sm">
                                🚨 Minta Bantuan Supervisor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Darurat Supervisor -->
    <div id="modal-supervisor" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl border-2 border-red-200 dark:border-red-800">
            <div class="text-center mb-6">
                <div class="text-5xl mb-3">🚨</div>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white">Minta Bantuan Supervisor</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-2">Pesan darurat akan dikirimkan secara real-time kepada supervisor.</p>
            </div>
            <form method="POST" action="{{ route('operator.request-supervisor') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Alasan / Keterangan</label>
                    <textarea name="reason" rows="3" placeholder="Contoh: Terdapat pelanggan komplain / Sistem error di loket..." class="w-full text-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-red-500 focus:border-red-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-colors">
                        🚨 Kirim Permintaan Bantuan
                    </button>
                    <button type="button" onclick="document.getElementById('modal-supervisor').classList.add('hidden')" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-semibold py-3 rounded-xl transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script type="module">
        let refreshTimer;

        // Request browser notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Web Audio API Synthesized Premium Chime Sound
        function playChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const audioCtx = new AudioContext();
                const now = audioCtx.currentTime;

                // Tone 1 (Chime base note)
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(523.25, now); // C5
                osc1.frequency.exponentialRampToValueAtTime(880.00, now + 0.15); // A5
                gain1.gain.setValueAtTime(0.12, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start(now);
                osc1.stop(now + 0.6);

                // Tone 2 (Harmonic note slightly delayed)
                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(659.25, now + 0.1); // E5
                osc2.frequency.exponentialRampToValueAtTime(1046.50, now + 0.25); // C6
                gain2.gain.setValueAtTime(0.1, now + 0.1);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.7);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start(now + 0.1);
                osc2.stop(now + 0.7);
            } catch (e) {
                console.warn('Synthesized chime sound blocked or failed:', e);
            }
        }

        function notifyNewTicket(ticket) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('🚨 Antrian Baru Masuk!', {
                    body: `Tiket ${ticket.ticket_number} telah ditambahkan ke daftar tunggu.`,
                    requireInteraction: false
                });
            }
        }

        async function refreshDashboard() {
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(async () => {
                try {
                    const response = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                    });
                    if (!response.ok) return;
                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');

                    ['stats-section', 'waiting-queue-panel', 'sidebar-panel'].forEach(id => {
                        const newEl = doc.getElementById(id);
                        const oldEl = document.getElementById(id);
                        if (newEl && oldEl) {
                            oldEl.innerHTML = newEl.innerHTML;
                        }
                    });
                } catch (err) {
                    console.warn('Realtime refresh failed:', err);
                }
            }, 400);
        }

        window.Echo.channel('location.{{ $location->id }}')
            .listen('.ticket.created', (e) => {
                playChime();
                notifyNewTicket(e.ticket);
                refreshDashboard();
            })
            .listen('.ticket.status.changed', refreshDashboard)
            .listen('.queue.updated', refreshDashboard);
    </script>
    @endpush
</x-layout>
