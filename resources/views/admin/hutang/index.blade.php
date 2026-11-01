{{-- resources/views/admin/hutang/index.blade.php --}}

@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br><br><br><br>

<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kredit</h1>
            <p class="text-sm text-gray-500">Kelola pembayaran hutang customer</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <span class="px-4 py-2 bg-orange-100 text-orange-700 rounded-lg text-sm font-medium">
                Total Kredit: {{ $statistics['total'] ?? 0 }}
            </span>
            <span class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-medium">
                Jatuh Tempo: {{ $statistics['overdue'] ?? 0 }}
            </span>
            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium">
                Total: Rp {{ number_format($statistics['total_nominal'] ?? 0, 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-medium text-gray-500">Status</label>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">Semua</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Jatuh Tempo</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500">Cari</label>
                <input type="text" name="search" placeholder="Kode / Customer" value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all text-sm">
                Filter
            </button>
            <a href="{{ route('admin.hutang.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-all text-sm">
                Reset
            </a>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Order</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($hutangs as $index => $hutang)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm">{{ $loop->iteration + (($hutangs->currentPage() - 1) * $hutangs->perPage()) }}</td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $hutang->kode_order }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $hutang->customer->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">{{ $hutang->customer->username ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-red-600">
                            Rp {{ number_format($hutang->total_harga, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">{{ $hutang->created_at ? $hutang->created_at->format('d-m-Y') : '-' }}</td>
                        <td class="px-4 py-3 text-center text-sm">
                            @if($hutang->due_date)
                                @if($hutang->due_date < now())
                                    <span class="text-red-600 font-bold">{{ $hutang->due_date->format('d-m-Y') }}</span>
                                @else
                                    <span class="text-gray-600">{{ $hutang->due_date->format('d-m-Y') }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($hutang->due_date && $hutang->due_date < now())
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Jatuh Tempo</span>
                            @else
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.hutang.detail', $hutang->id) }}" 
                               class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-lg font-medium text-gray-600">Belum ada hutang</p>
                                <p class="text-sm text-gray-400">Semua hutang sudah lunas</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $hutangs->links() }}
        </div>
    </div>
</div>
@endsection