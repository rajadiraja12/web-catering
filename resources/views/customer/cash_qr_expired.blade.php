{{-- resources/views/customer/cash_qr_expired.blade.php --}}

@extends('app')
@section('content')
<style>
    body {
        background: linear-gradient(135deg, #fc8181 0%, #e53e3e 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .expired-card {
        background: white;
        border-radius: 24px;
        padding: 40px;
        max-width: 450px;
        width: 100%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        animation: slideUp 0.5s ease-out;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .expired-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }
    
    .expired-card h1 {
        font-size: 28px;
        color: #e53e3e;
        margin: 0 0 10px;
    }
    
    .expired-card p {
        color: #718096;
        font-size: 16px;
        margin: 5px 0;
    }
    
    .expired-card .order-id {
        background: #f7fafc;
        padding: 12px;
        border-radius: 12px;
        margin: 20px 0;
        font-family: monospace;
        font-size: 18px;
        font-weight: bold;
        color: #2d3748;
    }
    
    .btn-group {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        justify-content: center;
    }
    
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: #4299e1;
        color: white;
    }
    
    .btn-primary:hover {
        background: #3182ce;
        transform: translateY(-2px);
    }
    
    .btn-success {
        background: #48bb78;
        color: white;
    }
    
    .btn-success:hover {
        background: #38a169;
        transform: translateY(-2px);
    }
</style>

<div class="expired-card">
    <div class="expired-icon"></div>
    <h1>QR Code Kadaluarsa!</h1>
    <p>Waktu pembayaran sudah habis.</p>
    <p style="font-size: 14px; color: #a0aec0;">QR Code hanya berlaku 24 jam</p>
    
    <div class="order-id">
        {{ $pending->order_id }}
    </div>
    
    <p style="font-size: 14px; color: #718096;">
        Silakan buat pesanan baru untuk melakukan pembayaran
    </p>
    
    <div class="btn-group">
        <a href="{{ route('customer.dashboard') }}" class="btn btn-primary">
            Dashboard
        </a>
        <a href="{{ route('customer.orders') }}" class="btn btn-success">
            Pesanan Saya
        </a>
    </div>
</div>
@endsection