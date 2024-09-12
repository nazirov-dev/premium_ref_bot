<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChannelResource\Pages;
use App\Filament\Resources\ChannelResource\RelationManagers;
use App\Models\Channel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?string $navigationIcon = 'heroicon-o-at-symbol';
    protected static ?string $navigationLabel = 'Kanallar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //'channel_id', 'username', 'name', 'url', 'status'
                Forms\Components\TextInput::make('channel_id')->placeholder('-100')->required()->label('Kanal id raqami'),
                Forms\Components\TextInput::make('username')->required()->placeholder('Kanal useri oddiy xolatda')->label('Kanal foydalanuvchi nomi'),
                Forms\Components\TextInput::make('name')->placeholder('Kanal nomi')->required()->label('Kanal nomi'),
                Forms\Components\TextInput::make('invite_link')->placeholder('Kanalga kirish uchun link.')->required()->label('Kanal linki'),
                Forms\Components\Toggle::make('status')->required()->default(0)->label('Xolati')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('channel_id')->label('Kanal idsi'),
                Tables\Columns\TextColumn::make('username')->label('Kanal useri'),
                Tables\Columns\TextColumn::make('name')->label('Kanal nomi'),
                Tables\Columns\TextColumn::make('invite_link')->label('Kanal linki')->url(fn($record) => $record->invite_link),
                Tables\Columns\ToggleColumn::make('status')->label('Xolati')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make(),
                ])
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('id', 'desc');

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
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
        ];
    }
}
