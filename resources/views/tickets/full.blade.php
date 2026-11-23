<x-layout :title="'Antrian Penuh — ' . $location->name">
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 flex items-center justify-center p-6">
        <div class="max-w-md w-full text-center">
            {{-- Animated icon --}}
            <div class="relative w-32 h-32 mx-auto mb-8">
                <div class="absolute inset-0 bg-red-100 dark:bg-red-900/30 rounded-full animate-ping opacity-40"></div>
                <div class="relative w-32 h-32 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <span class="text-6xl">🚫</span>
                </div>
            </div>

            <h1 class="text-3xl font-black text-slate-900 dark:text-white mb-3">Antrian Penuh</h1>
            <p class="text-slate-600 dark:text-slate-400 text-lg mb-2">
                Antrian di <span class="font-semibold text-slate-900 dark:text-white">{{ $location->name }}</span> telah mencapai batas maksimum hari ini.
            </p>
            <p class="text-slate-500 dark:text-slate-400 text-sm mb-8">
                Silakan datang kembali besok atau coba di lokasi layanan lain.
            </p>

            {{-- Info box --}}
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 mb-8 text-left">
                <div class="flex items-start gap-3">
                    <span class="text-amber-500 text-xl mt-0.5">⏰</span>
                    <div>
                        <p class="font-semibold text-amber-900 dark:text-amber-300 text-sm">Informasi Jam Layanan</p>
                        <p class="text-amber-700 dark:text-amber-400 text-sm mt-1">
                            {{ $location->name }} melayani mulai pukul
                            <span class="font-bold">{{ $location->open_time->format('H:i') }}</span> hingga
                            <span class="font-bold">{{ $location->close_time->format('H:i') }}</span>.
                        </p>
                        <p class="text-amber-700 dark:text-amber-400 text-sm mt-1">
                            Antrian akan direset setiap hari pada pukul 00:00.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3">
                <a href="{{ route('locations.index') }}"
                   class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-2xl transition-all hover:shadow-lg hover:shadow-blue-200 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Cari Lokasi Lain
                </a>
                <a href="{{ route('home') }}"
                   class="flex items-center justify-center gap-2 border-2 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-semibold py-4 px-6 rounded-2xl transition-all">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</x-layout>
