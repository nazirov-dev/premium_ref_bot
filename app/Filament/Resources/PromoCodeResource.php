<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Filament\Resources\PromoCodeResource\RelationManagers;
use App\Models\PromoCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    /* table structure:
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('premium_category_id');
            $table->foreign('premium_category_id')->references('id')->on('premium_categories')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->dateTime('expired_at')->nullable();
            $table->enum('status', [
                'active',
                'expired',
                'completed',
                'canceled'
            ])->default('active');
            $table->timestamps(); */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Promo code')
                    ->unique()
                    ->required(),
                Forms\Components\TextInput::make('user_id')
                    ->label('User ID')
                    ->required(),
                Forms\Components\Select::make('premium_category_id')
                    ->label('Premium category ID')
                    ->options(
                        \App\Models\PremiumCategory::all()->pluck('name', 'id')->toArray()
                    )
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required(),
                Forms\Components\DateTimePicker::make('expired_at')
                    ->label('Expired at')
                    ->nullable(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'completed' => 'Completed',
                        'canceled' => 'Canceled',
                    ])
                    ->default('active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Promo code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->label('User ID')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('premium_category_id')
                    ->label('Premium category')
                    ->options(
                        \App\Models\PremiumCategory::all()->pluck('name', 'id')->toArray()
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->suffix('so\'m')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expired_at')
                    ->label('Expired at')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),
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
            ->heading('Promo codes');
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
            'index' => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'edit' => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
