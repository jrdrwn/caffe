@php
    $links = [
        [
            'name' => 'Admin',
            'url' => '/admin/login',
            'icon' => 'heroicon-o-shield-check',
            'description' => 'Kelola semua cafe',
        ],
        [
            'name' => 'Manajer',
            'url' => '/manajer/login',
            'icon' => 'heroicon-o-building-storefront',
            'description' => 'Kelola cafe Anda',
        ],
    ];

    if (app()->environment('production')) {
        $links = array_filter($links, fn($link) => $link['name'] !== 'Admin');
    }
@endphp

<div class="mt-8 border-t border-gray-200 dark:border-white/10 pt-6">
    <div class="text-center mb-4">
        <p class="fi-section-header-description text-sm font-medium text-gray-500 dark:text-gray-400">
            Login sebagai role lain?
        </p>
    </div>

    <div class="grid {{ count($links) === 1 ? 'grid-cols-1' : 'grid-cols-1 sm:grid-cols-2' }} gap-3">
        @foreach ($links as $link)
            <a
                href="{{ $link['url'] }}"
                class="fi-btn fi-btn-color-gray flex items-center gap-3 p-4 rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:bg-gray-50 hover:ring-1 hover:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:ring-primary-500 no-underline group"
            >
                <div class="shrink-0 text-gray-400 group-hover:text-primary-500 transition-colors">
                    <x-filament::icon
                        :icon="$link['icon']"
                        class="w-6 h-6"
                    />
                </div>
                <div class="flex-1 text-left">
                    <div class="font-semibold text-sm text-gray-950 dark:text-white group-hover:text-primary-500 transition-colors mb-0.5">
                        {{ $link['name'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $link['description'] }}
                    </div>
                </div>
                <div class="shrink-0 text-gray-400 group-hover:text-primary-500 transition-colors opacity-50 group-hover:opacity-100 transform group-hover:translate-x-1">
                    <x-filament::icon
                        icon="heroicon-m-arrow-right"
                        class="w-4 h-4"
                    />
                </div>
            </a>
        @endforeach
    </div>
</div>
