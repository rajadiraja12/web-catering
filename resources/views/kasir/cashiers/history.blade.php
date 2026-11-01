{{-- resources/views/admin/cashier/history.blade.php --}}

@extends('app')
@include('layout.cashier')
@section('content')
@yield('nav')
<br><br><br><br>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Riwayat Transaksi Cash</h1>
            <p class="text-sm text-gray-500">Semua transaksi pembayaran tunai</p>
        </div>
        <a href="{{ route('kasir.dashboard') }}" 
           class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-all">
            ← Kembali
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-medium text-gray-500">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Cari</label>
                <input type="text" name="search" placeholder="Kode order / nama" value="{{ request('search') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 w-48">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Filter
            </button>
            <a href="{{ route('kasir.history') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                Reset
            </a>
        </form>
    </div>

    {{-- Total Revenue --}}
    <div class="bg-green-50 rounded-xl p-4 mb-6 border border-green-200">
        <div class="flex justify-between items-center">
            <span class="font-semibold text-green-800">Total Pendapatan Cash</span>
            <span class="text-2xl font-bold text-green-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menu</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dikonfirmasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $index => $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 font-mono text-sm">{{ $order->kode_order }}</td>
                        <td class="px-6 py-4">{{ $order->customer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $order->menu->nama_menu ?? '-' }}</td>
                        <td class="px-6 py-4 text-right font-semibold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($order->confirmed_by)
                                {{ $order->confirmedBy->name ?? 'Kasir' }}
                                <br>
                                <span class="text-xs text-gray-400">{{ date('H:i', strtotime($order->confirmed_at)) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">{{ date('d-m-Y H:i', strtotime($order->created_at)) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            Belum ada transaksi cash
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
    </div>
</div>

@endsection