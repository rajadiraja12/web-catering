{{-- resources/views/admin/dashboard.blade.php --}}
@extends('app')
@include('layout.admin')
@section('content')
@yield('nav')

<div class="min-h-screen w-full pt-28 pb-6 px-6 flex flex-col box-border bg-white dark:bg-gray-800"> 
    <div class="w-full flex-1 flex flex-col justify-between">
        <br>
        <div class="mb-4 flex-shrink-0">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="text-sm mt-1 text-gray-600 dark:text-gray-400">Welcome to the admin dashboard!</p>
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Google Authenticator</h3>
            
            @if($user->google2fa_enabled)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-600 font-medium">2FA Aktif</p>
                        <p class="text-sm text-gray-500">Akun Anda dilindungi dengan 2 langkah verifikasi</p>
                        <p class="text-xs text-gray-400">Diverifikasi: {{ $user->google2fa_verified_at ? date('d-m-Y H:i', strtotime($user->google2fa_verified_at)) : '-' }}</p>
                    </div>
                    <form method="POST" action="{{ route('2fa.disable') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all">
                            Nonaktifkan
                        </button>
                    </form>
                </div>
            @else
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-600 font-medium">2FA Tidak Aktif</p>
                        <p class="text-sm text-gray-500">Aktifkan 2FA untuk keamanan ekstra</p>
                    </div>
                    <a href="{{ route('2fa.setup') }}" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-all">
                        Setup 2FA
                    </a>
                </div>
            @endif
        </div>
        </div>
        
        {{-- STATISTIK CARD --}}
        {{-- <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
            {{-- Total Users --}
            <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalUsers ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    <span class="text-gray-500">Active: {{ $totalActiveUsers ?? 0 }}</span>
                </div>
            </div>

            {{-- Total Menus -
            <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Menu</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalMenus ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    <span class="text-gray-500">Menu tersedia</span>
                </div>
            </div>

            {{-- Total Orders Today 
            <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pesanan Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalOrdersToday ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    <span class="text-gray-500">Total: {{ $totalOrders ?? 0 }}</span>
                </div>
            </div>

            {{-- Total Revenue Today 
            <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pendapatan Hari Ini</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">Rp {{ number_format($totalRevenueToday ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    <span class="text-gray-500">Total: Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Total Expense Today 
            <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pengeluaran Hari Ini</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">Rp {{ number_format($totalExpenseToday ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-1 text-xs">
                    <span class="text-gray-500">Total: Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div> --}}

        
        {{-- GRAFIK & STATISTIK --}}
        <div class="flex flex-col lg:flex-row gap-6 flex-1 min-h-0 w-full items-stretch">
            
            {{-- Grafik --}}
            <div class="w-full lg:w-[65%] bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col">
                <div class="flex items-center justify-between mb-2 flex-shrink-0">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Grafik Penjualan 7 Hari Terakhir</h3>
                    <span class="text-xs text-gray-500">Real-time data</span>
                </div>
                <div class="relative w-full flex-1 min-h-0">
                    <canvas id="salesLineChart"></canvas>
                </div>
            </div>

            {{-- Statistik Cepat --}}
            <div class="w-full lg:w-[35%] flex flex-col gap-6">
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col justify-between flex-1 min-h-0">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Total Pesanan</h3>
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-gray-800 p-2 rounded-lg text-xl">shopping_bag</span>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalOrders ?? 0 }}</p>
                    </div>
                    <div class="mt-2 flex items-center text-xs text-green-500">
                        <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
                        <span>Total semua pesanan</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700 flex flex-col justify-between flex-1 min-h-0">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Total Pendapatan</h3>
                            <span class="material-symbols-outlined text-green-600 dark:text-green-400 bg-green-50 dark:bg-gray-800 p-2 rounded-lg text-xl">payments</span>
                        </div>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="mt-2 flex items-center text-xs text-green-500">
                        <span class="material-symbols-outlined text-sm mr-1">trending_up</span>
                        <span>Total pendapatan keseluruhan</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- LOG AKTIVITAS TERBARU --}}
        @if(isset($recentActivities) && $recentActivities->count() > 0)
        <div class="mt-6 bg-white dark:bg-gray-900 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru</h3>
                {{-- PERBAIKAN: Gunakan route yang benar --}}
                <a href="{{ route('admin.log-activity.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">User</th>
                            <th scope="col" class="px-4 py-3">Aksi</th>
                            <th scope="col" class="px-4 py-3">Module</th>
                            <th scope="col" class="px-4 py-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $log)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $log->user ? $log->user->name : 'System' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full dark:bg-blue-900 dark:text-blue-300">
                                    {{ $log->aksi }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $log->module }}</td>
                            <td class="px-4 py-3">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('salesLineChart').getContext('2d');
        
        // Data dari controller
        const chartData = @json($chartData ?? ['labels' => [], 'orders' => [], 'revenues' => []]);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels.length > 0 ? chartData.labels : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
                datasets: [{
                    label: 'Pesanan',
                    data: chartData.orders.length > 0 ? chartData.orders : [12, 19, 3, 5, 2, 3, 9],
                    borderColor: 'rgb(37, 99, 235)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        display: true,
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#fff' : '#333'
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                        }
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                        }
                    }
                }
            }
        });
    });
</script>
@endsection