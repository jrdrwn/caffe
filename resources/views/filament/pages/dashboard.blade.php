<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Ringkasan {{ $this->roleLabel }}
            </x-slot>

            <x-slot name="description">
                Data langsung dari sistem sesuai hak akses akun yang sedang login.
            </x-slot>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->statsCards as $card)
                    <x-filament::section>
                        <x-slot name="heading">
                            {{ $card['label'] }}
                        </x-slot>

                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">
                            {{ $card['value'] }}
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Transaksi Terbaru
            </x-slot>

            <x-slot name="description">
                Lima transaksi terakhir yang bisa diakses oleh role ini.
            </x-slot>

            @if (count($this->recentTransactions) > 0)
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">No. Transaksi</th>
                                <th class="px-4 py-3 text-left font-medium">Kasir</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-right font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($this->recentTransactions as $row)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">
                                        {{ $row['transaction_number'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                        {{ $row['cashier'] }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-filament::badge
                                            :color="$row['status'] === 'Completed' ? 'success' : ($row['status'] === 'Pending' ? 'warning' : 'danger')"
                                        >
                                            {{ $row['status'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-950 dark:text-white">
                                        {{ $row['total'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    Belum ada transaksi yang bisa ditampilkan untuk role ini.
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
