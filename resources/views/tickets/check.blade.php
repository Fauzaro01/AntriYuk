<x-layout title="Cek Antrian">
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-12">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl dark:shadow-slate-900/50 overflow-hidden">
                <!-- Header -->
                <div class="bg-slate-900 text-white p-8 text-center">
                    <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold">Cek Status Antrian</h1>
                    <p class="text-slate-400 mt-2">Masukkan nomor tiket untuk melihat status antrian Anda</p>
                </div>

                <!-- Form -->
                <div class="p-8">
                    <form method="GET" action="{{ route('tickets.check') }}">
                        <div class="mb-6">
                            <label for="ticket_number" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Nomor Tiket
                            </label>
                            <input
                                type="text"
                                id="ticket_number"
                                name="ticket_number"
                                value="{{ request('ticket_number') }}"
                                class="w-full px-6 py-4 border-2 border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-2xl font-bold tracking-widest uppercase transition-colors"
                                placeholder="CS-001"
                                required
                                autofocus
                            >
                            <p class="text-slate-500 dark:text-slate-400 text-sm text-center mt-2">Format: KODE-NOMOR (contoh: CS-001)</p>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-blue-200 transition-all hover:shadow-xl flex items-center justify-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cek Status
                        </button>
                    </form>
                </div>

                <!-- My Recent Tickets (from localStorage) -->
                <div id="my-tickets-section" class="hidden">
                    <div class="px-8">
                        <div class="border-t border-slate-200 dark:border-slate-700"></div>
                    </div>
                    <div class="p-8">
                        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Tiket Saya Hari Ini
                        </h2>
                        <div id="my-tickets-list" class="space-y-3"></div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="px-8">
                    <div class="border-t border-slate-200 dark:border-slate-700"></div>
                </div>

                <!-- CTA -->
                <div class="p-8 bg-slate-50 dark:bg-slate-900/50 text-center">
                    <p class="text-slate-600 dark:text-slate-400 mb-4">Belum punya tiket?</p>
                    <a href="{{ route('locations.index') }}" class="inline-flex items-center bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
                        Ambil tiket baru
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const today = new Date().toDateString();
            let myTickets = [];

            try {
                const raw = localStorage.getItem('antriyuk_my_tickets');
                if (raw) {
                    const all = JSON.parse(raw);
                    // Only show today's tickets
                    myTickets = all.filter(t => t.date === today);
                }
            } catch (e) {}

            const section = document.getElementById('my-tickets-section');
            const list = document.getElementById('my-tickets-list');

            if (myTickets.length === 0) {
                section.classList.add('hidden');
                return;
            }

            section.classList.remove('hidden');

            // Status badge colour mapping
            const statusColour = {
                waiting:   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                calling:   'bg-blue-100   text-blue-800   dark:bg-blue-900/30   dark:text-blue-300',
                serving:   'bg-green-100  text-green-800  dark:bg-green-900/30  dark:text-green-300',
                completed: 'bg-slate-100  text-slate-600  dark:bg-slate-700     dark:text-slate-400',
                skipped:   'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                cancelled: 'bg-red-100    text-red-800    dark:bg-red-900/30    dark:text-red-300',
            };
            const statusLabel = {
                waiting:   'Menunggu',
                calling:   'Dipanggil',
                serving:   'Dilayani',
                completed: 'Selesai',
                skipped:   'Dilewati',
                cancelled: 'Dibatalkan',
            };

            // Render recent tickets (newest first, max 5)
            [...myTickets].reverse().slice(0, 5).forEach(t => {
                const colour = statusColour[t.status] ?? statusColour.waiting;
                const label  = statusLabel[t.status]  ?? 'Menunggu';

                const div = document.createElement('a');
                div.href = t.url;
                div.className = 'flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-blue-400 transition-all group';
                div.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-sm shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">${t.number}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">${t.location}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium ${colour}">${label}</span>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                `;
                list.appendChild(div);
            });
        });
    </script>
</x-layout>

