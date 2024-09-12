<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BotUserResource\Pages;
use App\Filament\Resources\BotUserResource\RelationManagers;
use App\Models\Text;
use App\Models\BotUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BotUserResource extends Resource
{
    protected static ?string $model = BotUser::class;
    protected static ?string $navigationIcon = 'heroicon-m-user-group';
    protected static ?string $navigationLabel = 'Foydalanuvchilar';

    public static function canCreate(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\TextInput::make('id')
                        ->label('Foydalanuvchi tartib raqami')
                        ->numeric(),
                    Forms\Components\TextInput::make('user_id')
                        ->label('Foydalanuvchi ID raqami')
                        ->numeric(),
                    Forms\Components\TextInput::make('name')
                        ->label('Ismi'),

                    Forms\Components\TextInput::make('username')
                        ->label("Username"),

                    Forms\Components\Toggle::make('status')
                    ->label("Aktivmi?"),

                    Forms\Components\DateTimePicker::make('created_at')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                        ->label('Tartib raqami'),
                Tables\Columns\TextColumn::make('user_id')
                    ->label('Foydalanuvchi ID raqami'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Ismi'),

                Tables\Columns\TextColumn::make('username')
                    ->label("Username")
                    ->url(fn($record) => "https://t.me/{$record->username}")
                    ->prefix('@'),
                Tables\Columns\ToggleColumn::make('status')
                ->label("Aktivmi?"),

                Tables\Columns\TextColumn::make('created_at')
                ->label("Ro'yhatdan o'tgan vaqti")
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultSort('id', 'desc')
            ->heading('Foydalanuvchilar');
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
            'index' => Pages\ListBotUsers::route('/'),
            'create' => Pages\CreateBotUser::route('/create'),
            'edit' => Pages\EditBotUser::route('/{record}/edit'),
        ];
    }
}
