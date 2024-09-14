<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Closure;
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
                    ->default('text')
                    ->label('Turi')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('file_id', null);
                    })
                    ->live(true),
                Forms\Components\RichEditor::make('text')
                    ->label('Matn')
                    ->columnSpanFull()
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
                    ->visible(fn(Get $get) => $get('type') === 'text'),
                Forms\Components\Textarea::make('buttons')
                    ->label('Tugmalar')
                    ->columnSpanFull()
                    ->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            $text = $value;
                            $limitPerRow = 5;
                            $rows = explode("\n", $text);
                            $validPatternFound = false;
                            $valid = true;

                            foreach ($rows as $row) {
                                // Check for balanced brackets
                                $openBrackets = substr_count($row, '[');
                                $closeBrackets = substr_count($row, ']');
                                if ($openBrackets !== $closeBrackets) {
                                    $valid = false;
                                    break;
                                }

                                // Extract matches
                                preg_match_all('/\[(.*?)\-(.*?)\]/', $row, $matches, PREG_SET_ORDER);

                                if (!empty($matches)) {
                                    $validPatternFound = true;
                                }

                                foreach ($matches as $match) {
                                    $textPart = $match[1];
                                    $urlPart = $match[2];

                                    // Validate URL
                                    if (!filter_var($urlPart, FILTER_VALIDATE_URL)) {
                                        $valid = false;
                                        break;
                                    }
                                }

                                if (!$valid) {
                                    break;
                                }
                            }

                            // Check if at least one valid pattern was found and brackets are balanced
                            if (!$validPatternFound || !$valid) {
                                $fail("Tugmalar noto'g'ri formatda yozilgan. Misol: [Tugma matni - https://tugma.url]");
                            }
                        },
                    ]),
                Forms\Components\TextInput::make('file_id')
                    ->label('Fayl ID raqami')
                    ->nullable()
                    ->visible(fn(Get $get) => in_array($get('type'), ['photo', 'video'])),
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
