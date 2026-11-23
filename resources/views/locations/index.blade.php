<x-layout title="Pilih Lokasi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li><a href="{{ route('home') }}" class="text-slate-500 hover:text-blue-600">Beranda</a></li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-slate-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-slate-900 font-medium">Lokasi</span>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Pilih Lokasi Layanan</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-2">Pilih lokasi untuk mengambil tiket antrian digital</p>
        </div>

    <div x-data="{ filter: 'all' }">
        <!-- Search/Filter -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-3">
                <button 
                    @click="filter = 'all'" 
                    :class="filter === 'all' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:border-blue-300'"
                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all"
                >
                    Semua
                </button>
                <button 
                    @click="filter = 'open'" 
                    :class="filter === 'open' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:border-blue-300'"
                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-all"
                >
                    <span class="w-2 h-2 rounded-full mr-2" :class="filter === 'open' ? 'bg-white' : 'bg-green-500'"></span>
                    Buka Sekarang
                </button>
            </div>
        </div>

        @if($locations->isEmpty())
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-16 text-center">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-2">Belum Ada Lokasi</h3>
                <p class="text-slate-600 dark:text-slate-400">Belum ada lokasi layanan yang tersedia saat ini.</p>
            </div>
        @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($locations as $location)
                    <div 
                        x-show="filter === 'all' || (filter === 'open' && {{ $location->isOpen() ? 'true' : 'false' }})"
                        class="group bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl hover:border-blue-200 dark:hover:border-blue-700 transition-all duration-300"
                    >
                        <!-- Card Header -->
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span class="inline-block bg-blue-600 text-white text-xs font-bold px-2.5 py-1 rounded-lg mb-3">
                                        {{ $location->code }}
                                    </span>
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">{{ $location->name }}</h3>
                                </div>
                                @if($location->isOpen())
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-800">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                                        Buka
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-800">
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span>
                                        Tutup
                                    </span>
                                @endif
                            </div>

                            @if($location->description)
                                <p class="text-slate-600 text-sm mb-4 line-clamp-2">{{ $location->description }}</p>
                            @endif

                            <!-- Info Grid -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="flex items-center text-sm text-slate-500 dark:text-slate-400">
                                    <svg class="w-4 h-4 mr-2 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($location->open_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($location->close_time)->format('H:i') }}
                                </div>
                                <div class="flex items-center text-sm text-slate-500 dark:text-slate-400">
                                    <svg class="w-4 h-4 mr-2 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    ~{{ $location->average_service_time }} menit
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700">
                            <div class="flex items-center justify-between">
                                <div class="text-center">
                                    <span class="block text-3xl font-bold text-slate-900 dark:text-white">{{ $location->waiting_count }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Menunggu</span>
                                </div>

                                <a href="{{ route('tickets.create', $location) }}" 
                                   class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors {{ !$location->isOpen() ? 'opacity-50 pointer-events-none' : '' }}">
                                    Ambil Tiket
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
        @endif
    </div>
    </div>
</x-layout>
