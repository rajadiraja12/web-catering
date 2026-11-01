{{-- resources/views/admin/manage_order.blade.php --}}
@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

{{-- Filter dan Search --}}
<div class="mb-6">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-2 flex-wrap">
            <div class="inline-flex rounded-base shadow-xs -space-x-px" role="group">
                <a href="{{ route('admin.orders.index', ['status' => 'all']) }}" 
                    class="px-4 py-2 text-sm font-medium {{ request('status', 'all') == 'all' ? 'text-white bg-blue-600' : 'text-gray-700 bg-white hover:bg-gray-50' }} border border-gray-300 rounded-l-lg transition-all duration-200">
                    Semua
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'unpaid']) }}" 
                    class="px-4 py-2 text-sm font-medium {{ request('status') == 'unpaid' ? 'text-white bg-blue-600' : 'text-gray-700 bg-white hover:bg-gray-50' }} border-y border-gray-300 transition-all duration-200">
                    Belum Bayar
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" 
                    class="px-4 py-2 text-sm font-medium {{ request('status') == 'paid' ? 'text-white bg-blue-600' : 'text-gray-700 bg-white hover:bg-gray-50' }} border border-gray-300 rounded-r-lg transition-all duration-200">
                    Lunas
                </a>
            </div>
            <a href="{{ route('admin.orders.index') }}" 
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-all duration-200">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </a>
        </div>
        
        {{-- TOMBOL CETAK EXCEL --}}
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Tombol Excel Harian --}}
            <div class="flex items-center gap-2">
                <input type="date" id="exportDailyDate" value="{{ date('Y-m-d') }}" 
                    class="px-3 py-2 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <a href="#" onclick="exportExcelDaily()" 
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel Harian
                </a>
            </div>

            {{-- Tombol Excel Bulanan --}}
            <div class="flex items-center gap-2">
                <input type="month" id="exportMonthlyDate" value="{{ date('Y-m') }}" 
                    class="px-3 py-2 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                <a href="#" onclick="exportExcelMonthly()" 
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel Bulanan
                </a>
            </div>

            {{-- Tombol Excel dengan Filter Saat Ini --}}
            <a href="{{ route('admin.orders.export-excel-filtered', request()->query()) }}" 
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel Filter
            </a>

            <button onclick="bulkTransfer()" 
                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Transfer Massal
            </button>
        </div>
    </div>

    {{-- Search dan Filter Tanggal --}}
    <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-4 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <input type="hidden" name="status" value="{{ request('status', 'all') }}">
        
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" 
                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 placeholder-gray-400"
                placeholder="Cari berdasarkan kode order, customer, atau menu...">
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative flex-1 sm:flex-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="pl-10 pr-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 w-full sm:w-40">
            </div>
            <span class="text-gray-500 text-sm font-medium">s/d</span>
            <div class="relative flex-1 sm:flex-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="pl-10 pr-3 py-2.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm text-gray-900 w-full sm:w-40">
            </div>
            <button type="submit" 
                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md whitespace-nowrap">
                Filter
            </button>
            <a href="{{ route('admin.orders.index') }}" 
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-all duration-200">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Tabel --}}
<div class="relative overflow-x-auto bg-white shadow-sm rounded-2xl border border-gray-200">
    <table class="w-full text-sm text-left rtl:text-right text-gray-700" id="orderTable">
        <thead class="text-xs text-white uppercase bg-blue-700 border-b border-blue-900">
            <tr>
                <th scope="col" class="px-6 py-4 text-center w-12">
                    <input type="checkbox" id="selectAll" onchange="toggleAllCheckbox()" class="w-4 h-4 rounded border-gray-300">
                </th>
                <th scope="col" class="px-6 py-4 text-center w-16">No</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Kode Order</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Tgl Order</th>
                <th scope="col" class="px-6 py-4 whitespace-nowrap">Tgl Menu</th>
                <th scope="col" class="px-6 py-4">Menu</th>
                <th scope="col" class="px-6 py-4">Paket</th>
                <th scope="col" class="px-6 py-4 text-right">Harga</th>
                <th scope="col" class="px-6 py-4">Customer</th>
                <th scope="col" class="px-6 py-4 text-center">Status</th>
                <th scope="col" class="px-6 py-4 text-center">Catatan</th>
                <th scope="col" class="px-6 py-4 text-center">Action</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @forelse($orders as $index => $order)
            <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} border-b border-gray-200 hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-4 text-center">
                    <input type="checkbox" class="row-checkbox w-4 h-4 rounded border-gray-300" value="{{ $order->id }}">
                </td>
                <td class="px-6 py-4 text-center font-medium text-gray-900">
                    {{ $loop->iteration + (($orders->currentPage() - 1) * $orders->perPage()) }}
                </td>
                <td class="px-6 py-4 font-mono text-sm font-semibold text-blue-600">{{ $order->kode_order }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($order->tanggal_order)->format('d/m/Y') }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($order->tanggal_menu)->format('d/m/Y') }}</td>
                <td class="px-6 py-4">{{ $order->menu ? $order->menu->nama_menu : '-' }}</td>
                <td class="px-6 py-4">
                    <span class="px-2.5 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium capitalize">
                        {{ $order->paket ?? '-' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right font-semibold text-blue-600">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $order->customer ? $order->customer->name : '-' }}-{{ $order->customer ? $order->customer->kelas : '-' }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="px-2.5 py-1 {{ $order->payment_status == 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-full text-xs font-medium">
                        {{ $order->payment_status == 'paid' ? 'Lunas' : 'Hutang' }}
                    </span>
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">{{ $order->catatan }}</td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                        {{-- Tombol Transfer --}}
                        @if ( $order->payment_status === 'paid')   
                        <button onclick="openTransferModal(
                            {{ $order->id }}, 
                            '{{ $order->customer ? $order->customer->id : '' }}', 
                            '{{ $order->customer ? $order->customer->name . ' (' . $order->customer->kelas . ')' : 'Unknown' }}'
                        )" 
                                class="p-1.5 bg-orange-100 hover:bg-orange-600 text-orange-700 hover:text-white rounded-lg transition-all duration-200" 
                                title="Transfer ke customer lain">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </button>
                        @endif
                        <button onclick="openGantiHariModal({{ $order->id }})" 
                                class="p-1.5 bg-purple-100 hover:bg-purple-600 text-purple-700 hover:text-white rounded-lg transition-all duration-200" 
                                title="Ganti Hari (Jumat)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </button>
                        {{-- Tombol Hapus --}}
                        <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus order ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                class="p-1.5 bg-red-100 hover:bg-red-600 text-red-700 hover:text-white rounded-lg transition-all duration-200" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium">Tidak ada data</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between p-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl" aria-label="Table navigation">
        <span class="text-sm font-normal text-gray-700 mb-4 md:mb-0 block w-full md:inline md:w-auto">
            Menampilkan <span class="font-semibold text-gray-900">{{ $orders->firstItem() ?? 0 }}</span>-<span class="font-semibold text-gray-900">{{ $orders->lastItem() ?? 0 }}</span> 
            dari <span class="font-semibold text-gray-900">{{ $orders->total() }}</span> data
        </span>
        <div>
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </nav>
</div>

{{-- ============================================ --}}
{{-- MODAL TRANSFER ORDER --}}
{{-- ============================================ --}}
<div id="transfer-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8">
            
            <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg shadow-orange-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Transfer Order</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Pindahkan order ke customer lain</p>
                    </div>
                </div>
                <button type="button" onclick="closeTransferModal()" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="form-transfer-order" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Order yang Dipilih</label>
                    <div id="selected-orders-list" class="bg-gray-50 rounded-xl p-3 max-h-32 overflow-y-auto">
                        <p class="text-sm text-gray-500">Belum ada order dipilih</p>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Total: <span id="selected-total">Rp 0</span></p>
                </div>

                {{-- Customer Asal (Readonly) --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Customer Asal <span class="text-red-500">*</span></label>
                    <input type="text" id="from_customer_display" 
                        value="-" 
                        class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl cursor-not-allowed" 
                        readonly disabled>
                    <input type="hidden" name="from_customer" id="from_customer" value="">
                </div>

                {{-- Pilih Kelas --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kelas Tujuan <span class="text-red-500">*</span></label>
                    <select name="kelas" id="kelas" required 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                            onchange="updateNamaKelas()">
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas_list as $kelas)
                            <option value="{{ $kelas }}">Kelas {{ $kelas }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pilih Nama Kelas --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Nama Kelas <span class="text-red-500">*</span></label>
                    <select name="nama_kelas" id="nama_kelas" required 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                            onchange="updateCustomerList()">
                        <option value="">Pilih Nama Kelas</option>
                    </select>
                </div>

                {{-- Pilih Customer Tujuan --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Customer Tujuan <span class="text-red-500">*</span></label>
                    <select name="to_customer" id="to_customer" required 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                        <option value="">Pilih Customer Tujuan</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Order akan dipindahkan ke customer ini</p>
                </div>

                {{-- Catatan Transfer --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan Transfer</label>
                    <textarea name="transfer_note" id="transfer_note" rows="2"
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                            placeholder="Contoh: Karena customer sakit, order dialihkan ke..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 mt-5 border-t-2 border-gray-100">
                    <button type="button" onclick="closeTransferModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-orange-500/30">
                        <span id="transferBtnText">Transfer Order</span>
                        <span id="transferBtnLoading" class="hidden">
                            <svg class="inline w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL GANTI HARI --}}
{{-- ============================================ --}}
<div id="ganti-hari-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 md:p-8">
            
            <div class="flex items-center justify-between pb-5 mb-5 border-b-2 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg shadow-purple-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Ganti Hari Order</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Ubah tanggal menu ke hari Jumat</p>
                    </div>
                </div>
                <button type="button" onclick="closeGantiHariModal()" class="text-gray-400 bg-gray-50 hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-all duration-200 hover:rotate-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="form-ganti-hari" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Order yang Dipilih</label>
                    <div id="ganti-hari-orders-list" class="bg-gray-50 rounded-xl p-3 max-h-32 overflow-y-auto">
                        <p class="text-sm text-gray-500">Belum ada order dipilih</p>
                    </div>
                </div>

                {{-- Tanggal Menu Saat Ini --}}
<div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Menu Saat Ini</label>
                    <input type="text" id="current_tanggal_menu" 
                        class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl cursor-not-allowed" 
                        readonly disabled>
                    <input type="hidden" id="current_tanggal_menu_value" name="current_tanggal_menu">
                </div>

                {{-- Pilih Tanggal Baru (Hari Jumat) --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Tanggal Baru (Jumat) <span class="text-red-500">*</span></label>
                    <select name="new_tanggal_menu" id="new_tanggal_menu" required 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                        <option value="">Pilih Tanggal Jumat</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hanya menampilkan hari Jumat</p>
                </div>

                {{-- Alasan Ganti Hari --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan Ganti Hari <span class="text-red-500">*</span></label>
                    <select name="alasan" id="alasan_ganti_hari" required 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                        <option value="">Pilih Alasan</option>
                        <option value="sakit">Sakit</option>
                        <option value="tidak_masuk">Tidak Masuk Sekolah</option>
                        <option value="izin">Izin</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                {{-- Catatan Tambahan --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan Tambahan</label>
                    <textarea name="catatan_ganti_hari" id="catatan_ganti_hari" rows="2"
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                            placeholder="Tambahkan catatan jika diperlukan"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 mt-5 border-t-2 border-gray-100">
                    <button type="button" onclick="closeGantiHariModal()" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition-all hover:scale-105 hover:shadow-lg hover:shadow-purple-500/30">
                        <span id="gantiHariBtnText">📅 Ganti Hari</span>
                        <span id="gantiHariBtnLoading" class="hidden">
                            <svg class="inline w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- JAVASCRIPT --}}
{{-- ============================================ --}}
<script>
// ============================================
// NOTIFICATION
// ============================================
function showNotification(message, type = 'info') {
    const oldNotification = document.getElementById('custom-notification');
    if (oldNotification) oldNotification.remove();
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const notification = document.createElement('div');
    notification.id = 'custom-notification';
    notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-500 translate-x-0 opacity-100 ${colors[type] || colors.info} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 max-w-md`;
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
        if (notification.parentNode) notification.remove();
    }, 5000);
}

// ============================================
// TOGGLE ALL CHECKBOX
// ============================================
function toggleAllCheckbox() {
    const checked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked);
}

// ============================================
// EXPORT EXCEL
// ============================================
function exportExcelDaily() {
    const date = document.getElementById('exportDailyDate').value;
    if (!date) {
        showNotification('⚠️ Silakan pilih tanggal terlebih dahulu!', 'warning');
        return;
    }
    window.location.href = "{{ route('admin.orders.export-excel-daily') }}?date=" + date;
}

function exportExcelMonthly() {
    const month = document.getElementById('exportMonthlyDate').value;
    if (!month) {
        showNotification('⚠️ Silakan pilih bulan terlebih dahulu!', 'warning');
        return;
    }
    const parts = month.split('-');
    window.location.href = "{{ route('admin.orders.export-excel-monthly') }}?year=" + parts[0] + "&month=" + parts[1];
}

// ============================================
// DATA CUSTOMERS (Dari PHP)
// ============================================
const customersData = @json($customersWithParsed);
const namaKelasByKelas = @json($nama_kelas_by_kelas);

// ============================================
// UPDATE NAMA KELAS BERDASARKAN KELAS
// ============================================
function updateNamaKelas() {
    const kelas = document.getElementById('kelas').value;
    const namaKelasSelect = document.getElementById('nama_kelas');
    const toCustomerSelect = document.getElementById('to_customer');
    
    namaKelasSelect.innerHTML = '<option value="">Pilih Nama Kelas</option>';
    toCustomerSelect.innerHTML = '<option value="">Pilih Customer Tujuan</option>';
    
    if (!kelas) return;
    
    const namaKelasList = namaKelasByKelas[kelas] || [];
    
    namaKelasList.forEach(nama => {
        const option = document.createElement('option');
        option.value = nama;
        option.textContent = nama;
        namaKelasSelect.appendChild(option);
    });
}

// ============================================
// UPDATE CUSTOMER LIST
// ============================================
function updateCustomerList() {
    const kelas = document.getElementById('kelas').value;
    const namaKelas = document.getElementById('nama_kelas').value;
    const fromCustomerId = document.getElementById('from_customer').value;
    const toCustomerSelect = document.getElementById('to_customer');
    
    toCustomerSelect.innerHTML = '<option value="">Pilih Customer Tujuan</option>';
    
    if (!kelas || !namaKelas) return;
    
    const filtered = customersData.filter(c => 
        c.kelas === kelas && 
        c.nama_kelas === namaKelas &&
        c.id != fromCustomerId
    );
    
    filtered.forEach(customer => {
        const option = document.createElement('option');
        option.value = customer.id;
        option.textContent = customer.name + ' (' + customer.kelas_full + ')';
        toCustomerSelect.appendChild(option);
    });
}

// ============================================
// TRANSFER ORDER
// ============================================
let selectedOrderIds = [];

function openTransferModal(orderId, customerId, customerName) {
    selectedOrderIds = [];
    
    if (orderId) {
        selectedOrderIds.push(orderId);
        updateSelectedOrders();
        
        document.getElementById('from_customer_display').value = customerName || 'Unknown';
        document.getElementById('from_customer').value = customerId || '';
    }
    
    document.getElementById('kelas').value = '';
    document.getElementById('nama_kelas').innerHTML = '<option value="">Pilih Nama Kelas</option>';
    document.getElementById('to_customer').innerHTML = '<option value="">Pilih Customer Tujuan</option>';
    
    document.getElementById('transfer-modal').classList.remove('hidden');
    document.getElementById('transfer-modal').style.display = 'flex';
}

function closeTransferModal() {
    document.getElementById('transfer-modal').classList.add('hidden');
    document.getElementById('transfer-modal').style.display = 'none';
    selectedOrderIds = [];
    document.getElementById('selected-orders-list').innerHTML = '<p class="text-sm text-gray-500">Belum ada order dipilih</p>';
    document.getElementById('selected-total').textContent = 'Rp 0';
}

function updateSelectedOrders() {
    if (selectedOrderIds.length === 0) {
        document.getElementById('selected-orders-list').innerHTML = '<p class="text-sm text-gray-500">Belum ada order dipilih</p>';
        document.getElementById('selected-total').textContent = 'Rp 0';
        return;
    }
    
    fetch('{{ route("admin.orders.bulk-info") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ids: selectedOrderIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let html = '';
            let total = 0;
            data.orders.forEach(order => {
                html += `
                    <div class="flex justify-between items-center py-1 border-b border-gray-200 last:border-0">
                        <span class="text-sm font-mono">${order.kode_order}</span>
                        <span class="text-sm font-semibold text-blue-600">${order.total_harga}</span>
                    </div>
                `;
                total += order.total_harga_raw || 0;
            });
            document.getElementById('selected-orders-list').innerHTML = html;
            document.getElementById('selected-total').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('❌ Gagal mengambil data order', 'error');
    });
}

function bulkTransfer() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkedBoxes.length === 0) {
        showNotification('⚠️ Pilih minimal 1 order untuk ditransfer!', 'warning');
        return;
    }
    
    selectedOrderIds = [];
    checkedBoxes.forEach(cb => {
        selectedOrderIds.push(parseInt(cb.value));
    });
    
    // Ambil data customer asal dari order pertama
    fetch('{{ route("admin.orders.bulk-info") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ids: selectedOrderIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.orders.length > 0) {
            const order = data.orders[0];
            document.getElementById('from_customer_display').value = order.customer_name + ' (' + order.customer_kelas + ')';
            document.getElementById('from_customer').value = order.customer_id;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
    
    updateSelectedOrders();
    document.getElementById('transfer-modal').classList.remove('hidden');
    document.getElementById('transfer-modal').style.display = 'flex';
}

// ============================================
// SUBMIT TRANSFER FORM
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-transfer-order');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fromCustomerId = document.getElementById('from_customer').value;
            const toCustomer = document.getElementById('to_customer').value;
            const note = document.getElementById('transfer_note').value;
            
            if (!fromCustomerId) {
                showNotification('⚠️ Customer asal tidak ditemukan!', 'warning');
                return;
            }
            
            if (!toCustomer) {
                showNotification('⚠️ Pilih customer tujuan!', 'warning');
                return;
            }
            
            if (fromCustomerId === toCustomer) {
                showNotification('⚠️ Customer asal dan tujuan tidak boleh sama!', 'warning');
                return;
            }
            
            if (selectedOrderIds.length === 0) {
                showNotification('⚠️ Tidak ada order yang dipilih!', 'warning');
                return;
            }
            
            const btnText = document.getElementById('transferBtnText');
            const btnLoading = document.getElementById('transferBtnLoading');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            submitBtn.disabled = true;
            
            fetch('{{ route("admin.orders.transfer") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_ids: selectedOrderIds,
                    from_customer: fromCustomerId,
                    to_customer: toCustomer,
                    note: note
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✅ ' + data.message, 'success');
                    setTimeout(() => {
                        closeTransferModal();
                        location.reload();
                    }, 1500);
                } else {
                    let errorMsg = '❌ Gagal transfer!';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    showNotification(errorMsg, 'error');
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ ' + error.message, 'error');
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
                submitBtn.disabled = false;
            });
        });
    }
});

// ============================================
// GANTI HARI
// ============================================
let gantiHariOrderIds = [];

function openGantiHariModal(orderId) {
    gantiHariOrderIds = [];
    
    if (orderId) {
        gantiHariOrderIds.push(orderId);
        updateGantiHariOrders();
    }
    
    document.getElementById('new_tanggal_menu').innerHTML = '<option value="">Pilih Tanggal Jumat</option>';
    document.getElementById('alasan_ganti_hari').value = '';
    document.getElementById('catatan_ganti_hari').value = '';
    
    generateFridayList();
    
    document.getElementById('ganti-hari-modal').classList.remove('hidden');
    document.getElementById('ganti-hari-modal').style.display = 'flex';
}

function closeGantiHariModal() {
    document.getElementById('ganti-hari-modal').classList.add('hidden');
    document.getElementById('ganti-hari-modal').style.display = 'none';
    gantiHariOrderIds = [];
    document.getElementById('ganti-hari-orders-list').innerHTML = '<p class="text-sm text-gray-500">Belum ada order dipilih</p>';
}

function updateGantiHariOrders() {
    if (gantiHariOrderIds.length === 0) {
        document.getElementById('ganti-hari-orders-list').innerHTML = '<p class="text-sm text-gray-500">Belum ada order dipilih</p>';
        return;
    }
    
    fetch('{{ route("admin.orders.bulk-info") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ids: gantiHariOrderIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let html = '';
            let currentDate = '';
            data.orders.forEach((order, index) => {
                html += `
                    <div class="flex justify-between items-center py-1 border-b border-gray-200 last:border-0">
                        <span class="text-sm font-mono">${order.kode_order}</span>
                        <span class="text-sm text-gray-600">${order.tanggal_menu}</span>
                    </div>
                `;
                if (index === 0) {
                    currentDate = order.tanggal_menu_raw || order.tanggal_menu;
                }
            });
            document.getElementById('ganti-hari-orders-list').innerHTML = html;
            document.getElementById('current_tanggal_menu').value = currentDate;
            document.getElementById('current_tanggal_menu_value').value = currentDate;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('❌ Gagal mengambil data order', 'error');
    });
}

// ============================================
// GENERATE LIST HARI JUMAT
// ============================================
function generateFridayList() {
    const select = document.getElementById('new_tanggal_menu');
    const currentDate = document.getElementById('current_tanggal_menu_value').value;
    
    select.innerHTML = '<option value="">Pilih Tanggal Jumat</option>';
    
    const today = new Date();
    const fridays = [];
    
    let friday = new Date(today);
    friday.setDate(today.getDate() + (5 - today.getDay() + 7) % 7);
    
    for (let i = 0; i < 4; i++) {
        const date = new Date(friday);
        date.setDate(friday.getDate() + (i * 7));
        
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const dateStr = year + '-' + month + '-' + day;
        const displayStr = day + '/' + month + '/' + year + ' (Jumat)';
        
        if (dateStr === currentDate) continue;
        
        fridays.push({ value: dateStr, display: displayStr });
    }
    
    fridays.forEach(f => {
        const option = document.createElement('option');
        option.value = f.value;
        option.textContent = f.display;
        select.appendChild(option);
    });
}

// ============================================
// SUBMIT GANTI HARI FORM
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-ganti-hari');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newDate = document.getElementById('new_tanggal_menu').value;
            const alasan = document.getElementById('alasan_ganti_hari').value;
            const catatan = document.getElementById('catatan_ganti_hari').value;
            
            if (!newDate) {
                showNotification('⚠️ Pilih tanggal baru!', 'warning');
                return;
            }
            
            if (!alasan) {
                showNotification('⚠️ Pilih alasan ganti hari!', 'warning');
                return;
            }
            
            const btnText = document.getElementById('gantiHariBtnText');
            const btnLoading = document.getElementById('gantiHariBtnLoading');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            submitBtn.disabled = true;
            
            fetch('{{ route("admin.orders.ganti-hari") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_ids: gantiHariOrderIds,
                    new_tanggal_menu: newDate,
                    alasan: alasan,
                    catatan: catatan
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✅ ' + data.message, 'success');
                    setTimeout(() => {
                        closeGantiHariModal();
                        location.reload();
                    }, 1500);
                } else {
                    let errorMsg = '❌ Gagal ganti hari!';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    showNotification(errorMsg, 'error');
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ ' + error.message, 'error');
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
                submitBtn.disabled = false;
            });
        });
    }
});

// ============================================
// CLOSE MODAL WITH ESCAPE KEY
// ============================================
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeGantiHariModal();
        closeTransferModal();
    }
});

// ============================================
// CLICK OUTSIDE MODAL TO CLOSE
// ============================================
document.addEventListener('click', function(event) {
    const gantiHariModal = document.getElementById('ganti-hari-modal');
    if (gantiHariModal && !gantiHariModal.classList.contains('hidden')) {
        if (event.target === gantiHariModal) {
            closeGantiHariModal();
        }
    }
    
    const transferModal = document.getElementById('transfer-modal');
    if (transferModal && !transferModal.classList.contains('hidden')) {
        if (event.target === transferModal) {
            closeTransferModal();
        }
    }
});
</script>

<style>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animasi fade */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

#tableBody tr {
    animation: fadeIn 0.3s ease-out;
}

/* Focus style */
input:focus {
    outline: none;
}

/* Transisi smooth */
.transition-all {
    transition: all 0.2s ease;
}

/* Checkbox styling */
input[type="checkbox"] {
    cursor: pointer;
}

input[type="checkbox"]:checked {
    background-color: #2563eb;
    border-color: #2563eb;
}

/* Pagination styling */
.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 4px;
}

.pagination li {
    display: inline-block;
}

.pagination li a,
.pagination li span {
    display: block;
    padding: 6px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s;
}

.pagination li a:hover {
    background-color: #f3f4f6;
}

.pagination li.active span {
    background-color: #2563eb;
    color: white;
    border-color: #2563eb;
}

.pagination li.disabled span {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
@endsection