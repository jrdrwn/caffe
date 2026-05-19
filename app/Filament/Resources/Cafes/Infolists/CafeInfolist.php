<?php

namespace App\Filament\Resources\Cafes\Infolists;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CafeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = Auth::user()?->role === 'super_admin';

        return $schema
            ->components([
                Section::make('Informasi Cafe')
                    ->description('Ringkasan identitas cafe yang aktif di sistem.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Cafe'),
                        TextEntry::make('owner_name')
                            ->label('Pemilik')
                            ->placeholder('Belum diisi'),
                        TextEntry::make('phone')
                            ->label('Telepon')
                            ->placeholder('Belum diisi'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('Belum diisi'),
                        TextEntry::make('city')
                            ->label('Kota')
                            ->placeholder('Belum diisi'),
                        TextEntry::make('province')
                            ->label('Provinsi')
                            ->placeholder('Belum diisi'),
                    ]),

                Section::make('Pengaturan Transaksi')
                    ->description('Pajak dan biaya layanan yang diterapkan pada setiap transaksi.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('tax_percentage')
                            ->label('Pajak')
                            ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state}%" : 'Tidak ada pajak')
                            ->badge()
                            ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray'),
                        TextEntry::make('service_charge_percentage')
                            ->label('Service Charge')
                            ->formatStateUsing(fn (int $state): string => $state > 0 ? "{$state}%" : 'Tidak ada service charge')
                            ->badge()
                            ->color(fn (int $state): string => $state > 0 ? 'info' : 'gray'),
                    ]),

                Section::make('Manager')
                    ->description('Informasi manager yang ditugaskan pada cafe ini.')
                    ->columns(2)
                    ->visible($isSuperAdmin)
                    ->schema([
                        TextEntry::make('manager.name')
                            ->label('Manager')
                            ->placeholder('Belum ditetapkan'),
                    ]),

                Section::make('Langganan')
                    ->description('Paket aktif cafe dan masa berlakunya.')
                    ->columns(2)
                    // ->visible($isSuperAdmin)
                    ->schema([
                        TextEntry::make('subscription.name')
                            ->label('Paket')
                            ->badge()
                            ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                                'free' => 'gray',
                                'medium', 'premium' => 'primary',
                                default => 'gray',
                            })
                            ->placeholder('Belum diatur'),
                        TextEntry::make('subscription.duration_months')
                            ->label('Durasi (bulan)')
                            ->placeholder('Belum diatur'),
                        TextEntry::make('subscription.price')
                            ->label('Harga')
                            ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.'))
                            ->placeholder('Belum diatur'),
                    ]),

                Section::make('Payment Gateway (QRIS)')
                    ->description('Detail konfigurasi pembayaran otomatis.')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('qris_type')
                            ->label('Tipe QRIS')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'manual' => 'Manual (Scan Statis)',
                                'midtrans' => 'Otomatis (Midtrans)',
                                default => $state,
                            })
                            ->badge()
                            ->color(fn (string $state): string => $state === 'midtrans' ? 'success' : 'gray'),

                        TextEntry::make('midtrans_merchant_id')
                            ->label('Merchant ID')
                            ->placeholder('Tidak diset')
                            ->visible(fn ($record) => $record->qris_type === 'midtrans'),

                        TextEntry::make('midtrans_client_key')
                            ->label('Client Key')
                            ->placeholder('Tidak diset')
                            ->visible(fn ($record) => $record->qris_type === 'midtrans'),

                        TextEntry::make('midtrans_is_production')
                            ->label('Mode Sistem')
                            ->formatStateUsing(fn (): string => config('midtrans.is_production') ? 'Produksi (Live)' : 'Sandbox (Testing)')
                            ->helperText('Otomatis mengikuti environment sistem.')
                            ->badge()
                            ->color(fn (): string => config('midtrans.is_production') ? 'danger' : 'info')
                            ->visible(fn ($record) => $record->qris_type === 'midtrans'),
                    ]),
            ]);
    }
}
