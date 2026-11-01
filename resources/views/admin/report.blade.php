{{-- resources/views/admin/report.blade.php --}}
@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

{{-- Header Laporan --}}
<div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Laporan Keuangan</h1>
        <p class="text-sm text-gray-600 mt-1">
            Periode: {{ $startDate ? date('d-m-Y', strtotime($startDate)) : date('d-m-Y', strtotime('-1 month')) }} - {{ $endDate ? date('d-m-Y', strtotime($endDate)) : date('d-m-Y') }}
        </p>
    </div>
    <div class="flex items-center gap-3 mt-3 sm:mt-0">

        {{-- Tombol Export --}}
        <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Export PDF
        </button>
        
        {{-- Tambah Pengeluaran --}}
        <button type="button" data-modal-target="crud-modal" data-modal-toggle="crud-modal" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="#FFFFFF"><path d="M440-440H200v-80h240v-240h80v240h240v80H520v240h-80v-240Z"/></svg>
            Tambah Pengeluaran
        </button>
    </div>
</div>

{{-- Ringkasan Keuangan --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Pendapatan --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Total Pendapatan</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format($totalDebit ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-xl">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-1 text-sm">
            <span class="text-gray-500">Total pemasukan</span>
        </div>
    </div>

    {{-- Total Pengeluaran --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Total Pengeluaran</p>
                <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($totalKredit ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-red-100 rounded-xl">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-1 text-sm">
            <span class="text-gray-500">Total pengeluaran</span>
        </div>
    </div>

    {{-- Laba/Rugi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Laba / Rugi</p>
                <p class="text-2xl font-bold {{ ($totalSaldo ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }} mt-1">
                    Rp {{ number_format($totalSaldo ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-3 {{ ($totalSaldo ?? 0) >= 0 ? 'bg-blue-100' : 'bg-red-100' }} rounded-xl">
                <svg class="w-6 h-6 {{ ($totalSaldo ?? 0) >= 0 ? 'text-blue-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-1 text-sm">
            <span class="text-gray-500">Selisih pendapatan - pengeluaran</span>
        </div>
    </div>

    {{-- Total Transaksi --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 font-medium">Total Transaksi</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">{{ $totalTransaksi ?? 0 }}</p>
            </div>
            <div class="p-3 bg-purple-100 rounded-xl">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-1 text-sm">
            <span class="text-gray-500">Total semua transaksi</span>
        </div>
    </div>
</div>

{{-- Tabel Laporan Keuangan --}}
<div class="relative overflow-x-auto bg-white rounded-2xl border border-gray-200 shadow-sm">
    <table class="w-full text-sm text-left text-gray-700">
        <thead class="text-xs text-gray-800 uppercase bg-blue-200 from-blue-600 to-blue-800">
            <tr>
                <th scope="col" class="px-6 py-4 text-center w-20">No</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Tanggal</th>
                <th scope="col" class="px-6 py-4">Keterangan</th>
                <th scope="col" class="px-6 py-4 text-right whitespace-nowrap">Debit</th>
                <th scope="col" class="px-6 py-4 text-right whitespace-nowrap">Kredit</th>
                <th scope="col" class="px-6 py-4 text-right whitespace-nowrap">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-4 text-center font-medium text-gray-900">
                    {{ $loop->iteration + (($reports->currentPage() - 1) * $reports->perPage()) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $report->created_at }}</td>
                <td class="px-6 py-4">{{ $report->keterangan }}</td>
                <td class="px-6 py-4 text-right font-medium {{ $report->debit > 0 ? 'text-green-600' : '' }}">
                    {{ $report->debit > 0 ? 'Rp ' . number_format($report->debit, 0, ',', '.') : '-' }}
                </td>
                <td class="px-6 py-4 text-right font-medium {{ $report->kredit > 0 ? 'text-red-600' : '' }}">
                    {{ $report->kredit > 0 ? 'Rp ' . number_format($report->kredit, 0, ',', '.') : '-' }}
                </td>
                <td class="px-6 py-4 text-right font-medium">
                    Rp {{ number_format($report->debit + $report->kredit, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium">Tidak ada data transaksi</p>
                        <p class="text-xs text-gray-400 mt-1">Belum ada transaksi yang tercatat</p>
                    </div>
                </td>
            </tr>
            @endforelse

            {{-- Total/Jumlah --}}
            @if($reports->count() > 0)
            <tr class="bg-blue-50 border-b-2 border-blue-200 hover:bg-blue-100 transition-colors duration-200">
                <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900 text-base">
                    TOTAL
                </td>
                <td class="px-6 py-4 text-right font-bold text-green-700 text-base">
                    Rp {{ number_format($totalDebit ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-right font-bold text-red-700 text-base">
                    Rp {{ number_format($totalKredit ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-right font-bold text-blue-700 text-base">
                    Rp {{ number_format(($totalDebit ?? 0) - ($totalKredit ?? 0), 0, ',', '.') }}
                </td>
            </tr>

            {{-- Saldo Akhir --}}
            <tr class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <td colspan="3" class="px-6 py-4 text-right font-bold text-base">
                    SALDO AKHIR
                </td>
                <td class="px-6 py-4 text-right font-bold text-base">
                    Rp {{ number_format($totalDebit ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-right font-bold text-base">
                    Rp {{ number_format($totalKredit ?? 0, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-right font-bold text-base">
                    Rp {{ number_format($totalSaldo ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Pagination --}}
    <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between p-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl" aria-label="Table navigation">
        <span class="text-sm font-normal text-gray-700 mb-4 md:mb-0 block w-full md:inline md:w-auto">
            Menampilkan <span class="font-semibold text-gray-900">{{ $reports->firstItem() ?? 0 }}</span>-<span class="font-semibold text-gray-900">{{ $reports->lastItem() ?? 0 }}</span> 
            dari <span class="font-semibold text-gray-900">{{ $reports->total() }}</span> transaksi
        </span>
        <div>
            {{ $reports->links() }}
        </div>
    </nav>
</div>

@endsection
<div id="crud-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8 animate-fadeInDown">
            
            <!-- Modal header -->
            <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            Tambah Pengeluaran Baru
                        </h3>
                        <p class="text-sm text-gray-500 mt-0.5">Isi form di bawah untuk menambahkan Penegeluaran</p>
                    </div>
                </div>
                <button type="button" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90" data-modal-hide="crud-modal">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            
            <!-- Modal body -->
            <form action="{{ route('admin.reports.expense') }}" method="POST" class="space-y-6" id="form-expense">
                @csrf
                <div class="grid gap-4 grid-cols-1">
                    <div class="col-span-1">
                        <label for="tanggal" class="block mb-2 text-sm font-semibold text-gray-700">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">                                
                            <input type="date" name="tanggal" id="tanggal" 
                                class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200 hover:border-blue-400"  
                                required>
                        </div>
                    </div>

                    <div class="col-span-1">
                        <label for="keterangan" class="block mb-2 text-sm font-semibold text-gray-700">
                            Keterangan Pengeluaran <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <textarea name="keterangan" id="keterangan" rows="4" 
                                class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200 hover:border-blue-400 resize-none" 
                                placeholder="Tulis keterangan pengeluaran..." required></textarea>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Maksimal 500 karakter</p>
                    </div>

                    <div class="col-span-1">
                        <label for="nominal" class="block mb-2 text-sm font-semibold text-gray-700">
                            Nominal <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">                                
                            <input type="number" name="nominal" id="nominal" 
                                class="bg-gray-50 border-2 border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-3 transition-all duration-200 hover:border-blue-400" 
                                placeholder="0" required min="0">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t-2 border-gray-100">
                    <button data-modal-hide="crud-modal" type="button" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200 hover:scale-105 focus:ring-4 focus:ring-gray-200 focus:outline-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </button>
                    
                    <button type="submit" 
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-800 hover:bg-blue-700 rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-lg hover:shadow-blue-500/30 focus:ring-4 focus:ring-blue-300 focus:outline-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================
// FORM SUBMIT - TAMBAH PENGELUARAN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-expense');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Menyimpan...
            `;
            submitBtn.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✅ ' + data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = '❌ Gagal menyimpan!';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    }
                    showNotification(errorMsg, 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                showNotification('❌ Terjadi kesalahan!', 'error');
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// ============================================
// FUNGSI NOTIFICATION
// ============================================
function showNotification(message, type = 'success') {
    const oldNotification = document.getElementById('custom-notification');
    if (oldNotification) {
        oldNotification.remove();
    }
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const notification = document.createElement('div');
    notification.id = 'custom-notification';
    notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-500 translate-x-0 opacity-100 ${colors[type] || colors.success} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>