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
        .fade-up-d1{animation-delay:.1s;opacity:0}.fade-up-d2{animation-delay:.2s;opacity:0}.fade-up-d3{animation-delay:.3s;opacity:0}
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-indigo-50 min-h-screen">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full opacity-50 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full opacity-50 blur-3xl"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('public.landing') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-all duration-200 mb-8 group fade-up">
            <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Dokumen
        </a>

        <div class="glass rounded-2xl border border-white/60 shadow-xl shadow-indigo-500/5 p-8 sm:p-10 mb-8 fade-up fade-up-d1">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $color['from'] }} {{ $color['to'] }} flex items-center justify-center text-2xl shadow-lg float-anim">{{ $icon }}</div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">{{ $title }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color['badge'] }}-100 text-{{ $color['badge'] }}-700 mt-1">Dokumen Publik</span>
                </div>
            </div>
        </div>

        <div class="glass rounded-2xl border border-white/60 shadow-xl shadow-indigo-500/5 p-8 sm:p-10 fade-up fade-up-d2">
            <div class="prose prose-lg max-w-none">{!! $content !!}</div>
        </div>

        <div class="text-center mt-10 fade-up fade-up-d3">
            <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'MENCAF') }}. Semua hak dilindungi.</p>
        </div>
    </div>
</body>
</html>