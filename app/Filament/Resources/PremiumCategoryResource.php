<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PremiumCategoryResource\Pages;
use App\Filament\Resources\PremiumCategoryResource\RelationManagers;
use App\Models\PremiumCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class PremiumCategoryResource extends Resource
{
    protected static ?string $model = PremiumCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nomi')
                    ->live(true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug'),
                Forms\Components\TextInput::make('price')
                    ->label('Narxi')
                    ->numeric()
                    ->suffix('so\'m'),
                Forms\Components\Toggle::make('status')
                    ->label('Aktivmi?'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nomi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Narxi')
                    ->suffix('So\'m')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Aktivmi?')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc')
            ->heading('Premium kategoriyalar');
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
            'index' => Pages\ListPremiumCategories::route('/'),
            'create' => Pages\CreatePremiumCategory::route('/create'),
            'edit' => Pages\EditPremiumCategory::route('/{record}/edit'),
        ];
    }
}
