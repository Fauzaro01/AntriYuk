<x-layout title="Audit Logs Operasional">
    <div class="min-h-screen bg-slate-100 dark:bg-slate-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb & Title -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm inline-flex items-center mb-2">
                        ← Kembali ke Dashboard
                    </a>
                    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white">Audit Log Operasional</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Rekam jejak aktivitas operasional dan tindakan pengguna di sistem</p>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm mb-6">
                <form method="GET" action="{{ route('admin.audit-logs') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase mb-1">Filter Lokasi</label>
                        <select name="location_id" class="w-full text-sm border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-blue-500">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase mb-1">Filter Jenis Aksi</label>
                        <input type="text" name="action" value="{{ request('action') }}" placeholder="Cari misal: ticket.call, transfer..." class="w-full text-sm border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-blue-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors">
                            Filter
                        </button>
                        <a href="{{ route('admin.audit-logs') }}" class="px-4 py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-semibold transition-colors">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-xs font-bold uppercase text-slate-500 dark:text-slate-400">
                                <th class="py-4 px-6">Waktu</th>
                                <th class="py-4 px-6">Pengguna</th>
                                <th class="py-4 px-6">Lokasi</th>
                                <th class="py-4 px-6">Aksi</th>
                                <th class="py-4 px-6">Deskripsi</th>
                                <th class="py-4 px-6">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="py-4 px-6 text-slate-500 dark:text-slate-400 whitespace-nowrap text-xs">
                                        {{ $log->created_at->format('d M Y H:i:s') }}
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                        {{ $log->user?->name ?? 'Sistem / Anonim' }}
                                    </td>
                                    <td class="py-4 px-6 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                        {{ $log->location?->name ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full 
                                            {{ str_contains($log->action, 'call') ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : '' }}
                                            {{ str_contains($log->action, 'complete') ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : '' }}
                                            {{ str_contains($log->action, 'skip') ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' : '' }}
                                            {{ str_contains($log->action, 'transfer') ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' : '' }}
                                            {{ str_contains($log->action, 'supervisor') ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : '' }}
                                        ">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-slate-700 dark:text-slate-300">
                                        {{ $log->description }}
                                    </td>
                                    <td class="py-4 px-6 text-slate-400 dark:text-slate-500 font-mono text-xs whitespace-nowrap">
                                        {{ $log->ip_address ?? '127.0.0.1' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                        Belum ada data audit log yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
