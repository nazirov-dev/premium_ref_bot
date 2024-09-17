<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserIdentityDataResource\Pages;
use App\Filament\Resources\UserIdentityDataResource\RelationManagers;
use App\Models\UserIdentityData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserIdentityDataResource extends Resource
{
    /*
                $table->unsignedBigInteger('user_id');
            $table->string('timeOpened')->nullable();
            $table->string('timezone')->nullable();
            $table->string('browserLanguage')->nullable();
            $table->string('browserPlatform')->nullable();
            $table->string('sizeScreenW')->nullable();
            $table->string('sizeScreenH')->nullable();
            $table->string('sizeAvailW')->nullable();
            $table->string('sizeAvailH')->nullable();
            $table->string('ipAddress')->nullable();
            $table->string('userAgent')->nullable();
            $table->string('fingerprint')->nullable();
            $table->timestamps(); */
    protected static ?string $model = UserIdentityData::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationLabel = 'Qurilma ma\'lumotlari';
    protected static ?string $recordTitleAttribute = 'user_id';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'user_id',
            'timeOpened',
            'timezone',
            'browserLanguage',
            'browserPlatform',
            'sizeScreenW',
            'sizeScreenH',
            'sizeAvailW',
            'sizeAvailH',
            'ipAddress',
            'userAgent',
            'fingerprint'
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
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->label('User ID')
                    ->copyable()
                    ->copyMessage('ID raqamdan nusxa olindi')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('timeOpened')
                    ->label('Time Opened'),
                Tables\Columns\TextColumn::make('timezone')
                    ->label('Timezone'),
                Tables\Columns\TextColumn::make('browserLanguage')
                    ->label('Browser Language'),
                Tables\Columns\TextColumn::make('browserPlatform')
                    ->label('Browser Platform'),
                Tables\Columns\TextColumn::make('sizeScreenW')
                    ->label('Size Screen W'),
                Tables\Columns\TextColumn::make('sizeScreenH')
                    ->label('Size Screen H'),
                Tables\Columns\TextColumn::make('sizeAvailW')
                    ->label('Size Avail W'),
                Tables\Columns\TextColumn::make('sizeAvailH')
                    ->label('Size Avail H'),
                Tables\Columns\TextColumn::make('ipAddress')
                    ->label('IP Address')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('IP addressdan nusxa olindi')
                    ->copyMessageDuration(1500)
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('userAgent')
                    ->label('User Agent'),
                Tables\Columns\TextColumn::make('fingerprint')
                    ->label('Fingerprint')
                    ->copyable()
                    ->copyMessage('Fingerprintdan nusxa olindi')
                    ->copyMessageDuration(1500)
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan vaqti'),
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
            ->heading('Qurilma ma\'lumotlari');
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
            'index' => Pages\ListUserIdentityData::route('/'),
            'create' => Pages\CreateUserIdentityData::route('/create'),
            'edit' => Pages\EditUserIdentityData::route('/{record}/edit'),
        ];
    }
}
