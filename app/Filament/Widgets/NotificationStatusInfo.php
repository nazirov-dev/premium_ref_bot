<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\NotificationStatus;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationStatusInfo extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan  = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $getNotificationStatus = NotificationStatus::where('id', 1)->first();
                return $getNotificationStatus->query();
            })
            ->columns([
                Tables\Columns\TextColumn::make('notification_id')
                    ->url(function ($record) {
                        return route('filament.admin.resources.notifications.edit', ['record' => $record->notification_id]);
                    })
                    ->label("Xabar id raqami")
                    ->icon('heroicon-s-eye'),
                Tables\Columns\TextColumn::make("sent")
                    ->label("Jo'natildi"),
                Tables\Columns\TextColumn::make("not_sent")
                    ->label("Jo'natilmadi"),
                Tables\Columns\TextColumn::make("status")
                    ->label("Xolat")
                    ->formatStateUsing(function ($record) {
                        return $record->status ? 'Xozirda xabar yuborilmoqda' : 'Xabar yuborilmayapti';
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Oxirgi o\'zgarish vaqti')

            ])
            ->paginated(false)
            ->heading("Ommaviy xabar jo'natish xolati")
            ->actions([
                Tables\Actions\Action::make("stop-sending")
                    ->label("Yuborishni to'xtatish")
                    ->icon('heroicon-m-archive-box-x-mark')
                    ->requiresConfirmation()
                    ->modalHeading('Xabar yuborishni to\'xtatish')
                    ->modalDescription("Xozirda xabar yuborilmoqda, yuborishni to'xtatishga ishonchingiz komilmi?")
                    ->modalSubmitActionLabel('Ha, yuborish to\'xtatilsin')
                    ->action(function ($record) {
                        $response = Http::get(route('stop-sending-notification', ['notification_id' => $record->notification_id, 'json' => true]));
                        if(isset($response['ok'])) {
                            FilamentNotification::make()
                                ->{$response['method']}()
                                ->seconds(10)
                                ->color($response['color'])
                                ->title($response['message'])
                                ->send();
                        } else {
                            FilamentNotification::make()
                                ->danger()
                                ->second(5)
                                ->color('danger')
                                ->title('Internet bilan bog\'liq muammo yuzaga keldi tekshirib qaytadan urinib ko\'ring.')
                                ->send();
                        }
                    })
                    ->disabled(fn($record) => (!$record->status))
                    ->color('danger'),
            ]);
    }
}
