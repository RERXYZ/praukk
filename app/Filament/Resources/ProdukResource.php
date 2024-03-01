<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Widgets\ProdukStatsOverview;
use Filament\Forms;
use Filament\Tables;
use App\Models\Produks;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProdukResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProdukResource\RelationManagers;
use Filament\View\LegacyComponents\Widget;
use Filament\Widgets\Widget as WidgetsWidget;

class ProdukResource extends Resource
{
    protected static ?string $model = Produks::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Belanja';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $modelLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('NamaProduk')->required()->unique(ignoreRecord: true),
                        TextInput::make('Harga')->numeric()->required(),
                        TextInput::make('Stok')->numeric()->required(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('NamaProduk')->searchable(),
                TextColumn::make('Harga')
                ->numeric(
                    decimalPlaces: 0,
                    thousandsSeparator: '.',
                )
                ->sortable()
                ->searchable(),
                TextColumn::make('Stok')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProduks::route('/'),
        ];
    }
}
