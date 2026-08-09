<x-layout :title="'Ambil Tiket - ' . $location->name">
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
                        <a href="{{ route('locations.index') }}" class="text-slate-500 dark:text-slate-400 hover:text-blue-600">Lokasi</a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-slate-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-slate-900 font-medium">Ambil Tiket</span>
                    </li>
                </ol>
            </nav>

            <!-- Main Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="bg-blue-600 text-white p-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold">Ambil Tiket Antrian</h1>
                    <p class="text-blue-200 mt-1">{{ $location->name }}</p>
                </div>

                <!-- Queue Info -->
                <div class="p-6 border-b border-slate-100 dark:border-slate-700">
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-2xl p-5">
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">Antrian saat ini</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $location->waitingTickets()->count() }}</p>
                                <p class="text-slate-500 dark:text-slate-400 text-xs">orang menunggu</p>
                            </div>
                            <div>
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">Estimasi waktu</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">~{{ $location->getEstimatedWaitTime($location->waitingTickets()->count() + 1) }}</p>
                                <p class="text-slate-500 dark:text-slate-400 text-xs">menit</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="p-6">
                    <form method="POST" action="{{ route('tickets.store', $location) }}">
                        @csrf

                <div class="space-y-5" x-data="{ prioritySelected: '{{ old('priority_reason', '') }}' }">
                            @if(isset($serviceCategories) && $serviceCategories->count() > 0)
                            <div>
                                <label for="service_category_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Kategori Layanan <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <select
                                    id="service_category_id"
                                    name="service_category_id"
                                    class="w-full px-4 py-3.5 border-2 border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                >
                                    <option value="">— Pilih kategori —</option>
                                    @foreach($serviceCategories as $category)
                                        <option value="{{ $category->id }}" {{ old('service_category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                            @if($category->description) — {{ $category->description }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_category_id')
                                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif

                            {{-- Priority Queue Selection --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Layanan Prioritas <span class="text-slate-400 font-normal">(Jika diperlukan)</span>
                                </label>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Tiket prioritas akan didahulukan dalam antrian.</p>

                                <input type="hidden" name="is_priority" :value="prioritySelected ? '1' : ''"> 
                                <input type="hidden" name="priority_reason" :value="prioritySelected">

                                <div class="grid grid-cols-3 gap-3">
                                    {{-- Elderly --}}
                                    <button type="button"
                                        @click="prioritySelected = (prioritySelected === 'elderly') ? '' : 'elderly'"
                                        :class="prioritySelected === 'elderly' ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 ring-2 ring-orange-500' : 'border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:border-orange-300'"
                                        class="flex flex-col items-center p-3 rounded-xl border-2 transition-all text-center cursor-pointer"
                                    >
                                        <span class="text-2xl mb-1">🧓</span>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Lansia</span>
                                    </button>

                                    {{-- Pregnant --}}
                                    <button type="button"
                                        @click="prioritySelected = (prioritySelected === 'pregnant') ? '' : 'pregnant'"
                                        :class="prioritySelected === 'pregnant' ? 'border-pink-500 bg-pink-50 dark:bg-pink-900/20 ring-2 ring-pink-500' : 'border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:border-pink-300'"
                                        class="flex flex-col items-center p-3 rounded-xl border-2 transition-all text-center cursor-pointer"
                                    >
                                        <span class="text-2xl mb-1">🤰</span>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Ibu Hamil</span>
                                    </button>

                                    {{-- Disability --}}
                                    <button type="button"
                                        @click="prioritySelected = (prioritySelected === 'disability') ? '' : 'disability'"
                                        :class="prioritySelected === 'disability' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-500' : 'border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:border-blue-300'"
                                        class="flex flex-col items-center p-3 rounded-xl border-2 transition-all text-center cursor-pointer"
                                    >
                                        <span class="text-2xl mb-1">♿</span>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Disabilitas</span>
                                    </button>
                                </div>

                                <p x-show="prioritySelected" x-cloak class="mt-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Tiket Anda akan mendapat prioritas antrian
                                </p>
                            </div>

                            <div>
                                <label for="customer_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Nama <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <input
                                    type="text"
                                    id="customer_name"
                                    name="customer_name"
                                    value="{{ old('customer_name') }}"
                                    class="w-full px-4 py-3.5 border-2 border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400"
                                    placeholder="Masukkan nama Anda"
                                >
                                @error('customer_name')
                                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="customer_phone" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                    Nomor Telepon <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <input
                                    type="tel"
                                    id="customer_phone"
                                    name="customer_phone"
                                    value="{{ old('customer_phone') }}"
                                    class="w-full px-4 py-3.5 border-2 border-slate-200 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400"
                                    placeholder="08xxxxxxxxxx"
                                >
                                @error('customer_phone')
                                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-blue-200 transition-all hover:shadow-xl"
                        >
                            Ambil Tiket Sekarang
                        </button>
                    </form>

                    <p class="text-slate-500 dark:text-slate-400 text-sm text-center mt-5">
                        Dengan mengambil tiket, Anda setuju untuk menunggu giliran.
                    </p>
                </div>
            </div>

            <!-- Back Link -->
            <div class="text-center mt-6">
                <a href="{{ route('locations.index') }}" class="inline-flex items-center text-slate-600 dark:text-slate-400 hover:text-blue-600 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Pilih lokasi lain
                </a>
            </div>
        </div>
    </div>
</x-layout>
