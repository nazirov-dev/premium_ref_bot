<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ButtonResource\Pages;
use App\Filament\Resources\ButtonResource\RelationManagers;
use App\Models\Button;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ButtonResource extends Resource
{
    protected static ?string $model = Button::class;

    protected static ?string $navigationIcon = 'heroicon-c-cursor-arrow-ripple';
    protected static ?string $navigationLabel = 'Tugmalar';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nomi'),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug'),
                Forms\Components\Select::make('messages')
                    ->multiple()
                    ->options(
                        Message::pluck('name', 'id')->toArray()
                    )
                    ->searchable()
                    ->label('Xabarlar'),
                Forms\Components\Toggle::make('status')
                    ->label('Holati'),
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
                Tables\Columns\TextColumn::make('messages')
                    ->label('Xabarlar')
                    ->listWithLineBreaks(),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Holati'),
            ])
            ->defaultSort('id', 'desc')
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
            ->heading('Tugmalar')
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
            'index' => Pages\ListButtons::route('/'),
            'create' => Pages\CreateButton::route('/create'),
            'edit' => Pages\EditButton::route('/{record}/edit'),
        ];
    }
}
