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
use Illuminate\Validation\Validator;
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
                            // Normalize whitespace
                            $text = preg_replace('/\s+/', ' ', $value);

                            // Step 1: Check if brackets are balanced
                            if (substr_count($text, '[') !== substr_count($text, ']')) {
                                $fail("Brackets are not balanced.");
                                return;
                            }

                            // Step 2: Match all button patterns
                            $pattern = '/\[(.*?) - (https?:\/\/[^\s\]]+)\]/';
                            preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

                            if (empty($matches)) {
                                $fail("No valid buttons found. Ensure buttons are in the format [Text - URL].");
                                return;
                            }

                            $buttons = [];
                            $rows = explode("\n", $text);

                            foreach ($rows as $row) {
                                preg_match_all($pattern, $row, $matches, PREG_SET_ORDER);

                                if (empty($matches)) {
                                    $fail("Invalid format in row: $row");
                                    return;
                                }

                                foreach ($matches as $match) {
                                    $url = trim($match[2]);

                                    // Check for valid URL format
                                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                                        $fail("Invalid URL format: " . $url);
                                        return;
                                    }

                                    $buttons[] = ["text" => trim($match[1]), "url" => $url];
                                }

                                // Check for button count per row
                                if (count($buttons) > 5) {
                                    $fail("A row cannot have more than 5 buttons.");
                                    return;
                                }
                            }

                            // If no issues, validation passes
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
