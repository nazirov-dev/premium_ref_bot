<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TextResource\Pages;
use App\Filament\Resources\TextResource\RelationManagers;
use App\Models\Text;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TextResource extends Resource
{
    protected static ?string $model = Text::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationLabel = 'Textlar';


    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Kalit so\'z')
                    ->required(),
                Forms\Components\RichEditor::make('value')
                    ->label('Matn')
                    ->required()
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'codeBlock',
                        'italic',
                        'link',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->label('Kalit so\'z')->searchable(),
                Tables\Columns\TextColumn::make('value')->label('Matn')->searchable()->html()->listWithLineBreaks()->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
            ])
            ->defaultSort('id', 'desc')
            ->heading('Matnlar')
            ->searchOnBlur();
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
            'index' => Pages\ListTexts::route('/'),
            // 'create' => Pages\CreateAllTextForLang::route('/create'),
            'create' => Pages\CreateText::route('/create'),
            'edit' => Pages\EditText::route('/{record}/edit'),
        ];
    }
}
