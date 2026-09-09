<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MENCAF') }} — Dokumen Publik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
        .float-anim { animation: float 4s ease-in-out infinite; }
        .float-anim-delay { animation: float 4s ease-in-out 1s infinite; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp 0.6s ease-out forwards; }
        .fade-up-1 { animation-delay: 0.1s; opacity: 0; }
        .fade-up-2 { animation-delay: 0.2s; opacity: 0; }
        .fade-up-3 { animation-delay: 0.3s; opacity: 0; }
        .fade-up-4 { animation-delay: 0.4s; opacity: 0; }
        .fade-up-5 { animation-delay: 0.5s; opacity: 0; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-indigo-50 min-h-screen">
    {{-- Decorative background --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute top-1/2 -left-40 w-80 h-80 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full opacity-40 blur-3xl"></div>
        <div class="absolute -bottom-40 right-1/4 w-96 h-96 bg-gradient-to-br from-violet-100 to-pink-100 rounded-full opacity-40 blur-3xl"></div>
    </div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 py-12 sm:py-20">
        {{-- Header --}}
        <div class="text-center mb-16 fade-up">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl shadow-indigo-500/25 mb-6 float-anim">
                @if(file_exists(public_path('default-logo/logo.png')))
                    <img src="{{ asset('default-logo/logo.png') }}" alt="Logo" class="h-12 w-12 object-contain">
                @else
                    <span class="text-3xl">☕</span>
                @endif
            </div>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-3">
                {{ config('app.name', 'MENCAF') }}
            </h1>
            <p class="text-lg text-gray-500 max-w-md mx-auto">Dokumen & Informasi Penting</p>
            <div class="w-20 h-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full mx-auto mt-6"></div>
        </div>

        {{-- Document Grid --}}
        <div class="grid gap-6 sm:grid-cols-2">
            {{-- FAQ --}}
            <a href="{{ route('public.doc', ['slug' => 'faq']) }}"
               class="glass rounded-2xl border border-white/60 shadow-lg hover:shadow-xl hover:shadow-indigo-500/10 p-7 transition-all duration-300 group hover:-translate-y-1 fade-up fade-up-1">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-2xl shadow-lg shadow-blue-500/25 group-hover:scale-110 transition-transform duration-300 float-anim">
                        ❓
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">FAQ</h2>
                        <p class="text-sm text-gray-500 mt-1 leading-relaxed">Pertanyaan yang sering diajukan tentang MENCAF</p>
                        <span class="inline-flex items-center mt-4 text-sm font-semibold text-indigo-600">
                            Baca
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            {{-- Hubungi Kami --}}
            <a href="{{ route('public.doc', ['slug' => 'hubungi-kami']) }}"
               class="glass rounded-2xl border border-white/60 shadow-lg hover:shadow-xl hover:shadow-emerald-500/10 p-7 transition-all duration-300 group hover:-translate-y-1 fade-up fade-up-2">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/25 group-hover:scale-110 transition-transform duration-300 float-anim-delay">
                        📞
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">Hubungi Kami</h2>
                        <p class="text-sm text-gray-500 mt-1 leading-relaxed">Informasi kontak & customer support</p>
                        <span class="inline-flex items-center mt-4 text-sm font-semibold text-emerald-600">
                            Baca
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            {{-- Refund Policy --}}
            <a href="{{ route('public.doc', ['slug' => 'refund-policy']) }}"
               class="glass rounded-2xl border border-white/60 shadow-lg hover:shadow-xl hover:shadow-orange-500/10 p-7 transition-all duration-300 group hover:-translate-y-1 fade-up fade-up-3">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center text-2xl shadow-lg shadow-orange-500/25 group-hover:scale-110 transition-transform duration-300 float-anim">
                        💰
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 group-hover:text-orange-600 transition-colors">Refund Policy</h2>
                        <p class="text-sm text-gray-500 mt-1 leading-relaxed">Kebijakan pengembalian dana</p>
                        <span class="inline-flex items-center mt-4 text-sm font-semibold text-orange-600">
                            Baca
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            {{-- Syarat & Ketentuan --}}
            <a href="{{ route('public.doc', ['slug' => 'syarat-ketentuan']) }}"
               class="glass rounded-2xl border border-white/60 shadow-lg hover:shadow-xl hover:shadow-violet-500/10 p-7 transition-all duration-300 group hover:-translate-y-1 fade-up fade-up-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-2xl shadow-lg shadow-violet-500/25 group-hover:scale-110 transition-transform duration-300 float-anim-delay">
                        📋
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 group-hover:text-violet-600 transition-colors">Syarat & Ketentuan</h2>
                        <p class="text-sm text-gray-500 mt-1 leading-relaxed">Syarat penggunaan layanan MENCAF</p>
                        <span class="inline-flex items-center mt-4 text-sm font-semibold text-violet-600">
                            Baca
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-16 fade-up fade-up-5">
            <div class="w-20 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent mx-auto mb-6"></div>
            <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'MENCAF') }}. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>
