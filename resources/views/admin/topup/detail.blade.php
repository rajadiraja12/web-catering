{{-- resources/views/admin/topup/detail.blade.php --}}

@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br><br><br><br>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.topup.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Detail TopUp</h1>
            <span class="ml-auto text-sm text-gray-400">#{{ $topup->id }}</span>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            {{-- Status Banner --}}
            <div class="p-4 border-b">
                <div class="flex flex-wrap justify-between items-center gap-2">
                    <div>
                        <p class="text-sm text-gray-500">Kode TopUp</p>
                        <p class="font-mono font-bold text-lg">{{ $topup->kode_topup }}</p>
                    </div>
                    <div>
                        @if($topup->status == 'pending')
                            <span class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">Pending</span>
                        @elseif($topup->status == 'verified')
                            <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-medium">Diverifikasi</span>
                        @elseif($topup->status == 'rejected')
                            <span class="px-4 py-2 bg-red-100 text-red-700 rounded-full text-sm font-medium">Ditolak</span>
                        @else
                            <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">Kadaluarsa</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informasi --}}
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Customer</p>
                        <p class="font-semibold text-lg">{{ $topup->user->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-500">Username: {{ $topup->user->username ?? '-' }}</p>
                        <p class="text-sm text-gray-500">Email: {{ $topup->user->email ?? '-' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Nominal</p>
                        <p class="font-bold text-2xl text-green-600">Rp {{ number_format($topup->nominal, 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500 mt-2">
                            <span class="font-medium">Metode:</span> 
                            @if($topup->metode_pembayaran == 'midtrans')
                                Midtrans
                            @else
                                Saldo
                            @endif
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Tanggal Transaksi</p>
                        <p class="font-semibold">{{ $topup->created_at->format('d-m-Y H:i:s') }}</p>
                        <p class="text-sm text-gray-500">Berlaku hingga: {{ $topup->expires_at ? date('d-m-Y H:i', strtotime($topup->expires_at)) : '-' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500">Status Pembayaran</p>
                        @if($topup->status == 'verified')
                            <p class="font-semibold text-green-600">berhasil</p>
                            @if($topup->paid_at)
                                <p class="text-sm text-gray-500">Dibayar: {{ $topup->paid_at->format('d-m-Y H:i') }}</p>
                            @endif
                        @elseif($topup->status == 'pending')
                            <p class="font-semibold text-yellow-600">Menunggu Pembayaran</p>
                        @elseif($topup->status == 'rejected')
                            <p class="font-semibold text-red-600">Ditolak</p>
                        @else
                            <p class="font-semibold text-gray-600">Kadaluarsa</p>
                        @endif
                    </div>
                </div>

                {{-- Detail Midtrans --}}
                @if($topup->metode_pembayaran == 'midtrans')
                <div class="mb-6 p-4 bg-purple-50 rounded-xl border border-purple-200">
                    <h4 class="font-semibold text-purple-800 mb-2">Detail Midtrans</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-gray-500">Order ID:</span>
                            <span class="font-mono font-medium">{{ $topup->midtrans_order_id ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Payment Token:</span>
                            <span class="font-mono text-xs truncate block">{{ $topup->payment_token ?? '-' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500">Payment URL:</span>
                            @if($topup->payment_url)
                                <a href="{{ $topup->payment_url }}" target="_blank" class="text-blue-500 hover:underline text-sm">
                                    {{ $topup->payment_url }}
                                </a>
                            @else
                                <span>-</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Catatan --}}
                @if($topup->catatan)
                <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                    <p class="text-sm font-semibold text-gray-700">Catatan</p>
                    <p class="text-gray-600">{{ $topup->catatan }}</p>
                </div>
                @endif

                {{-- Tombol --}}
                <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-6">
                    <a href="{{ route('admin.topup.index') }}" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-all">
                        ← Kembali
                    </a>
                    @if($topup->payment_url)
                        <a href="{{ $topup->payment_url }}" target="_blank" class="px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all">
                            🔗 Lihat Pembayaran
                        </a>
                    @endif
                    <button onclick="window.print()" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-all">
                        🖨️ Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none; }
    body { background: white; }
    .shadow-lg { box-shadow: none !important; }
    .bg-white { background: white !important; }
}
</style>
@endsection