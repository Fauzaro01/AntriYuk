<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Antrian — {{ $location->name }}</title>
    <meta name="description" content="Ambil tiket antrian mandiri di {{ $location->name }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes popIn {
            0%   { opacity: 0; transform: scale(0.5); }
            70%  { transform: scale(1.08); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(99,102,241,0.5); }
            70%  { box-shadow: 0 0 0 20px rgba(99,102,241,0); }
            100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); }
        }
        .slide-up  { animation: slideUp 0.5s ease both; }
        .pop-in    { animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) both; }
        .pulse-ring { animation: pulse-ring 2s infinite; }

        .priority-btn:hover { transform: translateY(-4px); }
        .priority-btn { transition: all 0.2s ease; }
        .category-btn:hover { transform: translateY(-4px) scale(1.02); }
        .category-btn { transition: all 0.2s ease; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 min-h-screen text-white overflow-hidden select-none">

    <div x-data="kioskApp()" class="flex flex-col min-h-screen">

        {{-- TOP BAR --}}
        <header class="flex items-center justify-between px-10 py-6 border-b border-white/10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-indigo-500 rounded-2xl flex items-center justify-center text-2xl font-black shadow-lg shadow-indigo-900">
                    🏢
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight">{{ $location->name }}</h1>
                    <p class="text-indigo-300 text-sm font-medium">{{ __('Sistem Antrian Mandiri') }}</p>
                </div>
            </div>
            
            {{-- Language Selector in Kiosk --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('lang.switch', 'id') }}" class="px-3 py-1.5 rounded-lg border {{ App::getLocale() === 'id' ? 'border-indigo-400 bg-indigo-500/20 text-white font-bold' : 'border-white/10 text-indigo-300 hover:text-white hover:border-white/20' }} text-sm transition-all">
                    🇮🇩 ID
                </a>
                <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1.5 rounded-lg border {{ App::getLocale() === 'en' ? 'border-indigo-400 bg-indigo-500/20 text-white font-bold' : 'border-white/10 text-indigo-300 hover:text-white hover:border-white/20' }} text-sm transition-all">
                    🇺🇸 EN
                </a>
            </div>

            <div class="text-right">
                <p class="text-indigo-300 text-sm">{{ __('Antrian Saat Ini') }}</p>
                <p class="text-4xl font-black text-white" id="kiosk-waiting-count">{{ $waitingCount }}</p>
                <p class="text-indigo-300 text-sm">{{ __('orang menunggu') }}</p>
            </div>
        </header>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 flex items-center justify-center px-10 py-8">

            {{-- IDLE STATE --}}
            <div x-show="step === 'idle'" x-cloak
                 class="text-center slide-up"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
            >
                <div class="w-48 h-48 bg-indigo-500/20 rounded-full flex items-center justify-center mx-auto mb-10 pulse-ring">
                    <div class="w-36 h-36 bg-indigo-500/30 rounded-full flex items-center justify-center">
                        <span class="text-7xl">🎫</span>
                    </div>
                </div>
                <h2 class="text-5xl font-black mb-4">{{ __('Ambil Tiket') }}</h2>
                <p class="text-indigo-300 text-xl mb-12">{{ __('Sentuh layar untuk mulai') }}</p>
                <button
                    @click="step = 'priority'"
                    class="bg-indigo-500 hover:bg-indigo-400 text-white text-2xl font-black px-16 py-6 rounded-3xl shadow-2xl shadow-indigo-900 transition-all hover:scale-105 active:scale-95"
                >
                    {{ __('Mulai Ambil Tiket') }} →
                </button>
            </div>

            {{-- STEP 1: PRIORITY SELECTION --}}
            <div x-show="step === 'priority'" x-cloak class="w-full max-w-3xl slide-up">
                <h2 class="text-4xl font-black text-center mb-3">{{ __('Apakah Anda memerlukan layanan prioritas?') }}</h2>
                <p class="text-indigo-300 text-center text-lg mb-10">{{ __('Tiket prioritas akan didahulukan dalam antrian') }}</p>

                <div class="grid grid-cols-4 gap-5 mb-10">
                    {{-- No Priority --}}
                    <button
                        @click="priority = ''; step = 'category'"
                        class="priority-btn flex flex-col items-center gap-3 bg-white/10 hover:bg-white/20 backdrop-blur border border-white/20 rounded-3xl p-8 text-center"
                    >
                        <span class="text-5xl">🙋</span>
                        <span class="font-bold text-lg">{{ __('Tidak Ada') }}</span>
                        <span class="text-sm text-indigo-300">{{ __('Antrian biasa') }}</span>
                    </button>

                    {{-- Elderly --}}
                    <button
                        @click="priority = 'elderly'; step = 'category'"
                        class="priority-btn flex flex-col items-center gap-3 bg-orange-500/20 hover:bg-orange-500/30 backdrop-blur border border-orange-500/40 rounded-3xl p-8 text-center"
                    >
                        <span class="text-5xl">🧓</span>
                        <span class="font-bold text-lg text-orange-300">{{ __('Lansia') }}</span>
                        <span class="text-sm text-orange-300/70">{{ __('Usia 60+') }}</span>
                    </button>

                    {{-- Pregnant --}}
                    <button
                        @click="priority = 'pregnant'; step = 'category'"
                        class="priority-btn flex flex-col items-center gap-3 bg-pink-500/20 hover:bg-pink-500/30 backdrop-blur border border-pink-500/40 rounded-3xl p-8 text-center"
                    >
                        <span class="text-5xl">🤰</span>
                        <span class="font-bold text-lg text-pink-300">{{ __('Ibu Hamil') }}</span>
                        <span class="text-sm text-pink-300/70">{{ __('Prioritas khusus') }}</span>
                    </button>

                    {{-- Disability --}}
                    <button
                        @click="priority = 'disability'; step = 'category'"
                        class="priority-btn flex flex-col items-center gap-3 bg-blue-500/20 hover:bg-blue-500/30 backdrop-blur border border-blue-500/40 rounded-3xl p-8 text-center"
                    >
                        <span class="text-5xl">♿</span>
                        <span class="font-bold text-lg text-blue-300">{{ __('Disabilitas') }}</span>
                        <span class="text-sm text-blue-300/70">{{ __('Berkebutuhan khusus') }}</span>
                    </button>
                </div>

                <button @click="step = 'idle'" class="w-full py-4 rounded-2xl border border-white/20 text-white/60 hover:text-white hover:border-white/40 transition-all font-semibold text-lg">
                    ← {{ __('Kembali') }}
                </button>
            </div>

            {{-- STEP 2: CATEGORY SELECTION --}}
            <div x-show="step === 'category'" x-cloak class="w-full max-w-3xl slide-up">
                @if($serviceCategories->count() > 0)
                    <h2 class="text-4xl font-black text-center mb-3">{{ __('Pilih Kategori Layanan') }}</h2>
                    <p class="text-indigo-300 text-center text-lg mb-10">{{ __('Pilih layanan yang Anda butuhkan') }}</p>

                    <div class="grid grid-cols-{{ $serviceCategories->count() > 3 ? '3' : $serviceCategories->count() }} gap-5 mb-8">
                        @foreach($serviceCategories as $cat)
                        <button
                            @click="categoryId = {{ $cat->id }}; step = 'confirm'"
                            class="category-btn flex flex-col items-center gap-3 bg-white/10 hover:bg-indigo-500/40 backdrop-blur border border-white/20 hover:border-indigo-400 rounded-3xl p-8 text-center"
                        >
                            <span class="text-5xl">🔖</span>
                            <span class="font-bold text-xl">{{ $cat->name }}</span>
                            @if($cat->description)
                                <span class="text-sm text-indigo-300">{{ $cat->description }}</span>
                            @endif
                        </button>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <button @click="categoryId = null; step = 'confirm'" class="py-4 rounded-2xl border border-white/20 text-white/80 hover:text-white hover:bg-white/10 transition-all font-semibold text-lg">
                            {{ __('Lewati Kategori') }} →
                        </button>
                        <button @click="step = 'priority'" class="py-4 rounded-2xl border border-white/20 text-white/60 hover:text-white hover:border-white/40 transition-all font-semibold text-lg">
                            ← {{ __('Kembali') }}
                        </button>
                    </div>
                @else
                    {{-- Skip directly to confirm if no categories --}}
                    <div x-init="categoryId = null; step = 'confirm'"></div>
                @endif
            </div>

            {{-- STEP 3: CONFIRM --}}
            <div x-show="step === 'confirm'" x-cloak class="w-full max-w-lg slide-up text-center">
                <h2 class="text-4xl font-black mb-3">{{ __('Konfirmasi Tiket') }}</h2>
                <p class="text-indigo-300 text-lg mb-10">{{ __('Periksa detail tiket Anda sebelum mengambil') }}</p>

                <div class="bg-white/10 backdrop-blur border border-white/20 rounded-3xl p-8 mb-8 text-left space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-white/10">
                        <span class="text-indigo-300 font-medium">{{ __('Lokasi') }}</span>
                        <span class="font-bold text-xl">{{ $location->name }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-white/10">
                        <span class="text-indigo-300 font-medium">{{ __('Kategori Layanan') }}</span>
                        <span class="font-bold text-xl" x-text="getCategoryName()">—</span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-indigo-300 font-medium">{{ __('Tipe Antrian') }}</span>
                        <span class="font-bold text-xl" x-text="priority ? '🚨 ' + '{{ __('Prioritas') }}' + ' (' + getPriorityLabel() + ')' : '{{ __('Antrian Biasa') }}'">Biasa</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <button @click="step = {{ $serviceCategories->count() > 0 ? "'category'" : "'priority'" }}"
                        class="py-5 rounded-2xl border border-white/20 text-white/60 hover:text-white hover:border-white/40 transition-all font-bold text-lg">
                        ← {{ __('Kembali') }}
                    </button>
                    <button @click="submitTicket()" :disabled="submitting"
                        class="py-5 rounded-2xl bg-indigo-500 hover:bg-indigo-400 disabled:opacity-50 text-white font-black text-xl transition-all hover:scale-105 active:scale-95 shadow-xl shadow-indigo-900"
                    >
                        <span x-show="!submitting">🎫 {{ __('Ambil Tiket!') }}</span>
                        <span x-show="submitting">{{ __('Memproses...') }}</span>
                    </button>
                </div>
            </div>

            {{-- STEP 4: SUCCESS --}}
            <div x-show="step === 'success'" x-cloak class="w-full max-w-lg text-center pop-in">
                <div class="text-9xl mb-6"></div>
                <h2 class="text-4xl font-black mb-2">{{ __('Tiket Diambil!') }}</h2>
                <p class="text-indigo-300 text-lg mb-8">{{ __('Simpan nomor tiket Anda di bawah ini') }}</p>

                <div class="bg-indigo-500 rounded-3xl p-10 mb-8 shadow-2xl shadow-indigo-900">
                    <p class="text-indigo-200 text-sm font-semibold uppercase tracking-widest mb-2">{{ __('Nomor Tiket Anda') }}</p>
                    <p class="text-7xl font-black tracking-tight" x-text="ticketNumber">—</p>
                    <p class="text-indigo-200 mt-4 text-lg" x-text="ticketLocation"></p>
                </div>

                <p class="text-indigo-300 text-lg mb-6">{{ __('Halaman akan reset dalam') }} <span class="font-bold text-white" x-text="countdown"></span> {{ __('detik') }}</p>

                <button @click="resetKiosk()" class="w-full py-5 rounded-2xl border-2 border-indigo-500 text-indigo-300 hover:text-white hover:bg-indigo-500/30 transition-all font-bold text-lg">
                    {{ __('Ambil Tiket Lain') }}
                </button>
            </div>

            {{-- STEP: ERROR --}}
            <div x-show="step === 'error'" x-cloak class="w-full max-w-lg text-center slide-up">
                <div class="text-8xl mb-6">⚠️</div>
                <h2 class="text-3xl font-black mb-3 text-red-400">{{ __('Terjadi Kesalahan') }}</h2>
                <p class="text-indigo-300 mb-8" x-text="errorMessage">Gagal mengambil tiket.</p>
                <button @click="step = 'idle'" class="bg-indigo-500 hover:bg-indigo-400 text-white font-bold py-4 px-10 rounded-2xl transition-all">
                    {{ __('Coba Lagi') }}
                </button>
            </div>
        </main>

        {{-- FOOTER CLOCK --}}
        <footer class="px-10 py-5 border-t border-white/10 flex items-center justify-between text-indigo-400">
            <p class="text-sm">Kiosk Mandiri — {{ $location->name }}</p>
            <p class="font-mono text-lg font-bold" id="kiosk-clock"></p>
        </footer>
    </div>

    {{-- Hidden Form for ticket submission --}}
    <form id="kiosk-form" method="POST" action="{{ route('tickets.store', $location) }}" style="display:none">
        @csrf
        <input type="hidden" name="is_priority" id="kf-is-priority">
        <input type="hidden" name="priority_reason" id="kf-priority-reason">
        <input type="hidden" name="service_category_id" id="kf-category-id">
    </form>

    {{-- Hidden Iframe for automatic thermal printing --}}
    <iframe id="print-iframe" style="display:none"></iframe>

    <script>
        // Clock updater
        function updateClock() {
            document.getElementById('kiosk-clock').textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Category map for display
        const categoryMap = {
            @foreach($serviceCategories as $cat)
            {{ $cat->id }}: '{{ $cat->name }}',
            @endforeach
        };

        function kioskApp() {
            return {
                step: 'idle',
                priority: '',
                categoryId: null,
                submitting: false,
                ticketNumber: '',
                ticketLocation: '',
                errorMessage: '',
                countdown: 15,
                countdownInterval: null,

                getCategoryName() {
                    if (!this.categoryId) return '—';
                    return categoryMap[this.categoryId] || '—';
                },

                getPriorityLabel() {
                    const labels = { elderly: 'Lansia', pregnant: 'Ibu Hamil', disability: 'Disabilitas' };
                    return labels[this.priority] || this.priority;
                },

                async submitTicket() {
                    this.submitting = true;
                    try {
                        const form = document.getElementById('kiosk-form');
                        document.getElementById('kf-is-priority').value = this.priority ? '1' : '';
                        document.getElementById('kf-priority-reason').value = this.priority || '';
                        document.getElementById('kf-category-id').value = this.categoryId || '';

                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            redirect: 'follow',
                        });

                        // Extract ticket number from redirect URL
                        if (response.redirected) {
                            const urlParts = response.url.split('/');
                            const ticketId = urlParts[urlParts.length - 1];
                            // Fetch the ticket page to extract number
                            const ticketResp = await fetch(response.url, { headers: { 'Accept': 'text/html' } });
                            const html = await ticketResp.text();
                            const match = html.match(/<h1[^>]*class="[^"]*text-6xl[^"]*"[^>]*>([^<]+)<\/h1>/);
                            this.ticketNumber = match ? match[1].trim() : ticketId;
                            this.ticketLocation = '{{ $location->name }}';
                            this.step = 'success';

                            // Trigger hidden thermal printer print dialog
                            const printIframe = document.getElementById('print-iframe');
                            if (printIframe) {
                                printIframe.src = `/tickets/${ticketId}/print`;
                            }

                            this.startCountdown();
                        } else {
                            throw new Error('Gagal memproses permintaan.');
                        }
                    } catch (err) {
                        this.errorMessage = err.message || 'Terjadi kesalahan. Silakan coba lagi.';
                        this.step = 'error';
                    } finally {
                        this.submitting = false;
                    }
                },

                startCountdown() {
                    this.countdown = 15;
                    clearInterval(this.countdownInterval);
                    this.countdownInterval = setInterval(() => {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            clearInterval(this.countdownInterval);
                            this.resetKiosk();
                        }
                    }, 1000);
                },

                resetKiosk() {
                    clearInterval(this.countdownInterval);
                    this.step = 'idle';
                    this.priority = '';
                    this.categoryId = null;
                    this.ticketNumber = '';
                    this.errorMessage = '';
                    this.submitting = false;
                    // Refresh waiting count
                    fetch(window.location.href).then(r => r.text()).then(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const count = doc.getElementById('kiosk-waiting-count');
                        if (count) document.getElementById('kiosk-waiting-count').textContent = count.textContent;
                    });
                }
            };
        }
    </script>
</body>
</html>
