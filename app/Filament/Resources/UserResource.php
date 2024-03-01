<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextInputColumn;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Tables\Actions\ActionGroup;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-circle';
    protected static ?string $navigationGroup = 'Registrasi';
    protected static ?string $navigationLabel = 'Akun';
    protected static ?string $modelLabel = 'User';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                        TextInput::make('password')
                        ->revealable()
                        ->password()
                        ->required()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->visible(fn ($livewire)=> $livewire instanceof CreateUser),
                        Select::make('role')
                            ->options([
                                'admin'=>'admin',
                                'staff'=>'staff',
                            ]),
                    ])
                    ->columns(1),
                
                Section::make('New Password')
                    ->schema([
                        TextInput::make('password')
                        ->nullable()
                        ->password(),
                        TextInput::make('new_password_confirmation')
                        ->password()
                        ->same('password')
                        ->requiredWith('password'),
                    ])
                    ->columns(1)
                    ->visible(fn ($livewire)=> $livewire instanceof EditUser),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextInputColumn::make('email')->searchable()->disabled(),
                // TextColumn::make('role')->searchable(),
                SelectColumn::make('role')
                    ->options([
                        'admin'=>'Admin',
                        'staff'=>'Petugas',
                    ]),
                TextColumn::make('created_at')->label('Tanggal Dibuat')->dateTime('d M Y'),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Status')
                    ->options([
                        'admin'=>'Admin',
                        'staff'=>'Petugas',
                    ]),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()->role=='admin';   
    }
}
