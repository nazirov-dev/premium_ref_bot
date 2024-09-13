<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function canCreate(): bool
    {
        return Setting::count() === 0;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('giveaway_status')->required()->default(0)->label('Giveaway status'),
                Forms\Components\TextInput::make('referral_bonus')->required()->default(0)->label('Referral bonus'),
                Forms\Components\TextInput::make('premium_referral_bonus')->required()->default(0)->label('Premium referral bonus'),
                Forms\Components\Toggle::make('bonus_menu_status')->required()->default(false)->label('Bonus menu status'),
                Forms\Components\Toggle::make('referral_status')->required()->default(false)->label('Referral status'),
                Forms\Components\Toggle::make('premium_referral_status')->required()->default(false)->label('Premium referral status'),
                Forms\Components\TextInput::make('top_users_count')->required()->default(10)->label('Top users count'),
                Forms\Components\Select::make('bonus_type')->options([
                    'every_channel' => 'Hamma kanal uchun',
                    'only_first_channel' => 'Faqat bitta kanal'
                ])->required()->default('every_channel')->label('Bonus turi'),
                Forms\Components\TextInput::make('promo_code_expire_days')->required()->default(30)->label('Promo code expire days'),
                Forms\Components\TextInput::make('admin_id')->required()->default(1996292437)->label('Admin id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ToggleColumn::make('giveaway_status')->label('Giveaway status')->searchable(),
                Tables\Columns\TextColumn::make('referral_bonus')->label('Referral bonus')->searchable(),
                Tables\Columns\TextColumn::make('premium_referral_bonus')->label('Premium referral bonus')->searchable(),
                Tables\Columns\ToggleColumn::make('bonus_menu_status')->label('Bonus menu status')->searchable(),
                Tables\Columns\ToggleColumn::make('referral_status')->label('Referral status')->searchable(),
                Tables\Columns\ToggleColumn::make('premium_referral_status')->label('Premium referral status')->searchable(),
                Tables\Columns\TextColumn::make('top_users_count')->label('Top users count')->searchable(),
                Tables\Columns\SelectColumn::make('bonus_type')->label('Bonus type')->searchable(),
                Tables\Columns\TextColumn::make('promo_code_expire_days')->label('Promo code expire days')->searchable(),
                Tables\Columns\TextColumn::make('admin_id')->label('Admin id')->searchable(),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
