<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nomi')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'text' => 'Matn',
                        'photo' => 'Rasm',
                        'video' => 'Video'
                    ])
                    ->label('Turi'),
                Forms\Components\Textarea::make('text')
                    ->label('Matn'),
                Forms\Components\TextInput::make('buttons')
                    ->label('Tugmalar'),
                Forms\Components\TextInput::make('file_id')
                    ->label('Fayl ID raqami')->nullable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //table
                Tables\Columns\TextColumn::make('name')
                    ->label('Nomi')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('type')
                    ->label('Turi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('text')
                    ->label('Matn')
                    ->searchable(),
                Tables\Columns\TextColumn::make('buttons')
                    ->label('Tugmalar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_id')
                    ->label('Fayl ID raqami')
                    ->searchable()
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
            ->heading('Xabarlar');
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
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
            'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
}
