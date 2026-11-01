@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>
<br>

<div class="container mx-auto px-4">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pengaturan Payment Gateway</h1>
            <p class="text-sm text-gray-600">Konfigurasi payment gateway dan metode pembayaran</p>
        </div>
        <button onclick="showAddGateway()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            + Tambah Gateway
        </button>
    </div>

    @foreach($payments as $payment)
    <div class="bg-white rounded-2xl shadow-lg border {{ $payment->is_active ? 'border-green-500 ring-2 ring-green-200' : 'border-gray-200' }} overflow-hidden mb-6">
        {{-- Header --}}
        <div class="p-6 border-b border-gray-100 {{ $payment->is_active ? 'bg-gradient-to-r from-green-50 to-white' : 'bg-gray-50' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center text-2xl font-bold text-blue-600">
                        {{ substr($payment->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $payment->name }}</h3>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="text-xs px-2.5 py-0.5 rounded-full {{ $payment->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $payment->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full {{ $payment->is_sandbox ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $payment->is_sandbox ? 'Sandbox' : 'Production' }}
                            </span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                {{ strtoupper($payment->gateway) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="toggleActive({{ $payment->id }})" 
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-all {{ $payment->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                        {{ $payment->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                    @if($payment->gateway != 'cash' && $payment->gateway != 'saldo')
                    <button onclick="deleteGateway({{ $payment->id }})" 
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-red-100 text-red-700 hover:bg-red-200">
                        🗑️
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            {{-- API Settings --}}
            <div class="mb-6">
                <h4 class="font-semibold text-gray-700 mb-3">API Settings</h4>
                <form onsubmit="updatePayment(event, {{ $payment->id }})" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Client / API Key</label>
                        <input type="text" name="api_key" value="{{ $payment->api_key }}" 
                            class="w-full text-sm bg-gray-50 border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">API Secret/ Server Key</label>
                        <input type="password" name="api_secret" value="{{ $payment->api_secret }}" 
                            class="w-full text-sm bg-gray-50 border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    @if($payment->gateway == 'tripay')
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Merchant Code</label>
                        <input type="text" name="merchant_code" value="{{ $payment->merchant_code }}" 
                            class="w-full text-sm bg-gray-50 border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endif
                    @if($payment->gateway == 'midtrans')
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Client Key</label>
                        <input type="text" name="client_key" value="{{ $payment->settings['client_key'] ?? '' }}" 
                            class="w-full text-sm bg-gray-50 border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endif
                    {{-- SALDO Settings --}}
                    @if($payment->gateway == 'saldo' || $payment->gateway == 'internal')
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Min Saldo</label>
                        <input type="number" name="min_saldo" value="{{ $payment->settings['min_saldo'] ?? 0 }}" 
                            class="w-full text-sm bg-gray-50 border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-600 block mb-1">Max Saldo</label>
                        <input type="number" name="max_saldo" value="{{ $payment->settings['max_saldo'] ?? 999999999 }}" 
                            class="w-full text-sm bg-gray-50 border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endif
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_sandbox" value="1" {{ $payment->is_sandbox ? 'checked' : '' }} id="sandbox-{{ $payment->id }}">
                        <label for="sandbox-{{ $payment->id }}" class="text-sm text-gray-600 cursor-pointer">Mode Sandbox (Testing)</label>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all">
                            Simpan API Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- Payment Methods --}}
            <div class="border-t border-gray-200 pt-6">
                <h4 class="font-semibold text-gray-700 mb-3">Metode Pembayaran</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                    {{-- QRIS --}}
                    <div class="border rounded-xl p-4 {{ $payment->qris_enabled ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between flex-col text-center">
                            <div>
                                <span class="text-2xl">📱</span>
                                <p class="font-medium text-sm mt-1">QRIS</p>
                                <p class="text-xs text-gray-500">{{ $payment->qris_enabled ? '✅ Aktif' : '❌ Nonaktif' }}</p>
                            </div>
                            <button onclick="toggleMethod({{ $payment->id }}, 'qris')" 
                                class="mt-2 px-3 py-1 text-xs rounded-lg {{ $payment->qris_enabled ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $payment->qris_enabled ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </div>

                    {{-- Bank Transfer --}}
                    <div class="border rounded-xl p-4 {{ $payment->bank_transfer_enabled ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between flex-col text-center">
                            <div>
                                <span class="text-2xl">🏦</span>
                                <p class="font-medium text-sm mt-1">Transfer Bank</p>
                                <p class="text-xs text-gray-500">{{ $payment->bank_transfer_enabled ? '✅ Aktif' : '❌ Nonaktif' }}</p>
                            </div>
                            <button onclick="toggleMethod({{ $payment->id }}, 'bank_transfer')" 
                                class="mt-2 px-3 py-1 text-xs rounded-lg {{ $payment->bank_transfer_enabled ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $payment->bank_transfer_enabled ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </div>

                    {{-- Kartu Kredit --}}
                    <div class="border rounded-xl p-4 {{ $payment->credit_card_enabled ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between flex-col text-center">
                            <div>
                                <span class="text-2xl">💳</span>
                                <p class="font-medium text-sm mt-1">Kartu Kredit</p>
                                <p class="text-xs text-gray-500">{{ $payment->credit_card_enabled ? '✅ Aktif' : '❌ Nonaktif' }}</p>
                            </div>
                            <button onclick="toggleMethod({{ $payment->id }}, 'credit_card')" 
                                class="mt-2 px-3 py-1 text-xs rounded-lg {{ $payment->credit_card_enabled ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $payment->credit_card_enabled ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </div>

                    {{-- E-Wallet --}}
                    <div class="border rounded-xl p-4 {{ $payment->ewallet_enabled ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between flex-col text-center">
                            <div>
                                <span class="text-2xl">📲</span>
                                <p class="font-medium text-sm mt-1">E-Wallet</p>
                                <p class="text-xs text-gray-500">{{ $payment->ewallet_enabled ? '✅ Aktif' : '❌ Nonaktif' }}</p>
                            </div>
                            <button onclick="toggleMethod({{ $payment->id }}, 'ewallet')" 
                                class="mt-2 px-3 py-1 text-xs rounded-lg {{ $payment->ewallet_enabled ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $payment->ewallet_enabled ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </div>

                    {{-- Retail --}}
                    <div class="border rounded-xl p-4 {{ $payment->retail_enabled ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between flex-col text-center">
                            <div>
                                <span class="text-2xl">🏪</span>
                                <p class="font-medium text-sm mt-1">Retail</p>
                                <p class="text-xs text-gray-500">{{ $payment->retail_enabled ? '✅ Aktif' : '❌ Nonaktif' }}</p>
                            </div>
                            <button onclick="toggleMethod({{ $payment->id }}, 'retail')" 
                                class="mt-2 px-3 py-1 text-xs rounded-lg {{ $payment->retail_enabled ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $payment->retail_enabled ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </div>

                    {{-- CASH --}}
                    <div class="border rounded-xl p-4 {{ $payment->cash_enabled ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between flex-col text-center">
                            <div>
                                <span class="text-2xl">💵</span>
                                <p class="font-medium text-sm mt-1">Tunai</p>
                                <p class="text-xs text-gray-500">{{ $payment->cash_enabled ? '✅ Aktif' : '❌ Nonaktif' }}</p>
                            </div>
                            <button onclick="toggleMethod({{ $payment->id }}, 'cash')" 
                                class="mt-2 px-3 py-1 text-xs rounded-lg {{ $payment->cash_enabled ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $payment->cash_enabled ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </div>

                    {{-- SALDO --}}
                    <div class="border rounded-xl p-4 {{ $payment->saldo_enabled ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between flex-col text-center">
                            <div>
                                <span class="text-2xl">💰</span>
                                <p class="font-medium text-sm mt-1">Saldo</p>
                                <p class="text-xs text-gray-500">{{ $payment->saldo_enabled ? '✅ Aktif' : '❌ Nonaktif' }}</p>
                            </div>
                            <button onclick="toggleMethod({{ $payment->id }}, 'saldo')" 
                                class="mt-2 px-3 py-1 text-xs rounded-lg {{ $payment->saldo_enabled ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                {{ $payment->saldo_enabled ? 'ON' : 'OFF' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Modal Tambah Gateway --}}
<div id="addGatewayModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Tambah Payment Gateway</h3>
        <form onsubmit="addGateway(event)">
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Nama Gateway</label>
                <input type="text" name="name" id="gatewayName" required
                    class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="text-sm font-medium text-gray-700 block mb-1">Tipe Gateway</label>
                <select name="gateway" id="gatewayType" required
                    class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="midtrans">Midtrans</option>
                    <option value="tripay">Tripay</option>
                    <option value="xendit">Xendit</option>
                    <option value="cash">Tunai (Cash)</option>
                    <option value="saldo">Saldo Internal</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Tambahkan
                </button>
                <button type="button" onclick="closeAddGateway()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================
// SHOW ADD GATEWAY MODAL
// ============================================
function showAddGateway() {
    document.getElementById('addGatewayModal').classList.remove('hidden');
    document.getElementById('addGatewayModal').classList.add('flex');
}

function closeAddGateway() {
    document.getElementById('addGatewayModal').classList.add('hidden');
    document.getElementById('addGatewayModal').classList.remove('flex');
}

// ============================================
// ADD GATEWAY
// ============================================
function addGateway(event) {
    event.preventDefault();
    
    const name = document.getElementById('gatewayName').value;
    const gateway = document.getElementById('gatewayType').value;
    
    fetch('{{ route("admin.payment.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: name,
            gateway: gateway,
            is_active: false
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            closeAddGateway();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// DELETE GATEWAY
// ============================================
function deleteGateway(id) {
    if (!confirm('Yakin ingin menghapus payment gateway ini?')) return;
    
    const url = "{{ route('admin.payment.destroy', ['id' => ':id']) }}".replace(':id', id);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// TOGGLE PAYMENT GATEWAY
// ============================================
function toggleActive(id) {
    if (!confirm('Yakin ingin mengubah status payment gateway ini?')) return;
    
    const url = "{{ route('admin.payment.toggle', ['id' => ':id']) }}".replace(':id', id);
    
    fetch(url, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// TOGGLE PAYMENT METHOD
// ============================================
function toggleMethod(id, method) {
    const url = "{{ route('admin.payment.toggle-method', ['id' => ':id']) }}".replace(':id', id);
    
    fetch(url, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ method: method })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// UPDATE PAYMENT API SETTINGS
// ============================================
function updatePayment(event, id) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    const url = "{{ route('admin.payment.update', ['id' => ':id']) }}".replace(':id', id);
    
    fetch(url, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            api_key: data.api_key,
            api_secret: data.api_secret,
            merchant_code: data.merchant_code || null,
            client_key: data.client_key || null,
            is_sandbox: data.is_sandbox ? true : false,
            min_saldo: data.min_saldo || null,
            max_saldo: data.max_saldo || null,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✅ ' + data.message, 'success');
        } else {
            showNotification('❌ ' + (data.message || 'Gagal update!'), 'error');
        }
    })
    .catch(error => {
        showNotification('❌ Terjadi kesalahan!', 'error');
        console.error('Error:', error);
    });
}

// ============================================
// NOTIFICATION
// ============================================
function showNotification(message, type = 'success') {
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
    notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-500 translate-x-0 opacity-100 ${colors[type] || colors.success} text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-3 max-w-md`;
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
    }, 3000);
}
</script>

<style>
/* Custom style for payment cards */
.border-green-500 {
    border-color: #22c55e !important;
}
.bg-green-50 {
    background-color: #f0fdf4 !important;
}
.ring-2 {
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.3);
}
</style>

@endsection