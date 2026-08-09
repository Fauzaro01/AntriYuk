<x-layout :title="'Tiket ' . $ticket->ticket_number">
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-8">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li><a href="{{ route('home') }}" class="text-slate-500 dark:text-slate-400 hover:text-blue-600">Beranda</a></li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-slate-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-slate-900 dark:text-white font-medium">Tiket {{ $ticket->ticket_number }}</span>
                    </li>
                </ol>
            </nav>

            <!-- Main Ticket Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
                <!-- Ticket Header with Ticket Number -->
                <div id="ticket-download-card" class="relative bg-blue-600 text-white p-8 text-center">
                    <!-- Decorative Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                <circle cx="5" cy="5" r="1" fill="currentColor"/>
                            </pattern>
                            <rect width="100" height="100" fill="url(#grid)"/>
                        </svg>
                    </div>
                    
                    <div class="relative">
                        <p class="text-blue-200 text-sm font-medium uppercase tracking-wider mb-2">{{ $ticket->location->name }}</p>
                        <h1 class="text-6xl font-black tracking-tight">{{ $ticket->ticket_number }}</h1>
                        @if($ticket->customer_name)
                            <p class="text-blue-200 mt-3 text-lg">{{ $ticket->customer_name }}</p>
                        @endif
                    </div>

                    <!-- Ticket Punch Holes -->
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1/2">
                        <div class="w-8 h-8 bg-slate-50 dark:bg-slate-900 rounded-full"></div>
                    </div>
                    <div class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2">
                        <div class="w-8 h-8 bg-slate-50 dark:bg-slate-900 rounded-full"></div>
                    </div>
                </div>

                <!-- Dashed Line Separator -->
                <div class="px-6">
                    <div class="border-t-2 border-dashed border-slate-200 dark:border-slate-700"></div>
                </div>

                <!-- Status Section -->
                <div id="ticket-status-section" class="p-6">
                    @if(!in_array($ticket->status, ['skipped', 'cancelled']))
                        <!-- Progress Stepper -->
                        <div class="mb-8 px-2">
                            <div class="flex items-center justify-between relative">
                                <!-- Background line -->
                                <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-slate-200 dark:bg-slate-700 -z-10 rounded-full"></div>
                                <!-- Active progress line -->
                                @php
                                    $progressWidth = '0%';
                                    if ($ticket->status === 'waiting') $progressWidth = '15%';
                                    elseif ($ticket->status === 'calling') $progressWidth = '50%';
                                    elseif ($ticket->status === 'serving') $progressWidth = '85%';
                                    elseif ($ticket->status === 'completed') $progressWidth = '100%';
                                @endphp
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-blue-600 dark:bg-blue-500 -z-10 rounded-full transition-all duration-500" style="width: {{ $progressWidth }}"></div>

                                <!-- Step 1: Waiting -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs border-2 transition-colors duration-300
                                        {{ in_array($ticket->status, ['waiting', 'calling', 'serving', 'completed']) ? 'bg-blue-600 border-blue-600 dark:bg-blue-500 dark:border-blue-500 text-white' : 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-500' }}">
                                        1
                                    </div>
                                    <span class="text-xs mt-1.5 font-medium text-slate-600 dark:text-slate-400">Antri</span>
                                </div>

                                <!-- Step 2: Calling -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs border-2 transition-colors duration-300
                                        {{ in_array($ticket->status, ['calling', 'serving', 'completed']) ? 'bg-blue-600 border-blue-600 dark:bg-blue-500 dark:border-blue-500 text-white' : ($ticket->status === 'waiting' ? 'bg-white dark:bg-slate-800 border-blue-600 dark:border-blue-500 text-blue-600 dark:text-blue-400' : 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-500') }}">
                                        2
                                    </div>
                                    <span class="text-xs mt-1.5 font-medium text-slate-600 dark:text-slate-400">Dipanggil</span>
                                </div>

                                <!-- Step 3: Serving -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs border-2 transition-colors duration-300
                                        {{ in_array($ticket->status, ['serving', 'completed']) ? 'bg-blue-600 border-blue-600 dark:bg-blue-500 dark:border-blue-500 text-white' : ($ticket->status === 'calling' ? 'bg-white dark:bg-slate-800 border-blue-600 dark:border-blue-500 text-blue-600 dark:text-blue-400 animate-pulse' : 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-500') }}">
                                        3
                                    </div>
                                    <span class="text-xs mt-1.5 font-medium text-slate-600 dark:text-slate-400">Dilayani</span>
                                </div>

                                <!-- Step 4: Completed -->
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs border-2 transition-colors duration-300
                                        {{ $ticket->status === 'completed' ? 'bg-green-600 border-green-600 text-white' : ($ticket->status === 'serving' ? 'bg-white dark:bg-slate-800 border-blue-600 dark:border-blue-500 text-blue-600 dark:text-blue-400' : 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-500') }}">
                                        ✓
                                    </div>
                                    <span class="text-xs mt-1.5 font-medium text-slate-600 dark:text-slate-400">Selesai</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="text-center mb-6">
                        @if($ticket->status === 'waiting')
                            <div class="inline-flex items-center bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 px-6 py-3 rounded-2xl">
                                <div class="w-3 h-3 bg-amber-500 rounded-full mr-3 animate-pulse"></div>
                                <span class="font-semibold text-lg">Menunggu Dipanggil</span>
                            </div>
                        @elseif($ticket->status === 'calling')
                            <div class="inline-flex items-center bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-6 py-3 rounded-2xl animate-pulse">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="font-bold text-lg">DIPANGGIL - {{ $ticket->counter?->name }}</span>
                            </div>
                            <p class="mt-4 text-green-700 dark:text-green-400 font-medium animate-bounce">🔔 Silakan menuju {{ $ticket->counter?->name }}!</p>
                        @elseif($ticket->status === 'serving')
                            <div class="inline-flex items-center bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 px-6 py-3 rounded-2xl">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-semibold text-lg">Sedang Dilayani - {{ $ticket->counter?->name }}</span>
                            </div>
                        @elseif($ticket->status === 'completed')
                            <div class="inline-flex items-center bg-slate-100 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 px-6 py-3 rounded-2xl">
                                <svg class="w-6 h-6 mr-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold text-lg">Selesai</span>
                            </div>
                        @elseif($ticket->status === 'skipped')
                            <div class="inline-flex items-center bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-6 py-3 rounded-2xl">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold text-lg">Dilewati</span>
                            </div>
                            <p class="mt-4 text-red-600 dark:text-red-400 text-sm">Tiket Anda dilewati karena tidak hadir saat dipanggil.</p>
                        @elseif($ticket->status === 'cancelled')
                            <div class="inline-flex items-center bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-6 py-3 rounded-2xl">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="font-semibold text-lg">Dibatalkan</span>
                            </div>
                        @endif
                    </div>

                    @if($ticket->status === 'waiting')
                        <!-- Position & Estimated Time Cards -->
                        <div id="position-section" class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-5 text-center border border-blue-100 dark:border-blue-800">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <p class="text-slate-600 dark:text-slate-400 text-sm mb-1">Posisi Antrian</p>
                                <p class="text-4xl font-black text-blue-600 dark:text-blue-400" id="position">{{ $ticket->position }}</p>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-2xl p-5 text-center border border-green-100 dark:border-green-800">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-slate-600 dark:text-slate-400 text-sm mb-1">Estimasi Waktu</p>
                                <p class="text-4xl font-black text-green-600 dark:text-green-400" id="estimated-time">~{{ $ticket->estimated_wait_time }}</p>
                                <p class="text-slate-500 dark:text-slate-400 text-xs">menit</p>
                            </div>
                        </div>

                        <!-- Progress hint -->
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl p-4 mb-6">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="text-amber-800 dark:text-amber-300 text-sm font-medium">Tetap pantau halaman ini</p>
                                    <p class="text-amber-700 dark:text-amber-400 text-sm mt-1">Anda akan mendapat notifikasi saat giliran tiba.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Ticket Details -->
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white uppercase tracking-wider mb-4">Detail Tiket</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                <dt class="text-slate-500 dark:text-slate-400 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    Lokasi
                                </dt>
                                <dd class="font-semibold text-slate-900 dark:text-white">{{ $ticket->location->name }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                <dt class="text-slate-500 dark:text-slate-400 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Waktu Ambil
                                </dt>
                                <dd class="font-semibold text-slate-900 dark:text-white">{{ $ticket->created_at->format('H:i, d M Y') }}</dd>
                            </div>
                            @if($ticket->called_at)
                                <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-700">
                                    <dt class="text-slate-500 dark:text-slate-400 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        Waktu Dipanggil
                                    </dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">{{ $ticket->called_at->format('H:i') }}</dd>
                                </div>
                            @endif
                            @if($ticket->completed_at)
                                <div class="flex justify-between items-center py-2">
                                    <dt class="text-slate-500 dark:text-slate-400 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Waktu Selesai
                                    </dt>
                                    <dd class="font-semibold text-slate-900 dark:text-white">{{ $ticket->completed_at->format('H:i') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-slate-50 dark:bg-slate-900/50 p-6 border-t border-slate-200 dark:border-slate-700">
                    <!-- QR Code -->
                    <div class="text-center mb-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Scan QR untuk cek status</p>
                        <div id="qrcode" class="inline-block"></div>
                    </div>

                    <!-- Download & View buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button
                            id="btn-download-ticket"
                            onclick="downloadTicketCard()"
                            class="flex-1 flex items-center justify-center gap-2 bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white py-3 px-4 rounded-xl font-semibold transition-all active:scale-95"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Simpan Tiket
                        </button>

                        <a href="{{ route('locations.show', $ticket->location) }}" class="flex-1 flex items-center justify-center gap-2 border-2 border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 py-3 px-4 rounded-xl font-semibold transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat Antrian
                        </a>
                    </div>

                    @if($ticket->status === 'waiting')
                        <div x-data="{ open: false }" class="mt-4 border-t border-slate-100 dark:border-slate-700/60 pt-4">
                            <button
                                @click="open = true"
                                class="w-full bg-red-50 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center justify-center gap-1.5"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Batalkan Tiket
                            </button>

                            {{-- Confirm Modal --}}
                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                                <div @click.away="open = false" class="bg-white dark:bg-slate-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 dark:border-slate-700 text-left">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Batalkan Tiket?</h3>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Apakah Anda yakin ingin membatalkan tiket ini? Tindakan ini tidak dapat dibatalkan.</p>
                                    <div class="flex justify-end gap-3">
                                        <button @click="open = false" type="button" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                            Batal
                                        </button>
                                        <form method="POST" action="{{ route('tickets.cancel-self', $ticket) }}">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors">
                                                Ya, Batalkan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($ticket->status === 'completed' && !$ticket->feedback)
                    <!-- Feedback Form -->
                    <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-blue-50 dark:bg-blue-900/20">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 text-center">⭐ Berikan Penilaian</h3>
                        <form method="POST" action="{{ route('feedback.store', $ticket) }}" x-data="{ rating: 0, hoverRating: 0 }">
                            @csrf
                            <div class="text-center mb-4">
                                <div class="inline-flex space-x-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button"
                                            @click="rating = {{ $i }}"
                                            @mouseenter="hoverRating = {{ $i }}"
                                            @mouseleave="hoverRating = 0"
                                            class="text-4xl transition-transform hover:scale-110 focus:outline-none"
                                            :class="(hoverRating || rating) >= {{ $i }} ? 'text-yellow-400' : 'text-slate-300 dark:text-slate-600'">
                                            ★
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" :value="rating">
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2" x-show="rating > 0" x-text="['', 'Sangat Buruk', 'Buruk', 'Cukup', 'Baik', 'Sangat Baik'][rating]"></p>
                            </div>
                            <div class="mb-4">
                                <textarea name="comment" rows="2" placeholder="Komentar (opsional)..."
                                    class="w-full px-4 py-3 border-2 border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400"></textarea>
                            </div>
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition-colors disabled:opacity-50"
                                :disabled="rating === 0">
                                Kirim Feedback
                            </button>
                        </form>
                    </div>
                @endif

                @if($ticket->feedback)
                    <div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-green-50 dark:bg-green-900/20 text-center">
                        <p class="text-green-800 dark:text-green-300 font-semibold mb-1">✅ Terima kasih atas feedback Anda!</p>
                        <div class="text-2xl">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $ticket->feedback->rating ? 'text-yellow-400' : 'text-slate-300 dark:text-slate-600' }}">★</span>
                            @endfor
                        </div>
                        @if($ticket->feedback->comment)
                            <p class="text-slate-600 dark:text-slate-400 text-sm mt-2 italic">"{{ $ticket->feedback->comment }}"</p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Bookmark Notice -->
            @if($ticket->status === 'waiting')
                <div class="mt-6 text-center">
                    <p class="text-slate-500 dark:text-slate-400 text-sm flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        Simpan halaman ini untuk memantau status antrian Anda
                    </p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script type="module">
        let refreshTimer;

        async function refreshTicketStatus(playSound = false, notifyData = null) {
            // Play notification if needed before refresh
            if (playSound && window.playNotificationSound) {
                window.playNotificationSound();
            }
            if (notifyData && Notification.permission === 'granted') {
                new Notification('🔔 Giliran Anda!', {
                    body: `Tiket ${notifyData.ticket_number} dipanggil ke ${notifyData.counter_name}`,
                    icon: '/favicon.ico',
                    requireInteraction: true,
                });
            }

            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(async () => {
                try {
                    const response = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                    });
                    if (!response.ok) return;
                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');

                    ['ticket-status-section'].forEach(id => {
                        const newEl = doc.getElementById(id);
                        const oldEl = document.getElementById(id);
                        if (newEl && oldEl) {
                            oldEl.innerHTML = newEl.innerHTML;
                        }
                    });
                } catch (err) {
                    console.warn('Realtime refresh failed:', err);
                }
            }, 300);
        }

        window.Echo.channel('ticket.{{ $ticket->id }}')
            .listen('.ticket.called', (e) => {
                refreshTicketStatus(true, {
                    ticket_number: e.ticket?.ticket_number,
                    counter_name: e.counter?.name ?? 'loket'
                });
            })
            .listen('.ticket.status.changed', (e) => {
                refreshTicketStatus();
            });

        window.Echo.channel('location.{{ $ticket->location_id }}')
            .listen('.queue.updated', (e) => {
                refreshTicketStatus();
            });

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Save ticket to localStorage for "My Tickets" quick access
        (function saveMyTicket() {
            try {
                const ticketData = {
                    id:       {{ $ticket->id }},
                    number:   '{{ $ticket->ticket_number }}',
                    location: '{{ $ticket->location->name }}',
                    status:   '{{ $ticket->status }}',
                    url:      '{{ route("tickets.show", $ticket) }}',
                    date:     new Date().toDateString(),
                };

                const raw = localStorage.getItem('antriyuk_my_tickets');
                let tickets = raw ? JSON.parse(raw) : [];

                // Remove outdated entries (older than today) to keep storage lean
                const today = new Date().toDateString();
                tickets = tickets.filter(t => t.date === today);

                // Update if exists, otherwise push
                const idx = tickets.findIndex(t => t.id === ticketData.id);
                if (idx > -1) {
                    tickets[idx] = ticketData;
                } else {
                    tickets.push(ticketData);
                }

                // Keep max 20 tickets per day
                if (tickets.length > 20) tickets = tickets.slice(-20);

                localStorage.setItem('antriyuk_my_tickets', JSON.stringify(tickets));
            } catch (e) {}
        })();

        // Generate QR Code of the current ticket URL
        document.addEventListener('DOMContentLoaded', () => {
            const qrEl = document.getElementById('qrcode');
            if (qrEl && window.QRCode) {
                new QRCode(qrEl, {
                    text: "{{ route('tickets.show', $ticket) }}",
                    width: 128,
                    height: 128,
                    colorDark : "#0f172a",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.M
                });
            }
        });
    </script>

    <script>
        async function downloadTicketCard() {
            const btn = document.getElementById('btn-download-ticket');
            const cardEl = document.getElementById('ticket-download-card');
            if (!cardEl || !window.html2canvas) return;

            const originalText = btn.innerHTML;
            btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Menyiapkan...';
            btn.disabled = true;

            try {
                const canvas = await html2canvas(cardEl, {
                    scale: 3,
                    useCORS: true,
                    backgroundColor: null,
                    logging: false,
                });

                const link = document.createElement('a');
                link.download = 'tiket-{{ $ticket->ticket_number }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tersimpan!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 2000);
            } catch (err) {
                console.error('Download error:', err);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
    @endpush
</x-layout>
