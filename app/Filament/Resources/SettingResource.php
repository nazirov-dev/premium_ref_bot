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
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-m-cog';
    protected static ?string $navigationLabel = 'Sozlamalar';
    protected static bool $canCreateAnother = false;

    public static function canCreate(): bool
    {
        return Setting::count() === 0;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('giveaway_status')
                    ->required()
                    ->default(0)
                    ->label('Konkurs holati')
                    ->helperText('Konkurs holatini yoqish yoki o\'chirish')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('referral_status')
                    ->required()->default(false)
                    ->label('Referral tizim holati')
                    ->helperText('Referral tizimini yoqish yoki o\'chirish')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            $set('premium_referral_status', false);
                        }
                    }),
                Forms\Components\TextInput::make('referral_bonus')
                    ->required()->default(0)
                    ->label('Referral bonus')
                    ->helperText('Referral bonus summasi oddiy referral uchun')
                    ->numeric()
                    ->visible(fn(Get $get): bool => $get('referral_status')),
                Forms\Components\Toggle::make('premium_referral_status')
                    ->required()
                    ->default(false)
                    ->label('Premium referral tizim holati')
                    ->helperText('Premium referral tizimini yoqish yoki o\'chirish')
                    ->visible(fn(Get $get): bool => $get('referral_status'))
                    ->live(),
                Forms\Components\TextInput::make('premium_referral_bonus')
                    ->required()
                    ->default(0)
                    ->label('Premium referral bonus')
                    ->helperText('Premium referral bonus summasi premium referral uchun')
                    ->numeric()
                    ->visible(fn(Get $get): bool => $get('premium_referral_status')),
                Forms\Components\Toggle::make('bonus_menu_status')
                    ->required()
                    ->default(false)
                    ->label('Bonus menyu holati')
                    ->helperText('Bonus menyuni yoqish yoki o\'chirish')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            $set('daily_bonus_status', false);
                        }
                    }),
                Forms\Components\Toggle::make('daily_bonus_status')
                    ->required()
                    ->default(false)
                    ->label('Kunlik bonus holati')
                    ->helperText('Bonus menyuni yoqish yoki o\'chirish')
                    ->visible(fn(Get $get): bool => $get('bonus_menu_status')),
                Forms\Components\Select::make('bonus_type')->options([
                    'every_channel' => 'Hamma kanal uchun',
                    'only_first_channel' => 'Faqat bitta kanal'
                ])
                    ->required()
                    ->default('every_channel')
                    ->label('Bonus turi')
                    ->helperText('Boost uchun bonus turi')
                    ->visible(fn(Get $get): bool => $get('bonus_menu_status')),
                Forms\Components\TextInput::make('top_users_count')
                    ->required()
                    ->default(10)
                    ->label('Top foydalanuvchilar')
                    ->helperText('Top foydalanuvchilar bo\'limida ko\'rsatiladigan foydalanuvchilar soni'),
                Forms\Components\Select::make('multi_account_action')
                    ->label('Nakrutka vaqtida nima qilish')
                    ->options([
                        'warn' => "Ogohlantirish",
                        'ban' => 'Ban qilish'
                    ]),
                Forms\Components\TextInput::make('promo_code_expire_days')
                    ->required()
                    ->default(30)
                    ->label('Promo code amal qilish muddati')
                    ->helperText('Promo kod amal qilish muddati kunlar hissobida, agarda limit qo\'yishni xohlamasangiz 0 qo\'ying')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('admin_id')
                    ->required()
                    ->default(1996292437)
                    ->label('Admin id')
                    ->helperText('Admin id raqami'),
                Forms\Components\TextInput::make('proof_channel_id')
                    ->required()
                    ->default(1996292437)
                    ->label('Isbot kanal ID raqami')
                    ->helperText("To'lab berilganlik haqida xabar tushadigan kanal ID raqami"),
            ]);
    }

    public static function table(Table $table): Table
    {
        $bot_settings = Cache::remember('bot_settings', 60 * 60 * 24, function () {
            return Setting::first();
        });
        define('SETTINGS', json_decode($bot_settings));
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('referral_status')
                    ->label('Referral bo\'lim holati')
                    ->wrapHeader()
                    ->badge()
                    ->color(function ($state) {
                        return $state ? 'success' : 'danger';
                    })
                    ->formatStateUsing(function ($state) {
                        return $state ? 'Yoqilgan' : "O'chirilgan";
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('referral_bonus')
                    ->label('Referral bonus summasi')
                    ->wrapHeader()
                    ->searchable()
                    ->visible(SETTINGS->referral_status),
                Tables\Columns\TextColumn::make('premium_referral_status')
                    ->label('Premium referral bo\'lim holati')
                    ->wrapHeader()
                    ->badge()
                    ->color(function ($state) {
                        return $state ? 'success' : 'danger';
                    })
                    ->formatStateUsing(function ($state) {
                        return $state ? 'Yoqilgan' : "O'chirilgan";
                    })
                    ->searchable()
                    ->visible(SETTINGS->referral_status),
                Tables\Columns\TextColumn::make('premium_referral_bonus')
                    ->label('Premium ref bonus summasi')
                    ->searchable()
                    ->wrapHeader()
                    ->visible(SETTINGS->referral_status and SETTINGS->premium_referral_status),
                Tables\Columns\TextColumn::make('bonus_menu_status')
                    ->label('Bonus bo\'limi holati')
                    ->searchable()
                    ->wrapHeader()
                    ->formatStateUsing(function ($state) {
                        return $state ? 'Yoqilgan' : "O'chirilgan";
                    })
                    ->badge()
                    ->color(function ($state) {
                        return $state ? 'success' : 'danger';
                    }),
                Tables\Columns\TextColumn::make('daily_bonus_status')
                    ->label('Kunlik bonus holati')
                    ->searchable()
                    ->wrapHeader()
                    ->formatStateUsing(function ($state) {
                        return $state ? 'Yoqilgan' : "O'chirilgan";
                    })
                    ->badge()
                    ->color(function ($state) {
                        return $state ? 'success' : 'danger';
                    })
                    ->visible(SETTINGS->daily_bonus_status),
                Tables\Columns\TextColumn::make('bonus_type')
                    ->label('Bonus turi')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        return [
                            'every_channel' => 'Hamma kanal uchun',
                            'only_first_channel' => 'Faqat bitta kanal'
                        ][$state];
                    })
                    ->visible(SETTINGS->daily_bonus_status)
                    ->selectablePlaceholder(false)
                    ->disabled(),
                Tables\Columns\TextColumn::make('top_users_count')
                    ->label('Top foydalanuvchilar soni')
                    ->searchable()
                    ->wrapHeader(),
                Tables\Columns\TextColumn::make('multi_account_action')
                    ->label('Nakrutka vaqtida nima qilish')
                    ->wrapHeader()
                    ->formatStateUsing(function ($state) {
                        return [
                            'warn' => "Ogohlantirish",
                            'ban' => 'Ban qilish'
                        ][$state];
                    })
                    ->selectablePlaceholder(false)
                    ->disabled(),
                Tables\Columns\TextColumn::make('promo_code_expire_days')
                    ->label('Promo kod muddati')
                    ->searchable(),
                Tables\Columns\TextColumn::make('admin_id')
                    ->label('Admin ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('proof_channel_id')
                    ->label('Isbot kanal ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrapHeader(),
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
            ->selectable(false);
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
