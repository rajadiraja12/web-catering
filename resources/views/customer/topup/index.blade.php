{{-- resources/views/customer/topup/index.blade.php --}}

@extends('app')
@include('layout.customer')
@section('content')
@yield('nav')
<br><br><br><br>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">TopUp Saldo</h1>
                <p class="text-sm text-gray-500">Isi saldo akun Anda</p>
            </div>
            <a href="{{ route('customer.topup.create') }}" 
               class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-all">
                + TopUp Baru
            </a>
        </div>

        {{-- Saldo Saat Ini --}}
        <div class="bg-gray-400 from-green-500 to-green-600 rounded-xl p-6 mb-6 text-white">
            <p class="text-sm opacity-80">Saldo Saat Ini</p>
            <p class="text-3xl font-bold">Rp {{ number_format($user->saldo ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs opacity-70 mt-1">Total TopUp: Rp {{ number_format($user->saldo_total ?? 0, 0, ',', '.') }}</p>
        </div>

        {{-- Riwayat TopUp --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-700">Riwayat TopUp</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nominal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($topups as $topup)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-sm">{{ $topup->kode_topup }}</td>
                            <td class="px-4 py-3 font-semibold text-green-600">
                                Rp {{ number_format($topup->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($topup->metode_pembayaran == 'midtrans')
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">💳 Midtrans</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">💰 Saldo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($topup->status == 'pending')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">⏳ Pending</span>
                                    @if($topup->created_at < now()->subHours(20))
                                        <span class="text-xs text-red-500 block mt-1">⚠️ Segera bayar!</span>
                                    @endif
                                @elseif($topup->status == 'verified')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">✅ Berhasil</span>
                                @elseif($topup->status == 'rejected')
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">❌ Ditolak</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">⏰ Kadaluarsa</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $topup->created_at->format('d-m-Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('customer.topup.detail', $topup->id) }}" 
                                       class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                                        Detail
                                    </a>
                                    @if($topup->status == 'pending' && $topup->payment_url)
                                        <a href="{{ $topup->payment_url }}" target="_blank" 
                                           class="text-green-500 hover:text-green-700 text-sm font-medium">
                                            Bayar
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-600">Belum ada riwayat topup</p>
                                    <p class="text-sm text-gray-400">Silakan lakukan topup pertama Anda</p>
                                    <a href="{{ route('customer.topup.create') }}" 
                                       class="mt-3 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all text-sm">
                                        + TopUp Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200">
                {{ $topups->links() }}
            </div>
        </div>
    </div>
</div>
@endsection