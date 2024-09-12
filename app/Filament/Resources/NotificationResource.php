<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use Filament\Notifications\Notification as FilamentNotification;
use App\Models\Notification;
use App\Models\NotificationStatus;
use App\Models\Lang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Services\TelegramService;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Ommaviy xabarlar';

    public static function form(Form $form): Form
    {
        $langs = ['*' => "Barcha foydalanuvchilarga"] + Lang::pluck('name', 'short_code')->toArray();
        return $form
            ->schema([
                Forms\Components\TextInput::make('admin_chat_id')
                    ->numeric()
                    ->label("Admin telegram ID raqami")
                    ->helperText(new HtmlString("Telegram orqali xabar yuborilish xolatini bildirib turish uchun, avtomatik bazadagi asosiy admin ID raqami yoziladi agar boshqa admin bo'lmasa o'zgartirish shart emas!<br><b>Botga start bosgan bo'lishi shart!</b>"))
                    ->default(config('env.ADMIN_ID'))
                    ->required(),
                Forms\Components\TextInput::make('message_id')
                    ->numeric()
                    ->label("Xabarlar joylangan kanaldagi xabar ID raqami")
                    ->helperText(new HtmlString("Xabar IDsini olish uchun kanaldagi xabarni havolasini nusxalash kerak<br>Nusxalangan havoladagi ohirgi\"/\" belgisidan keyingi raqam xabar ID raqami bo'ladi<br>Namuna: <br><b>https://t.me/NAMANGANLIKLAR_UZ/101617</b> bu xolatda <b>101617</b> xabar ID raqami<br><b>https://t.me/c/1547412981/2</b> bu xolatda <b>2</b> xabar ID raqami"))
                    ->required()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('check_message_exists')
                            ->icon('heroicon-s-check-circle')
                            ->action(function ($state) {
                                if(empty($state)) {
                                    FilamentNotification::make()
                                    ->title('Xabar ID raqami kiritilmagan!')
                                    ->danger()
                                    ->duration(5000)
                                    ->color('danger')
                                    ->send();
                                } else {
                                    $bot = new TelegramService();
                                    $send = $bot->forwardMessage([
                                        'chat_id' => config('env.DEV_ID'),
                                        'message_id' => $state,
                                        'from_chat_id' => config('env.ADS_TELEGRAM_CHANNEL_ID')
                                    ]);
                                    if(isset($send['ok']) && $send['ok']) {
                                        FilamentNotification::make()
                                            ->title('Xabar mavjud davom etsangiz bo\'ladi')
                                            ->success()
                                            ->duration(5000)
                                            ->color('success')
                                            ->send();
                                    } else {
                                        FilamentNotification::make()
                                         ->title('Xabar mavjud emas tekshirib qaytadan kiriting!')
                                         ->danger()
                                         ->duration(5000)
                                         ->color('danger')
                                         ->send();
                                    }
                                }
                            })
                    ),
                Forms\Components\Select::make('filter_by_language')
                    ->label("Til bo'yicha filter")
                    ->helperText(new HtmlString("Agar O'zbek tili tanlansa faqat O'zbek tilini tanlagan faol foydalanuvchilarga jo'natiladi agar boshqa til tanlansa o'sha tilni tanlagan foydalanuvchilarga yuboriladi.<br>Odatiy holatda barcha foydalanuvchilarga jo'natiladi."))
                    ->options($langs)
                    ->default('*')
                    ->required(),
                Forms\Components\Select::make('sending_type')
                    ->label("Xabarni jo'natish turi")
                    ->helperText(new HtmlString("Oddiy xolatda jo'natish tanlansa xabar bot nomida jo'natiladi.<br>Forward xolatda jo'natish tanlansa xabar reklama joylangan kanaldan forward qilinadi, bu xolatda jo'natish sekinroq bo'ladi<br><b>Oddiy ko'rinishda jo'natish tavsiya etiladi</b>"))
                    ->options([
                        'copymessage' => "Oddiy xolatda jo'natish",
                        'forwardmessage' => "Forward xolatda jo'natish"
                    ])
                    ->default('copymessage')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        define('LANGS', ['*' => "Barcha foydalanuvchilarga"] + Lang::pluck('name', 'short_code')->toArray());
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(function ($record): string {
                        $status = [
                            'waiting' => "Navbatda kutilmoqda",
                            'sending' => "Xozirda jo'natilmoqda",
                            'terminated' => "Yakunlanmasdan, to'xtatilgan",
                            'completed' => "Yuborish yakunlangan"
                        ];
                        return $status[$record->status];
                    })
                    ->label("Xabar xolati"),
                Tables\Columns\TextColumn::make('sent')
                    ->label('Yuborildi'),
                Tables\Columns\TextColumn::make('not_sent')
                    ->label('Yuborilmadi'),
                    Tables\Columns\TextColumn::make('sending_type')
                    ->label("Xabarni yuborish turi")
                    ->formatStateUsing(function ($record) {
                        $types = [
                            'copymessage' => "Oddiy xolatda jo'natish",
                            'forwardmessage' => "Forward xolatda jo'natish"
                        ];
                        return $types[$record->sending_type];
                    }),
                Tables\Columns\TextColumn::make('filter_by_language')
                    ->label("Kimlarga yuborilgan")
                    ->formatStateUsing(function ($record) {
                        return LANGS[$record->filter_by_language];
                    }),
                Tables\Columns\TextColumn::make('sending_end_time')
                    ->label('Yuborish yakunlangan vaqt')
                    ->state(function ($record) {
                        return empty($record->sending_end_time) ? 'Yakunlanmagan' : $record->sending_end_time;
                    })

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultSort('id', 'desc')
            ->heading('Ommaviy jo\'natilgan xabarlar');
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
