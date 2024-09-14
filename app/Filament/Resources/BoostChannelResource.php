<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoostChannelResource\Pages;
use App\Filament\Resources\BoostChannelResource\RelationManagers;
use App\Models\BoostChannel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BoostChannelResource extends Resource
{
    protected static ?string $model = BoostChannel::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationLabel = 'Boost qilish uchun kanallar';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'channel_id'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nomi'),
                Forms\Components\TextInput::make('channel_id')
                    ->label('Kanal ID'),
                Forms\Components\TextInput::make('bonus_each_boost')
                    ->label('Har bir boost uchun bonus')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('daily_bonus_each_boost')
                    ->label('Har kundagi har bir boost uchun bonus')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('daily_bonus')
                    ->label('Kundagi bonus')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Select::make('daily_bonus_type')
                    ->options([
                        'simple' => 'Oddiy',
                        'bonus_each_boost' => 'Har bir boost uchun bonus',
                    ])
                    ->label('Kunlik bonus turi'),
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
                Tables\Columns\TextColumn::make('channel_id')
                    ->label('Kanal ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bonus_each_boost')
                    ->label('Har bir boost uchun bonus')
                    ->searchable(),
                Tables\Columns\TextColumn::make('daily_bonus_each_boost')
                    ->label('Har kundagi har bir boost uchun bonus')
                    ->searchable(),
                Tables\Columns\TextColumn::make('daily_bonus')
                    ->label('Kundagi bonus')
                    ->searchable(),
                Tables\Columns\TextColumn::make('daily_bonus_type')
                    ->label('Kunlik bonus turi')
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
            ->heading('Boost kanallar')
            ->searchOnBlur();
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
            'index' => Pages\ListBoostChannels::route('/'),
            'create' => Pages\CreateBoostChannel::route('/create'),
            'edit' => Pages\EditBoostChannel::route('/{record}/edit'),
        ];
    }
}
