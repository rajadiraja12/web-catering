<?php

use App\Http\Controllers\Admin\LogActivityController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserImportExportController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])->group(function () {
    Route::get('/2fa/verify', [TwoFactorController::class, 'showVerifyForm'])
        ->name('2fa.verify')
        ->withoutMiddleware([\App\Http\Middleware\CheckActiveSession::class]);
    
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])
        ->name('2fa.verify.post')
        ->withoutMiddleware([\App\Http\Middleware\CheckActiveSession::class]);
});

// routes/web.php
Route::get('/test-session', function () {
    session(['test' => 'Hello World']);
    session()->save();
    
    $sessionId = session()->getId();
    $exists = DB::table('sessions')->where('id', $sessionId)->exists();
    
    return [
        'session_id' => $sessionId,
        'saved_in_db' => $exists,
        'session_data' => DB::table('sessions')->where('id', $sessionId)->first()
    ];
});
Route::post('/login/force', [AuthController::class, 'forceLogin'])->name('login.force');
// ============ AUTH ROUTES (Tanpa middleware auth bawaan) ============
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/welcome', [AuthController::class, 'welcome'])->name('welcome');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/syarat-ketentuan', [AuthController::class, 'sk'])->name('terms');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/2fa/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'showVerifyForm'])->name('2fa.verify')->withoutMiddleware([\App\Http\Middleware\CheckActiveSession::class]);
Route::post('/2fa/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('2fa.verify.post')->withoutMiddleware([\App\Http\Middleware\CheckActiveSession::class]);
Route::get('/2fa/setup', [App\Http\Controllers\Auth\TwoFactorController::class, 'setup'])->name('2fa.setup')->withoutMiddleware([\App\Http\Middleware\CheckActiveSession::class]);
Route::post('/2fa/enable', [App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('2fa.enable')->withoutMiddleware([\App\Http\Middleware\CheckActiveSession::class]);
Route::post('/2fa/disable', [App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('2fa.disable')->withoutMiddleware([\App\Http\Middleware\CheckActiveSession::class]);


// ============ ADMIN DASHBOARD ROUTES ============
Route::middleware(['check.session:admin','check.expired'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/menus/check-quota', [AdminController::class, 'checkMenuQuota'])->name('menus.check-quota');

    Route::put('/users/{userId}/hutang-limit', [AdminController::class, 'updateHutangLimit'])->name('users.hutang-limit');
    // User Management
    Route::get('/users', [AdminController::class, 'manageUsers'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    
    // Hutang Management
    Route::get('/hutang', [AdminController::class, 'hutang'])->name('hutang.index');
    Route::get('/hutang/{id}', [AdminController::class, 'detail'])->name('hutang.detail');
    Route::post('/hutang/{id}/confirm', [AdminController::class, 'confirm'])->name('hutang.confirm');
    Route::post('/hutang/{id}/reject', [AdminController::class, 'reject'])->name('hutang.reject');

    // Menu Management
    Route::get('/menus', [AdminController::class, 'manageMenus'])->name('menus');
    Route::post('/menus', [AdminController::class, 'storeMenu'])->name('menus.store');
    Route::put('/menus/{id}', [AdminController::class, 'updateMenu'])->name('menus.update');
    Route::patch('/menus/{id}/toggle-status', [AdminController::class, 'toggleMenuStatus'])->name('menus.toggle-status');
    Route::delete('/menus/{id}', [AdminController::class, 'deleteMenu'])->name('menus.delete');
    
    Route::get('/payment', [AdminPaymentController::class, 'index'])->name('payment');
    Route::put('/payment/{id}', [AdminPaymentController::class, 'update'])->name('payment.update');
    Route::patch('/payment/{id}/toggle', [AdminPaymentController::class, 'toggleActive'])->name('payment.toggle');
    Route::patch('/payment/{id}/toggle-method', [AdminPaymentController::class, 'toggleMethod'])->name('payment.toggle-method');
    Route::post('/payment/{id}/upload-qris', [AdminPaymentController::class, 'uploadQris'])->name('payment.upload-qris');
    Route::post('/payment', [App\Http\Controllers\Admin\PaymentController::class, 'store'])->name('payment.store');
    Route::delete('/payment/{id}', [App\Http\Controllers\Admin\PaymentController::class, 'destroy'])->name('payment.destroy');
    Route::post('/payment/{id}/upload-qris', [App\Http\Controllers\Admin\PaymentController::class, 'uploadQris'])->name('payment.upload-qris');
    Route::put('/payment/{id}/banks', [App\Http\Controllers\Admin\PaymentController::class, 'updateBanks'])->name('payment.update-banks');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/print/{id}', [OrderController::class, 'print'])->name('orders.print');
    Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/orders/transfer', [OrderController::class, 'transferOrder'])->name('orders.transfer');
    Route::post('/orders/ganti-hari', [OrderController::class, 'gantiHari'])->name('orders.ganti-hari');
    Route::post('/orders/bulk-info', [OrderController::class, 'bulkOrderInfo'])->name('orders.bulk-info');
    
    // Export Excel
    Route::get('/orders/export-excel-daily', [OrderController::class, 'exportExcelDaily'])->name('orders.export-excel-daily');
    Route::get('/orders/export-excel-monthly', [OrderController::class, 'exportExcelMonthly'])->name('orders.export-excel-monthly');
    Route::get('/orders/export-excel-filtered', [OrderController::class, 'exportExcelFiltered'])->name('orders.export-excel-filtered');

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/expense', [AdminController::class, 'storeExpense'])->name('reports.expense');
    
    // Log Activities
    Route::get('/log-activity', [LogActivityController::class, 'index'])->name('log-activity.index');
    Route::delete('/log-activity/clear', [LogActivityController::class, 'clear'])->name('log-activity.clear');
    Route::get('/log-activity/export', [LogActivityController::class, 'exportExcel'])->name('log-activity.export');
    
    // Backups
    Route::get('/backups', [AdminController::class, 'backups'])->name('backups');
    Route::post('/backups/transaksi', [AdminController::class, 'backupTransaksi'])->name('backups.transaksi');
    Route::post('/backups', [AdminController::class, 'createBackup'])->name('backups.store');
    Route::get('/backups/{id}/download', [AdminController::class, 'downloadBackup'])->name('backups.download');
    Route::delete('/backups/{id}', [AdminController::class, 'deleteBackup'])->name('backups.delete');
    
    Route::get('/topup', [App\Http\Controllers\Admin\TopUpController::class, 'index'])->name('topup.index');
    Route::get('/export/excel', [App\Http\Controllers\Admin\TopUpController::class, 'exportExcel'])->name('export.excel');
    Route::get('/{id}', [App\Http\Controllers\Admin\TopUpController::class, 'detail'])->name('topup.detail');
    Route::get('/export/pdf', [App\Http\Controllers\Admin\TopUpController::class, 'exportPdf'])->name('export.pdf');

    Route::get('/admin/confirm-cash/{orderId}', [AdminController::class, 'confirmCashPayment'])->name('admin.confirm.cash');
    Route::post('/admin/confirm-cash/{orderId}', [AdminController::class, 'processConfirmCash'])->name('admin.process.confirm.cash');

    // Active Sessions
    Route::get('/active-sessions', [AdminController::class, 'activeSessions'])->name('active-sessions');
    
    // Export
    Route::get('/export', [AdminController::class, 'exportData'])->name('export');

    Route::get('/users/export', [UserImportExportController::class, 'export'])->name('users.export');
    Route::get('/users/import', [UserImportExportController::class, 'showImportForm'])->name('users.import-form');
    Route::post('/users/import', [UserImportExportController::class, 'import'])->name('users.import');
    Route::get('/users/template', [UserImportExportController::class, 'downloadTemplate'])->name('users.template');
    // Customer Detail
    Route::get('/customers', [AdminController::class, 'customerDashboard'])->name('customers');
    Route::get('/customers/{id}', [AdminController::class, 'customerDashboard'])->name('customers.detail');
    // Route::middleware(['check.session:admin','check.expired'])->prefix('cashier')->name('cashier.')->group(function() {
        Route::get('cashier/dashboard', [App\Http\Controllers\Admin\CashierController::class, 'index'])->name('cashier.dashboard');
        Route::get('cashier/scan', [App\Http\Controllers\Admin\CashierController::class, 'scan'])->name('cashier.scan');
        Route::get('cashier/confirm/{orderId}', [App\Http\Controllers\Admin\CashierController::class, 'confirm'])->name('cashier.confirm');
        Route::post('cashier/confirm/{orderId}', [App\Http\Controllers\Admin\CashierController::class, 'processConfirm'])->name('cashier.process.confirm');
        Route::get('cashier/status/{orderId}', [App\Http\Controllers\Admin\CashierController::class, 'getStatus'])->name('cashier.status');
        Route::get('cashier/history', [App\Http\Controllers\Admin\CashierController::class, 'history'])->name('cashier.history');
        Route::post('cashier/cashier/cancel/{orderId}', [App\Http\Controllers\Admin\CashierController::class, 'cancel'])->name('cashier.cashier.cancel');
    // });
});


Route::middleware(['check.session:admin','check.expired'])->prefix('cashier')->name('cashier.')->group(function() {
    Route::get('/dashboard', [App\Http\Controllers\Admin\CashierController::class, 'index'])->name('dashboard');
    Route::get('/scan', [App\Http\Controllers\Admin\CashierController::class, 'scan'])->name('scan');
    Route::get('/confirm/{orderId}', [App\Http\Controllers\Admin\CashierController::class, 'confirm'])->name('confirm');
    Route::post('/confirm/{orderId}', [App\Http\Controllers\Admin\CashierController::class, 'processConfirm'])->name('process.confirm');
    Route::get('/status/{orderId}', [App\Http\Controllers\Admin\CashierController::class, 'getStatus'])->name('status');
    Route::get('/history', [App\Http\Controllers\Admin\CashierController::class, 'history'])->name('history');
    Route::post('/cashier/cancel/{orderId}', [App\Http\Controllers\Admin\CashierController::class, 'cancel'])->name('cashier.cancel');
});

// ============ CASHIER ROUTES ============
Route::middleware(['check.session:kasir','check.expired'])->prefix('kasir')->name('kasir.')->group(function() {
    Route::get('/dashboard', [App\Http\Controllers\Kasir\CashierController::class, 'index'])->name('dashboard');
    Route::get('/scan', [App\Http\Controllers\Kasir\CashierController::class, 'scan'])->name('scan');
    Route::get('/confirm/{orderId}', [App\Http\Controllers\Kasir\CashierController::class, 'confirm'])->name('confirm');
    Route::post('/confirm/{orderId}', [App\Http\Controllers\Kasir\CashierController::class, 'processConfirm'])->name('process.confirm');
    Route::get('/status/{orderId}', [App\Http\Controllers\Kasir\CashierController::class, 'getStatus'])->name('status');
    Route::get('/history', [App\Http\Controllers\Kasir\CashierController::class, 'history'])->name('history');
    Route::post('/kasir/cancel/{orderId}', [App\Http\Controllers\Kasir\CashierController::class, 'cancel'])->name('kasir.cancel');
    Route::get('/order-manual', [App\Http\Controllers\Kasir\OrderManualController::class, 'index'])->name('order.manual');
    Route::get('/order-manual/get-menu', [App\Http\Controllers\Kasir\OrderManualController::class, 'getMenuByDate'])->name('order.get-menu');
    Route::get('/order-manual/search-customer', [App\Http\Controllers\Kasir\OrderManualController::class, 'searchCustomer'])->name('order.search-customer');
    Route::post('/order-manual/store', [App\Http\Controllers\Kasir\OrderManualController::class, 'store'])->name('order.store');
    Route::post('/order-manual/{id}/update-payment', [App\Http\Controllers\Kasir\OrderManualController::class, 'updatePayment'])->name('order.update-payment');
    Route::delete('/order-manual/{id}', [App\Http\Controllers\Kasir\OrderManualController::class, 'destroy'])->name('order.destroy');
    Route::get('/order-manual/{id}/print-struk', [App\Http\Controllers\Kasir\OrderManualController::class, 'printStruk'])->name('order.print-struk');
    Route::get('/order/{id}/print-struk', [App\Http\Controllers\Kasir\OrderManualController::class, 'print'])->name('order.print');

    // Customers
    Route::get('/customers', [App\Http\Controllers\Kasir\CustomerController::class, 'index'])->name('customers');
    Route::get('/customers/search', [App\Http\Controllers\Kasir\CustomerController::class, 'search'])->name('customers.search');
    Route::get('/customers/{id}/edit', [App\Http\Controllers\Kasir\CustomerController::class, 'edit'])->name('customers.edit');
    Route::post('/customers', [App\Http\Controllers\Kasir\CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{id}', [App\Http\Controllers\Kasir\CustomerController::class, 'update'])->name('customers.update');
    Route::patch('/customers/{id}/toggle-status', [App\Http\Controllers\Kasir\CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::delete('/customers/{id}', [App\Http\Controllers\Kasir\CustomerController::class, 'destroy'])->name('customers.delete');
});

// ============ CUSTOMER ROUTES ============
Route::middleware(['check.session:customer','check.expired'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [CustomerController::class, 'updateProfile'])->name('profile.update');
    Route::post('/credit/pay', [App\Http\Controllers\Customer\PaymentController::class, 'payCredit'])->name('credit.pay');
    // Order History / Pesanan Saya
    Route::get('/orders', [CustomerController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [CustomerController::class, 'detail'])->name('order.detail');
    Route::post('/customer/order/{id}/pay', [CustomerController::class, 'payOrder'])->name('customer.order.pay');
    Route::delete('/customer/order/{id}/cancel', [CustomerController::class, 'cancelOrder'])->name('customer.order.cancel');
    // Cart / Keranjang
    Route::get('/cart', [CustomerController::class, 'cart'])->name('cart');
    Route::patch('/cart/{id}/update', [CustomerController::class, 'updateCartQuantity'])->name('cart.update');
    Route::delete('/cart/{id}/remove', [CustomerController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/checkout', [CustomerController::class, 'checkout'])->name('checkout');
    
    Route::get('/payment', [CustomerPaymentController::class, 'index'])->name('payment');
    Route::post('/payment/process', [CustomerPaymentController::class, 'process'])->name('payment.process');
    Route::get('/payment/success', [CustomerPaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/status/{orderId}', [CustomerPaymentController::class, 'checkPaymentStatus'])->name('payment.status');
    Route::get('/payment/failed', [CustomerPaymentController::class, 'failed'])->name('payment.failed');
    Route::get('/payment/receipt/{orderId}', [CustomerPaymentController::class, 'downloadReceipt'])->name('customer.payment.receipt');
    
    Route::get('/pending-payments', [CustomerPaymentController::class, 'pendingPayments'])->name('pending-payments');
    Route::delete('/pending-payment/{id}', [CustomerPaymentController::class, 'cancelPendingPayment'])->name('pending-payment.cancel');
    Route::get('/pending-payment/continue/{id}', [CustomerPaymentController::class, 'continuePayment'])->name('pending-payment.continue');

    Route::get('/topup', [App\Http\Controllers\Customer\TopUpController::class, 'index'])->name('topup.index');
    Route::get('/create', [App\Http\Controllers\Customer\TopUpController::class, 'create'])->name('topup.create');
    Route::post('/store', [App\Http\Controllers\Customer\TopUpController::class, 'store'])->name('topup.store');
    Route::get('/callback', [App\Http\Controllers\Customer\TopUpController::class, 'callback'])->name('topup.callback');
    Route::get('/failed', [App\Http\Controllers\Customer\TopUpController::class, 'failed'])->name('topup.failed');
    Route::get('/{id}', [App\Http\Controllers\Customer\TopUpController::class, 'detail'])->name('topup.detail');

    // Menu
    Route::get('/menus/{date}', [CustomerController::class, 'menusByDate'])->name('menus.by-date');
    Route::post('/cart/add', [CustomerController::class, 'addToCart'])->name('cart.add');
    
});

// ============ QR CODE ROUTES ============
Route::middleware(['check.session'])->group(function () {
    Route::get('/cash-qr/{orderId}', [App\Http\Controllers\Customer\CashQRController::class, 'show'])
        ->name('cash.qr.show');
    Route::get('/cash-qr/status/{orderId}', [App\Http\Controllers\Customer\CashQRController::class, 'checkStatus'])
        ->name('cash.qr.status');
    Route::get('/cash-qr/download/{orderId}', [App\Http\Controllers\Customer\CashQRController::class, 'download'])
        ->name('cash.qr.download');
    Route::get('/cash-qr/print/{orderId}', [App\Http\Controllers\Customer\CashQRController::class, 'print'])
        ->name('cash.qr.print');
});

// ============ REDIRECT ROOT ============
Route::get('/', function () {
    if (session()->has('user_id')) {
        $role = session('user_role');
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        elseif ($role === 'kasir') {
            return redirect()->route('kasir.dashboard');
        }
        return redirect()->route('customer.dashboard');
    }
    return view('auth.welcome');
})->name('welcome');

// ============ DEBUG ROUTE ============
Route::get('/debug-session', function () {
    if (!session()->has('user_id')) {
        return [
            'status' => 'Not logged in',
            'session_id' => session()->getId(),
            'has_user_id' => false
        ];
    }
    
    $user = \App\Models\User::find(session('user_id'));
    return [
        'status' => 'Logged in',
        'user_id' => session('user_id'),
        'username' => session('user_username'),
        'user_role' => session('user_role'),
        'session_id_from_user' => $user ? $user->session_id : null,
        'current_session_id' => session()->getId(),
        'is_match' => $user && $user->session_id === session()->getId(),
        'session_data' => DB::table('sessions')->where('id', session()->getId())->first(),
        'all_session' => session()->all()
    ];
});

// ============ FORCE LOGOUT ROUTE (Untuk Test) ============
Route::get('/force-logout', function () {
    session()->forget(['user_id', 'user_name', 'user_role', 'user_username']);
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/login')->with('success', 'Berhasil logout');
});