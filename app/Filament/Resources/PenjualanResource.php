<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\produks;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Penjualans;
use Filament\Tables\Table;
use League\Uri\Idna\Option;
use App\Models\detailpenjualans;
use Doctrine\DBAL\Schema\Column;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use function Laravel\Prompts\table;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;

use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Columns\Summarizers\Average;
use App\Filament\Resources\PenjualanResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PenjualanResource\RelationManagers;
use App\Filament\Resources\PenjualanResource\Pages\EditPenjualan;
use App\Filament\Resources\PenjualanResource\Pages\CreatePenjualan;
use Filament\Forms\Components\Fieldset;

class PenjualanResource extends Resource
{
    protected static ?string $model = Penjualans::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Belanja';
    protected static ?string $navigationLabel = 'Pesan';
    protected static ?string $modelLabel = 'Pesan';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string{
        return static::getModel()::count();
    }

    
    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Wizard::make([
                Wizard\Step::make('Detail Pembelian')
                    ->schema([
                        Select::make('PelangganID')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->relationship('pelanggan', 'namapelanggan'),
                    ]),
                Wizard\Step::make('Order Produk')
                    ->schema([
                        Repeater::make('produk')
                            ->required()
                            ->label('Pilih Produk')
                            ->schema([
                                Select::make('ProdukID')
                                    ->preload()
                                    ->required()
                                    ->searchable()
                                    ->relationship('produk', 'NamaProduk')
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Set $set) => $set('Subtotal', produks::find($state)?->Harga ?? 0) && $set('produk.Stok', produks::find($state)?->Stok ?? 0)),
                                TextInput::make('JumlahProduk')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(fn (Get $get) => produks::find($get('ProdukID'))?->Stok ?? 0)
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get) => $get('ProdukID') ? $set('Subtotal', produks::find($get('ProdukID'))->Harga * $state) && $set('produk.Stok', max(0, produks::find($get('ProdukID'))->Stok - $state)) : $set('Subtotal', 0) &&  $set('produk.Stok', 0)),
                                TextInput::make('Subtotal')
                                    ->disabled()
                                    ->default(0)
                                    ->numeric()
                                    ->dehydrated()
                                    ->prefix('Rp.')
                                    ->required(),
                                Fieldset::make()
                                    ->relationship('produk')
                                    ->schema([
                                        TextInput::make('Stok')
                                        ->disabled()
                                        ->numeric()
                                        ->live()
                                        ->dehydrated()
                                        ->default(0)
                                        ->required(),
                                    ]),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->live()
                            ->relationship('detailpenjualan'),
                            TextInput::make('totalharga')
                                ->required()
                                ->default(0)
                                ->prefix('Rp.')
                                ->numeric()
                                ->inputMode('decimal')
                                ->mask(fn (Get $get)=>collect($get('produk'))->pluck('Subtotal')->sum()),
                    ]),
            ])
            ->columnSpanFull()
            ->visible(fn ($livewire)=> $livewire instanceof CreatePenjualan),

                Section::make('View Tampilan')
                    ->schema([
                        Select::make('PelangganID')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->relationship('pelanggan', 'namapelanggan'),
                        Repeater::make('produk')
                                    ->required()
                                    ->label('Pilih Produk')
                                    ->schema([
                                        Select::make('ProdukID')
                                            ->preload()
                                            ->required()
                                            ->searchable()
                                            ->relationship('produk', 'NamaProduk')
                                            ->live()
                                            ->afterStateUpdated(fn ($state, Set $set) => $set('Subtotal', produks::find($state)?->Harga ?? 0)),
                                        TextInput::make('JumlahProduk')
                                        ->numeric()
                                        ->default(1)
                                        ->reactive()
                                        ->live()
                                        ->afterStateUpdated(fn ($state, Set $set, Get $get) => $get('ProdukID') ? $set('Subtotal', produks::find($get('ProdukID'))->Harga * $state) : $set('Subtotal', 0)),
                                        TextInput::make('Subtotal')
                                        ->disabled()
                                        ->default(0)
                                        ->numeric()
                                        ->dehydrated()
                                        ->prefix('Rp.')
                                        ->required(),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(1)
                                    ->live()
                                    ->relationship('detailpenjualan'),
                                    TextInput::make('totalharga')
                                    ->required()
                                    ->default(0)
                                    ->prefix('Rp.')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->mask(fn (Get $get)=>collect($get('produk'))->pluck('Subtotal')->sum()),
                    ])
                    ->hidden(fn ($livewire)=> $livewire instanceof CreatePenjualan)
                    ->disabled(fn ($livewire)=> $livewire instanceof EditPenjualan)
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pelanggan.namapelanggan')->label('Nama Pelanggan')->searchable(),
                TextColumn::make('pelanggan.alamat')->label('Alamat Pelanggan')->searchable(),
                TextColumn::make('detailpenjualan.produk.NamaProduk')->label('Produk Dibeli'),
                TextColumn::make('totalharga')
                ->label('Total Harga')
                ->numeric(
                    decimalPlaces: 0,
                    decimalSeparator: ',',
                    thousandsSeparator: '.',
                )
                ->summarize(Sum::make()
                            ->label('Total')
                            ->numeric(
                            decimalPlaces: 0,
                            thousandsSeparator: '.',
                            )
                            ->money('Rp.'))
                ->prefix('Rp. '),
                TextColumn::make('detailpenjualan.produk.NamaProduk')->label('Produk Dibeli')->searchable(),
                TextColumn::make('tanggalpenjualan')->label('Tanggal Penjualan')->dateTime('d M Y')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'edit' => Pages\EditPenjualan::route('/{record}/edit'),
        ];
    }
}
