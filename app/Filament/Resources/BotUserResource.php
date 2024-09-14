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
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'user_id',
            'name',
            'username',
            'phone_number',
            'referrer_id'
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->label('Foydalanuvchi ID raqami')
                    ->disabled(),
                Forms\Components\TextInput::make('name')
                    ->label('Ismi'),
                Forms\Components\TextInput::make('username')
                    ->label('Username'),
                Forms\Components\TextInput::make('phone_number')
                    ->label('Telefon raqami'),
                Forms\Components\Toggle::make('status')
                    ->label('Aktivmi?'),
                Forms\Components\TextInput::make('balance')
                    ->numeric()
                    ->label('Balansi'),
                Forms\Components\TextInput::make('referrer_id')
                    ->numeric()
                    ->label('Taklif qilgan ID raqami'),
                Forms\Components\Checkbox::make('is_premium')
                    ->label('Premiummi?'),
                Forms\Components\Checkbox::make('daily_bonus_status')
                    ->label('Kunlik bonus olganmi?'),
                Forms\Components\Toggle::make('status')
                    ->label('Aktivmi?'),
                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Ro\'yhatdan o\'tgan vaqti')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('updated_at')
                    ->label('O\'zgartirilgan vaqti')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->searchable()
                    ->label('Foydalanuvchi ID raqami')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('Ismi'),
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')->url(fn($record) => "https://t.me/{$record->username}")
                    ->prefix('@')
                    ->badge()
                    ->searchable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telefon raqami')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balansi')
                    ->suffix(' so\'m')
                    ->badge()
                    ->color(function ($record) {
                        return $record->balance > 0 ? 'success' : 'danger';
                    }),
                Tables\Columns\TextColumn::make('referrer_id')
                    ->label('Taklif qilgan ID raqami')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\CheckboxColumn::make('is_premium')
                    ->label('Premiummi?')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\CheckboxColumn::make('daily_bonus_status')
                    ->label('Kunlik bonus olganmi?')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Aktivmi?'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ro\'yhatdan o\'tgan vaqti'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('O\'zgartirilgan vaqti')
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->searchOnBlur()
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
