<?php

namespace App\Filament\Resources\Cafes\Schemas;

use App\Models\Subscription;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CafeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Identitas Cafe')
                    ->description('Data dasar cafe yang digunakan untuk dashboard, transaksi, dan laporan.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Cafe')
                            ->required()
                            ->placeholder('Contoh: Caffe Maju')
                            ->maxLength(255),
                        TextInput::make('owner_name')
                            ->label('Nama Pemilik')
                            ->placeholder('Nama pemilik cafe')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->placeholder('owner@domain.com')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->placeholder('08xxxxxxxxxx')
                            ->tel()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Cafe nonaktif akan disembunyikan dari pemilihan data utama.'),
                    ]),
                Section::make('Lokasi & Brand')
                    ->description('Tambahkan alamat dan aset visual agar tampilan lebih profesional.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('city')
                            ->label('Kota')
                            ->placeholder('Bandung')
                            ->maxLength(255),
                        TextInput::make('province')
                            ->label('Provinsi')
                            ->placeholder('Jawa Barat')
                            ->maxLength(255),
                        FileUpload::make('logo_url')
                            ->label('Logo')
                            ->image()
                            ->directory('cafes')
                            ->columnSpanFull(),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Langganan')
                    ->description('Pilih paket langganan yang sesuai dengan kebutuhan cafe.')
                    ->schema([
                        Select::make('subscription_id')
                            ->label('Paket Langganan')
                            ->placeholder('Pilih paket langganan...')
                            ->options(
                                Subscription::where('is_active', true)
                                    ->orderBy('price')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->nullable(),
                    ]),

            ]);
    }
}
