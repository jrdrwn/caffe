<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — {{ config('app.name', 'MENCAF') }}</title>
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
        .fade-up-d1{animation-delay:.1s;opacity:0}.fade-up-d2{animation-delay:.2s;opacity:0}.fade-up-d3{animation-delay:.3s;opacity:0}.fade-up-d4{animation-delay:.4s;opacity:0}
        .contact-card{transition:all .3s ease}
        .contact-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px -12px rgba(0,0,0,.15)}
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-emerald-50 min-h-screen">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-br from-teal-100 to-cyan-100 rounded-full opacity-50 blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('public.landing') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-emerald-600 transition-all duration-200 mb-8 group fade-up">
            <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Dokumen
        </a>

        {{-- Header --}}
        <div class="glass rounded-2xl border border-white/60 shadow-xl shadow-emerald-500/5 p-8 sm:p-10 mb-8 fade-up fade-up-d1">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/25 float-anim">📞</div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">{{ $title }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 mt-1">Dokumen Publik</span>
                </div>
            </div>
        </div>

        {{-- Company Info Cards --}}
        <div class="grid gap-5 sm:grid-cols-2 mb-8 fade-up fade-up-d2">
            {{-- Email --}}
            <div class="contact-card glass rounded-2xl border border-white/60 shadow-lg p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Email</h3>
                </div>
                <a href="mailto:Satutriliun450@gmail.com" class="text-emerald-600 font-medium hover:underline">Satutriliun450@gmail.com</a>
            </div>

            {{-- WhatsApp --}}
            <div class="contact-card glass rounded-2xl border border-white/60 shadow-lg p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900">WhatsApp / Telepon</h3>
                </div>
                <a href="https://wa.me/6285822536359" class="text-emerald-600 font-medium hover:underline" target="_blank">0858-2253-6359</a>
            </div>

            {{-- Alamat --}}
            <div class="contact-card glass rounded-2xl border border-white/60 shadow-lg p-6 sm:col-span-2">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900">Alamat Usaha</h3>
                </div>
                <p class="text-gray-600 leading-relaxed">JL. Meranti III kel. Panarung kec. Pahandut Kota. Palangkaraya Kalimantan Tengah 73111</p>
            </div>
        </div>

        {{-- Customer Support Section --}}
        <div class="glass rounded-2xl border border-white/60 shadow-xl shadow-emerald-500/5 p-6 sm:p-10 fade-up fade-up-d3">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Customer Support</h2>
            </div>

            <p class="text-gray-600 mb-6 leading-relaxed">
                Jika Anda mengalami kendala dalam menggunakan MENCAF, silakan hubungi customer support kami dengan mencantumkan:
            </p>

            <div class="grid gap-3 sm:grid-cols-2 mb-6">
                <div class="flex items-center gap-3 p-3 bg-white/50 rounded-xl">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-gray-700 font-medium">Nama akun</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white/50 rounded-xl">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-gray-700 font-medium">Email akun</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white/50 rounded-xl">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-gray-700 font-medium">Nama usaha / kafe</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white/50 rounded-xl">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-gray-700 font-medium">Kendala yang dialami</span>
                </div>
                <div class="flex items-center gap-3 p-3 bg-white/50 rounded-xl sm:col-span-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-gray-700 font-medium">Screenshot jika diperlukan</span>
                </div>
            </div>

            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-5 border border-emerald-100">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-bold text-emerald-800">Jam Layanan</span>
                </div>
                <p class="text-emerald-700 ml-7">Senin – Sabtu, 09.00 – 16.00 WIB</p>
            </div>
        </div>

        <div class="text-center mt-10 fade-up fade-up-d4">
            <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'MENCAF') }}. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>
