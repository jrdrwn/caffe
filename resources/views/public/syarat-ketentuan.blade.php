<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat &amp; Ketentuan — {{ config('app.name', 'MENCAF') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter','ui-sans-serif','system-ui','sans-serif'] } } }
        }
    </script>
    <style>
        body{font-family:'Inter',ui-sans-serif,system-ui,sans-serif}
        .glass{background:rgba(255,255,255,0.7);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px)}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
        .float-anim{animation:float 6s ease-in-out infinite}
        @keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        .fade-up{animation:fadeUp .6s ease-out forwards}
        .fade-up-d1{animation-delay:.1s;opacity:0}.fade-up-d2{animation-delay:.2s;opacity:0}.fade-up-d3{animation-delay:.3s;opacity:0}
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-indigo-50 min-h-screen">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-violet-100 to-purple-100 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-br from-indigo-100 to-blue-100 rounded-full opacity-50 blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('public.landing') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-all duration-200 mb-8 group fade-up">
            <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Dokumen
        </a>

        <div class="glass rounded-2xl border border-white/60 shadow-xl shadow-indigo-500/5 p-8 sm:p-10 mb-8 fade-up fade-up-d1">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-600 to-purple-600 flex items-center justify-center text-2xl shadow-lg float-anim">📋</div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">Syarat &amp; Ketentuan Penggunaan</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700 mt-1">Dokumen Publik</span>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Terakhir diperbarui: {{ date('d F Y') }}</p>
        </div>

        <div class="glass rounded-2xl border border-white/60 shadow-xl shadow-indigo-500/5 p-8 sm:p-10 fade-up fade-up-d2">
            <div class="prose prose-lg max-w-none space-y-8 text-gray-700">

                {{-- Intro --}}
                <p class="text-gray-600 leading-relaxed">
                    Dokumen ini mengatur penggunaan platform <strong class="text-gray-900">MENCAF (ManajemenCafe)</strong>, yaitu aplikasi manajemen Point of Sale (POS) berbasis web yang ditujukan untuk pengelolaan operasional cafe. Dengan mendaftar, Anda dianggap telah membaca, memahami, dan <strong class="text-gray-900">menyetujui seluruh syarat dan ketentuan berikut</strong>.
                </p>

                {{-- 1 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        1. Penerimaan Ketentuan &amp; Kewajiban Persetujuan Merchant
                    </h3>
                    <ul class="space-y-2 pl-1">
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Setiap merchant (Manajer Cafe) <strong>wajib mendaftar, membaca, dan mencentang persetujuan</strong> terhadap Syarat &amp; Ketentuan ini sebelum dapat menggunakan layanan MENCAF.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Persetujuan dilakukan secara eksplisit melalui checkbox yang tersedia pada formulir pendaftaran di halaman <strong>/manajer/register</strong>.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Merchant yang belum menyetujui Syarat &amp; Ketentuan tidak dapat menyelesaikan proses pendaftaran.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Dengan menggunakan layanan setelah pendaftaran, merchant dianggap terus menyetujui ketentuan yang berlaku termasuk setiap pembaruan yang diumumkan.</span></li>
                    </ul>
                </div>

                {{-- 2 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        2. Akun Pengguna
                    </h3>
                    <ul class="space-y-2 pl-1">
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Pengguna bertanggung jawab untuk memberikan informasi yang benar dan lengkap saat pendaftaran.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Pengguna wajib menjaga kerahasiaan password dan keamanan akun.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Pengguna tidak diperbolehkan memberikan akses akun kepada pihak yang tidak berwenang.</span></li>
                    </ul>
                </div>

                {{-- 3 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        3. Aplikasi POS yang Digunakan
                    </h3>
                    <p class="leading-relaxed mb-3">MENCAF menyediakan aplikasi Point of Sale (POS) berbasis web yang digunakan oleh merchant (Manajer dan Kasir) untuk:</p>
                    <ul class="space-y-2 pl-1">
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Mencatat dan memproses transaksi penjualan produk di cafe secara real-time.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Mengelola produk, kategori, stok, dan harga.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Mencetak struk transaksi digital.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Melihat laporan penjualan harian, mingguan, dan bulanan.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Mengelola akun kasir dan hak aksesnya.</span></li>
                    </ul>
                    <p class="leading-relaxed mt-3">Akses aplikasi POS tersedia melalui panel kasir di <strong>{{ config('app.url') }}/cashier</strong>. Setiap merchant mendapatkan akses POS sesuai paket langganan yang aktif.</p>
                </div>

                {{-- 4 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        4. Alur Penerimaan Dana Transaksi POS (via iPaymu)
                    </h3>
                    <p class="leading-relaxed mb-3">MENCAF menggunakan <strong>iPaymu</strong> sebagai payment gateway untuk dua jenis pembayaran:</p>

                    <div class="bg-violet-50 border border-violet-200 rounded-xl p-5 mb-4">
                        <p class="font-semibold text-violet-900 mb-2">A. Pembayaran Langganan Platform</p>
                        <ul class="space-y-1.5 pl-1">
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span class="text-violet-800 text-sm">Merchant melakukan pembayaran langganan (subscription) ke MENCAF melalui iPaymu.</span></li>
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span class="text-violet-800 text-sm">Dana masuk ke rekening MENCAF sebagai penyelenggara platform.</span></li>
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span class="text-violet-800 text-sm">Langganan yang tersedia: Free, Basic, dan Premium dengan harga berbeda-beda sesuai fitur.</span></li>
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span class="text-violet-800 text-sm">Pembayaran langganan bersifat non-refundable kecuali terdapat gangguan layanan dari pihak MENCAF.</span></li>
                        </ul>
                    </div>

                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-4">
                        <p class="font-semibold text-indigo-900 mb-2">B. Pembayaran Transaksi POS di Cafe</p>
                        <ul class="space-y-1.5 pl-1">
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span><span class="text-indigo-800 text-sm">Saat pelanggan cafe melakukan pembayaran melalui POS yang disediakan MENCAF, transaksi diproses menggunakan iPaymu.</span></li>
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span><span class="text-indigo-800 text-sm"><strong>Dana dari transaksi POS yang berhasil diterima langsung ke rekening/akun iPaymu milik merchant (Manajer Cafe)</strong> yang telah terdaftar dan terverifikasi.</span></li>
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span><span class="text-indigo-800 text-sm">MENCAF bertindak sebagai fasilitator teknologi dan tidak menyimpan atau menahan dana transaksi pelanggan cafe.</span></li>
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span><span class="text-indigo-800 text-sm">Settlement/pencairan dana mengikuti ketentuan dan jadwal yang ditetapkan oleh iPaymu.</span></li>
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span><span class="text-indigo-800 text-sm">Merchant bertanggung jawab memastikan rekening iPaymu-nya aktif dan terverifikasi sebelum mengaktifkan fitur pembayaran digital di POS.</span></li>
                            <li class="flex items-start gap-3"><span class="mt-2 w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span><span class="text-indigo-800 text-sm">Apabila terjadi kegagalan transaksi, dispute, atau chargeback, proses penanganan dilakukan sesuai kebijakan iPaymu dan merchant bertanggung jawab penuh atas transaksi di cafe-nya.</span></li>
                        </ul>
                    </div>

                    <p class="text-sm text-gray-500 leading-relaxed">Dengan menggunakan fitur pembayaran digital di POS MENCAF, merchant menyatakan telah memahami dan menyetujui alur penerimaan dana di atas serta tunduk pada ketentuan iPaymu yang berlaku.</p>
                </div>

                {{-- 5 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        5. Paket Langganan
                    </h3>
                    <ul class="space-y-2 pl-1">
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>MENCAF menyediakan layanan berdasarkan paket berlangganan (Free, Basic, Premium) yang tersedia di website.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Setiap paket memiliki batasan fitur, jumlah pengguna, dan kapasitas yang berbeda.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Harga dan fitur paket dapat berubah sewaktu-waktu dengan pemberitahuan terlebih dahulu.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Pengujian pembayaran langganan dapat dilakukan melalui halaman manajer setelah mendaftar menggunakan paket Free.</span></li>
                    </ul>
                </div>

                {{-- 6 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        6. Pembayaran
                    </h3>
                    <ul class="space-y-2 pl-1">
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Pengguna wajib melakukan pembayaran sesuai harga dan periode berlangganan yang dipilih.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Pembayaran diproses melalui iPaymu. Pengguna wajib memastikan metode pembayaran yang digunakan valid dan memiliki saldo/limit yang cukup.</span></li>
                        <li class="flex items-start gap-3 py-1"><span class="mt-2 w-2 h-2 rounded-full bg-violet-400 flex-shrink-0"></span><span>Untuk kebijakan pengembalian dana, silakan baca <a href="{{ route('public.doc', ['slug' => 'refund-policy']) }}" class="text-violet-600 hover:underline font-medium">Refund Policy</a> kami.</span></li>
                    </ul>
                </div>

                {{-- 7 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        7. Penggunaan Layanan
                    </h3>
                    <p class="leading-relaxed">Pengguna dilarang menggunakan layanan untuk aktivitas yang melanggar hukum, melakukan penyalahgunaan sistem, mencoba mendapatkan akses tanpa izin, atau melakukan aktivitas yang dapat mengganggu keamanan dan operasional platform.</p>
                </div>

                {{-- 8 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        8. Penghentian Akun
                    </h3>
                    <p class="leading-relaxed">MENCAF dapat membatasi atau menghentikan akses akun apabila ditemukan pelanggaran terhadap Syarat &amp; Ketentuan ini, termasuk penyalahgunaan sistem pembayaran atau penipuan transaksi.</p>
                </div>

                {{-- 9 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        9. Ketersediaan Layanan
                    </h3>
                    <p class="leading-relaxed">Kami berusaha menjaga layanan tetap tersedia, tetapi tidak menjamin layanan akan selalu bebas dari gangguan, pemeliharaan, kesalahan teknis, atau downtime.</p>
                </div>

                {{-- 10 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        10. Data Pengguna
                    </h3>
                    <p class="leading-relaxed">Pengguna bertanggung jawab atas data yang dimasukkan ke dalam sistem dan wajib memastikan bahwa data yang digunakan tidak melanggar hak pihak lain atau ketentuan hukum yang berlaku.</p>
                </div>

                {{-- 11 --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-violet-500 to-purple-600 rounded-full flex-shrink-0"></span>
                        11. Perubahan Ketentuan
                    </h3>
                    <p class="leading-relaxed">MENCAF dapat memperbarui Syarat &amp; Ketentuan dari waktu ke waktu. Perubahan akan diinformasikan melalui website atau media komunikasi yang tersedia. Penggunaan layanan yang berkelanjutan setelah perubahan dianggap sebagai penerimaan ketentuan yang diperbarui.</p>
                </div>

                {{-- Referensi dokumen --}}
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Dokumen Terkait</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('public.doc', ['slug' => 'faq']) }}" class="inline-flex items-center gap-1.5 text-sm text-violet-600 hover:text-violet-800 hover:underline">❓ FAQ</a>
                        <a href="{{ route('public.doc', ['slug' => 'refund-policy']) }}" class="inline-flex items-center gap-1.5 text-sm text-violet-600 hover:text-violet-800 hover:underline">💰 Refund Policy</a>
                        <a href="{{ route('public.doc', ['slug' => 'hubungi-kami']) }}" class="inline-flex items-center gap-1.5 text-sm text-violet-600 hover:text-violet-800 hover:underline">📞 Hubungi Kami</a>
                        <a href="https://cafe.manajemen-pos.my.id/important" target="_blank" class="inline-flex items-center gap-1.5 text-sm text-violet-600 hover:text-violet-800 hover:underline">⚠️ Informasi Penting</a>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-center mt-10 fade-up fade-up-d3">
            <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'MENCAF') }}. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>
