<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="30">

    <title>Display - {{ $location->name }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
    <div id="audio-setup-overlay" class="fixed inset-0 bg-slate-950/95 flex items-center justify-center z-50 transition-opacity duration-500">
        <div class="bg-slate-800 p-8 rounded-2xl border border-slate-700 max-w-md w-full mx-4 text-center shadow-2xl">
            <div class="w-20 h-20 bg-blue-500/10 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .9-1.077 1.34-1.707.707L5.586 15z" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-3">Aktifkan Suara Display</h3>
            <p class="text-slate-400 mb-6 text-sm leading-relaxed">
                Browser memerlukan interaksi pengguna satu kali untuk mengaktifkan suara bel ("ding") dan panggilan suara (Text-to-Speech) pada layar display antrian ini.
            </p>
            <button onclick="startDisplay()" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/25 transition-all">
                Mulai Layar & Aktifkan Audio
            </button>
        </div>
    </div>

    <script>
        // Audio and display start helper
        window.audioActivated = false;

        function startDisplay() {
            // Unblock audio
            const audio = new Audio('/sounds/ding.mp3');
            audio.play().catch(() => {});
            
            if ('speechSynthesis' in window) {
                const welcome = new SpeechSynthesisUtterance("Sistem suara aktif");
                welcome.lang = "id-ID";
                welcome.volume = 0; // Silent welcome to activate TTS permission
                window.speechSynthesis.speak(welcome);
            }

            window.audioActivated = true;
            sessionStorage.setItem('displayAudioActivated', 'true');

            const overlay = document.getElementById('audio-setup-overlay');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 500);
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('displayAudioActivated') === 'true') {
                const overlay = document.getElementById('audio-setup-overlay');
                if (overlay) overlay.style.display = 'none';
                window.audioActivated = true;
            }
        });

        // TTS Message generator for Indonesian queue calls
        function formatTTSMessage(ticketNumber, counterName) {
            const parts = ticketNumber.split('-');
            const letters = parts[0] || '';
            const numbers = parts[1] || '';

            const spelledLetters = letters.split('').join(' ');

            const digitMap = {
                '0': 'kosong', '1': 'satu', '2': 'dua', '3': 'tiga', '4': 'empat',
                '5': 'lima', '6': 'enam', '7': 'tujuh', '8': 'delapan', '9': 'sembilan'
            };
            const spelledNumbers = numbers.split('').map(char => digitMap[char] || char).join(' ');

            let formattedCounter = counterName || 'loket';
            formattedCounter = formattedCounter
                .replace(/CS/i, 'C S')
                .replace(/PAY/i, 'P A Y')
                .replace(/REG/i, 'R E G')
                .replace(/\b1\b/g, 'satu')
                .replace(/\b2\b/g, 'dua')
                .replace(/\b3\b/g, 'tiga')
                .replace(/\b4\b/g, 'empat')
                .replace(/\b5\b/g, 'lima');

            return `Nomor antrian, ${spelledLetters}, ${spelledNumbers}, silakan menuju, ${formattedCounter}`;
        }
    </script>

    <script type="module">
        // Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID');
            document.getElementById('date').textContent = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Load initial data
        function loadData() {
            fetch('{{ route("locations.show", $location) }}')
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    // Just reload the page for simplicity
                });
        }

        // WebSocket listeners
        window.Echo.channel('location.{{ $location->id }}')
            .listen('.ticket.called', (e) => {
                // Play sound
                if (window.audioActivated) {
                    const audio = new Audio('/sounds/ding.mp3');
                    audio.play().catch(() => {});
                }

                // Show notification
                const container = document.getElementById('calling-tickets');
                container.innerHTML = `
                    <div class="bg-green-600 rounded-2xl p-8 text-center animate-pulse">
                        <p class="text-green-200 mb-2">${e.counter?.name || 'Loket'}</p>
                        <p class="text-6xl font-bold">${e.ticket.ticket_number}</p>
                        ${e.ticket.customer_name ? `<p class="text-green-200 mt-2">${e.ticket.customer_name}</p>` : ''}
                    </div>
                `;

                // Speak ticket call
                if (window.audioActivated && 'speechSynthesis' in window) {
                    setTimeout(() => {
                        const ttsText = formatTTSMessage(e.ticket.ticket_number, e.counter?.name);
                        const speech = new SpeechSynthesisUtterance(ttsText);
                        speech.lang = 'id-ID';
                        speech.rate = 0.9;
                        window.speechSynthesis.speak(speech);
                    }, 1200);
                }

                setTimeout(() => location.reload(), 8000);
            })
            .listen('.ticket.status.changed', (e) => {
                location.reload();
            })
            .listen('.queue.updated', (e) => {
                document.getElementById('waiting-count').textContent = `${e.waiting_count} antrian menunggu`;
            });

        // Initial load simulation
        document.addEventListener('DOMContentLoaded', () => {
            const callingContainer = document.getElementById('calling-tickets');
            const waitingContainer = document.getElementById('waiting-tickets');

            // This would be populated via API or passed data
            @php
                $calling = $location->tickets()->whereIn('status', ['calling', 'serving'])->with('counter')->get();
                $waiting = $location->waitingTickets()->take(10)->get();
            @endphp

            @if($calling->isEmpty())
                callingContainer.innerHTML = `
                    <div class="bg-slate-700 rounded-2xl p-8 text-center col-span-2">
                        <p class="text-slate-400 text-xl">Tidak ada yang dipanggil</p>
                    </div>
                `;
            @else
                callingContainer.innerHTML = `
                    @foreach($calling as $ticket)
                    <div class="bg-green-600 rounded-2xl p-8 text-center">
                        <p class="text-green-200 mb-2">{{ $ticket->counter?->name }}</p>
                        <p class="text-6xl font-bold">{{ $ticket->ticket_number }}</p>
                        @if($ticket->customer_name)
                            <p class="text-green-200 mt-2">{{ $ticket->customer_name }}</p>
                        @endif
                    </div>
                    @endforeach
                `;
            @endif

            @if($waiting->isEmpty())
                waitingContainer.innerHTML = `
                    <p class="text-slate-400 text-center py-4">Tidak ada antrian</p>
                `;
            @else
                waitingContainer.innerHTML = `
                    @foreach($waiting as $ticket)
                    <div class="flex items-center justify-between py-3 border-b border-slate-700">
                        <span class="text-xl font-semibold">{{ $ticket->ticket_number }}</span>
                        <span class="text-slate-400">~{{ $ticket->estimated_wait_time }} menit</span>
                    </div>
                    @endforeach
                `;
            @endif

            document.getElementById('waiting-count').textContent = '{{ $waiting->count() }} antrian menunggu';
        });
    </script>

    <style>
    @keyframes marquee {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
    .animate-marquee {
        display: inline-block;
        white-space: nowrap;
        animation: marquee 28s linear infinite;
    }
    </style>

    <!-- Running Text Footer Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-slate-900/95 border-t border-slate-700 text-white py-3 px-6 flex items-center z-40 shadow-2xl backdrop-blur-md">
        <div class="bg-blue-600 text-white text-xs font-black uppercase px-3 py-1 rounded-lg flex items-center gap-1.5 shadow-md flex-shrink-0 mr-4">
            <span class="w-2 h-2 bg-white rounded-full animate-ping"></span>
            INFO LOKASI
        </div>
        <div class="overflow-hidden w-full relative flex items-center">
            <div class="animate-marquee font-medium text-lg text-blue-100">
                {{ $location->running_text ?? 'Selamat Datang di ' . $location->name . ' — Mohon perhatikan nomor antrian Anda di layar display. Harap menyiapkan dokumen pendukung sebelum dipanggil ke loket.' }}
            </div>
        </div>
    </div>
</body>
</html>
