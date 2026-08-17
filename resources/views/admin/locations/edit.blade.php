<x-layout :title="'Edit ' . $location->name">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <a href="{{ route('admin.locations') }}" class="text-blue-600 hover:text-blue-700 text-sm mb-2 inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Edit Lokasi</h1>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <form method="POST" action="{{ route('admin.locations.update', $location) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="col-span-2 sm:col-span-1">
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Nama Lokasi *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $location->name) }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="code" class="block text-sm font-medium text-slate-700 mb-1">Kode *</label>
                            <input type="text" id="code" name="code" value="{{ old('code', $location->code) }}" required maxlength="10"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            @error('code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                        <textarea id="description" name="description" rows="3"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $location->description) }}</textarea>
                        @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $location->address) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('address')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="average_service_time" class="block text-sm font-medium text-slate-700 mb-1">Rata-rata Waktu Layanan (menit) *</label>
                            <input type="number" id="average_service_time" name="average_service_time" value="{{ old('average_service_time', $location->average_service_time) }}" required min="1" max="120"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('average_service_time')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="max_daily_tickets" class="block text-sm font-medium text-slate-700 mb-1">Batas Tiket Per Hari</label>
                            <input type="number" id="max_daily_tickets" name="max_daily_tickets" value="{{ old('max_daily_tickets', $location->max_daily_tickets) }}" min="1" placeholder="Kosongkan untuk tidak dibatasi"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('max_daily_tickets')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="open_time" class="block text-sm font-medium text-slate-700 mb-1">Jam Buka *</label>
                            <input type="time" id="open_time" name="open_time" value="{{ old('open_time', \Carbon\Carbon::parse($location->open_time)->format('H:i')) }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('open_time')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="close_time" class="block text-sm font-medium text-slate-700 mb-1">Jam Tutup *</label>
                            <input type="time" id="close_time" name="close_time" value="{{ old('close_time', \Carbon\Carbon::parse($location->close_time)->format('H:i')) }}" required
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('close_time')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="col-span-2 sm:col-span-1">
                            <label for="sla_waiting_threshold_minutes" class="block text-sm font-medium text-slate-700 mb-1">Batas SLA Menunggu (menit) *</label>
                            <input type="number" id="sla_waiting_threshold_minutes" name="sla_waiting_threshold_minutes" value="{{ old('sla_waiting_threshold_minutes', $location->sla_waiting_threshold_minutes ?? 15) }}" required min="1" max="120"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-slate-500 mt-1">Peringatan jika pelanggan menunggu lebih dari menit ini.</p>
                            @error('sla_waiting_threshold_minutes')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="running_text" class="block text-sm font-medium text-slate-700 mb-1">Teks Berjalan Display TV (Running Text)</label>
                        <textarea id="running_text" name="running_text" rows="2"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Pengumuman khusus yang tampil di bagian bawah TV ruang tunggu">{{ old('running_text', $location->running_text) }}</textarea>
                        @error('running_text')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $location->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                        <label for="is_active" class="ml-2 text-sm text-slate-700">Lokasi Aktif</label>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-slate-200">
                    <a href="{{ route('admin.locations') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
