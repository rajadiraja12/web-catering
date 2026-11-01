{{-- resources/views/terms/index.blade.php --}}

@extends('app')
@include('layout.utama')
@section('content')
@yield('nav')
<br>
<br>
<br>
<br>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg mb-4">
                <img src="image/logo.png" alt="logo">
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Syarat & Ketentuan</h1>
            <p class="text-lg font-semibold text-blue-600 mb-1">Hanif Catering</p>
        </div>

        {{-- Intro --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 mb-6 border border-blue-100">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-700 leading-relaxed">
                    Dengan menggunakan website dan melakukan pemesanan katering, pengguna dianggap telah membaca, 
                    memahami, dan menyetujui Syarat dan Ketentuan berikut.
                </p>
            </div>
        </div>

        {{-- Card Content --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 md:p-8 space-y-8">

                {{-- A. PEMESANAN KATERING --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-lg">A</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Pemesanan Katering</h2>
                    </div>
                    <ol class="space-y-3 text-gray-600 ml-14 list-decimal list-inside">
                        <li>Hanif Catering menyediakan makanan katering secara nyata (offline) untuk siswa di lingkungan sekolah.</li>
                        <li>Pemesanan dilakukan melalui website resmi Hanif Catering.</li>
                        <li>Pengguna wajib mengisi data dengan benar, termasuk nama siswa, kelas, tanggal pemesanan, dan pilihan menu.</li>
                        <li>Pengguna bertanggung jawab atas kebenaran data yang dimasukkan pada saat pemesanan.</li>
                        <li>Pesanan yang telah dibayar dan dikonfirmasi akan diproses sesuai dengan tanggal dan menu yang dipilih.</li>
                    </ol>
                </section>

                <hr class="border-gray-200">

                {{-- B. BATAS WAKTU PEMESANAN --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                            <span class="text-red-600 font-bold text-lg">B</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Batas Waktu Pemesanan</h2>
                    </div>
                    <ol class="space-y-3 text-gray-600 ml-14 list-decimal list-inside">
                        <li>Pemesanan katering untuk hari yang sama (H-0) ditutup setiap hari pada pukul <strong class="text-red-600">07.30 WIB</strong>.</li>
                        <li>Setelah pukul 07.30 WIB, website tidak lagi menerima pemesanan untuk hari tersebut.</li>
                        <li>Pesanan yang telah ditutup tidak dapat dilakukan melalui sistem website.</li>
                    </ol>
                    <div class="ml-14 mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-700">
                            <strong>Penting:</strong> Pastikan pesanan dilakukan sebelum pukul 07.30 WIB untuk hari yang sama.
                        </p>
                    </div>
                </section>

                <hr class="border-gray-200">

                {{-- C. PEMBAYARAN --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                            <span class="text-green-600 font-bold text-lg">C</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Pembayaran</h2>
                    </div>
                    <ol class="space-y-3 text-gray-600 ml-14 list-decimal list-inside">
                        <li>Pembayaran dilakukan melalui sistem pembayaran pihak ketiga, yaitu <strong>MIDTRANS</strong>.</li>
                        <li>Pesanan akan diproses setelah pembayaran berhasil dan terkonfirmasi otomatis.</li>
                        <li>Pengguna wajib menyelesaikan pembayaran sesuai dengan instruksi yang tersedia pada website.</li>
                        <li>Apabila terjadi kendala pembayaran, pengguna dapat menghubungi admin Hanif Catering melalui kontak resmi yang tersedia.</li>
                    </ol>
                </section>

                <hr class="border-gray-200">

                {{-- D. PEMBATALAN DAN PENGALIHAN PESANAN --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                            <span class="text-orange-600 font-bold text-lg">D</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Pembatalan dan Pengalihan Pesanan</h2>
                    </div>
                    <ol class="space-y-3 text-gray-600 ml-14 list-decimal list-inside">
                        <li>Pembatalan pesanan karena anak sakit atau berhalangan hadir ke sekolah dapat dilakukan secara manual melalui <strong>WhatsApp resmi Hanif Catering</strong>.</li>
                        <li>Pengguna wajib menghubungi admin Hanif Catering sesegera mungkin setelah mengetahui bahwa anak tidak dapat hadir.</li>
                        <li>Pembatalan atau pengalihan pesanan <strong>tidak dilakukan secara otomatis</strong> melalui website.</li>
                        <li>Pengalihan pesanan ke hari lain dilakukan berdasarkan komunikasi dan persetujuan admin Hanif Catering.</li>
                        <li>Pengalihan diberikan sesuai dengan jumlah hari katering yang tidak digunakan.</li>
                        <li>
                            <strong>Contoh:</strong> Apabila pengguna telah memesan katering selama 20 hari dan anak tidak masuk sekolah selama 5 hari karena sakit, 
                            maka 5 hari katering tersebut dapat dialihkan ke hari lain sesuai dengan kesepakatan dan ketersediaan layanan.
                        </li>
                        <li>Pengalihan tidak mengubah jumlah hari katering yang telah dibeli oleh pengguna.</li>
                    </ol>
                    <div class="ml-14 mt-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                        <p class="text-sm text-orange-700">
                            <strong>Hubungi Admin via WhatsApp</strong> untuk pembatalan atau pengalihan pesanan.
                        </p>
                    </div>
                </section>

                <hr class="border-gray-200">

                {{-- E. PENGEMBALIAN DANA (REFUND) --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                            <span class="text-purple-600 font-bold text-lg">E</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Pengembalian Dana (Refund)</h2>
                    </div>
                    <ol class="space-y-3 text-gray-600 ml-14 list-decimal list-inside">
                        <li>Pengembalian dana dapat dilakukan berdasarkan kesepakatan antara Hanif Catering dan pengguna.</li>
                        <li>Refund dilakukan secara manual oleh Hanif Catering setelah dana pembayaran telah diterima dan dapat ditarik oleh Hanif Catering dari sistem pembayaran.</li>
                        <li>
                            Proses refund dilakukan melalui rekening <strong>Bank Syariah Indonesia (BSI)</strong> milik 
                            <strong>Koperasi Konsumen Berkah Sejahtera Alhanif</strong> kepada rekening konsumen yang telah diverifikasi.
                        </li>
                        <li>Proses pengembalian dana dilakukan dalam waktu <strong>2–3 hari kerja</strong> setelah kesepakatan refund tercapai dan data yang diperlukan telah lengkap.</li>
                        <li>Pengguna wajib memberikan informasi rekening yang benar untuk keperluan refund.</li>
                        <li>Pembatalan atau refund <strong>tidak dapat dilakukan secara otomatis</strong> melalui website.</li>
                    </ol>
                </section>

                <hr class="border-gray-200">

                {{-- F. PENGANTARAN DAN PENGAMBILAN KATERING --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center">
                            <span class="text-teal-600 font-bold text-lg">F</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Pengantaran dan Pengambilan Katering</h2>
                    </div>
                    <ol class="space-y-3 text-gray-600 ml-14 list-decimal list-inside">
                        <li>Katering akan diantarkan ke kelas masing-masing siswa sekitar pukul <strong>12.00 WIB</strong>.</li>
                        <li>
                            <strong>Khusus siswa laki-laki kelas 4, 5, dan 6</strong>, katering wajib diambil sendiri di kantor Hanif Catering.
                        </li>
                        <li>Waktu pengantaran dapat mengalami penyesuaian sesuai dengan kondisi operasional dan kegiatan sekolah.</li>
                        <li>Pengguna wajib memastikan data nama siswa dan kelas yang dimasukkan pada website sudah benar.</li>
                    </ol>
                    <div class="ml-14 mt-3 p-3 bg-teal-50 border border-teal-200 rounded-lg">
                        <p class="text-sm text-teal-700">
                            <strong>Info:</strong> Pengantaran sekitar pukul 12.00 WIB. Siswa laki-laki kelas 4-6 mengambil sendiri di kantor.
                        </p>
                    </div>
                </section>

                <hr class="border-gray-200">

                {{-- G. MENU DAN KETERSEDIAAN MAKANAN --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                            <span class="text-yellow-600 font-bold text-lg">G</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Menu dan Ketersediaan Makanan</h2>
                    </div>
                    <ol class="space-y-3 text-gray-600 ml-14 list-decimal list-inside">
                        <li>Hanif Catering berupaya menyediakan makanan sesuai dengan menu yang dipilih pengguna.</li>
                        <li>Dalam kondisi tertentu, Hanif Catering dapat melakukan perubahan menu karena ketersediaan bahan baku atau kendala operasional.</li>
                        <li>Apabila terjadi perubahan menu, Hanif Catering akan mengupayakan pemberitahuan kepada pengguna.</li>
                        <li>Pengguna dapat menghubungi admin Hanif Catering apabila memiliki pertanyaan mengenai menu atau pesanan.</li>
                    </ol>
                </section>

                <hr class="border-gray-200">

                {{-- H. KELUHAN DAN LAYANAN PELANGGAN --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center">
                            <span class="text-pink-600 font-bold text-lg">H</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Keluhan dan Layanan Pelanggan</h2>
                    </div>
                    <ol class="space-y-3 text-gray-600 ml-14 list-decimal list-inside">
                        <li>Keluhan terkait pesanan, pembayaran, kualitas makanan, pengantaran, atau pengambilan katering dapat disampaikan melalui <strong>WhatsApp resmi Hanif Catering</strong>.</li>
                        <li>Pengguna diharapkan menyampaikan keluhan sesegera mungkin dengan mencantumkan nama siswa, kelas, tanggal pesanan, dan informasi yang diperlukan.</li>
                        <li>Hanif Catering akan menindaklanjuti keluhan sesuai dengan kondisi dan hasil pemeriksaan.</li>
                    </ol>
                </section>

                <hr class="border-gray-200">

                {{-- I. GANGGUAN LAYANAN --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                            <span class="text-gray-600 font-bold text-lg">I</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Gangguan Layanan</h2>
                    </div>
                    <p class="text-gray-600 ml-14 leading-relaxed">
                        Hanif Catering berupaya menyediakan layanan secara optimal. Namun, perubahan jadwal sekolah, 
                        gangguan sistem, kendala operasional, atau keadaan di luar kendali dapat menyebabkan penyesuaian 
                        terhadap proses pemesanan, pengantaran, atau penyediaan makanan.
                    </p>
                </section>

                <hr class="border-gray-200">

                {{-- J. PERSETUJUAN PENGGUNA --}}
                <section>
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <span class="text-indigo-600 font-bold text-lg">J</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 pt-2">Persetujuan Pengguna</h2>
                    </div>
                    <p class="text-gray-600 ml-14 leading-relaxed">
                        Dengan melakukan pemesanan melalui website, pengguna menyatakan bahwa seluruh informasi yang 
                        diberikan adalah benar dan pengguna telah membaca, memahami, serta menyetujui Syarat dan 
                        Ketentuan Hanif Catering.
                    </p>
                </section>

                {{-- Box Persetujuan --}}
                <div class="mt-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Kontak Resmi</h3>
                            <p class="text-sm text-gray-600 mb-3">
                                Untuk pertanyaan, keluhan, atau pembatalan pesanan, hubungi:
                            </p>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    <span>WhatsApp: 0896-7570-4781</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-700">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Alamat: Kompaks Yayasan Alhanif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- Copyright --}}
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>© {{ date('Y') }} HaniF Catering. All rights reserved.</p>
        </div>
    </div>
</div>

@endsection