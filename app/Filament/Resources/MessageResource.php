<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Message;
use App\Services\TelegramService;
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

    protected static ?string $navigationIcon = 'heroicon-s-chat-bubble-oval-left-ellipsis';
    protected static ?string $navigationLabel = 'Xabarlar';

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
                        if ($state === 'text') {
                            $set('file_id', null);
                        }

                    })
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('file_id')
                    ->label('Fayl ID raqami')
                    ->nullable()
                    ->visible(fn(Get $get) => in_array($get('type'), ['photo', 'video']))
                    ->rules([
                        fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (empty($value))
                                return true;
                            $bot = new TelegramService();
                            $file_type = $get('type');
                            if ($file_type === 'photo') {
                                $try = $bot->sendPhoto([
                                    'chat_id' => env('DEV_ID'),
                                    'photo' => $value,
                                    'caption' => "Photo file id checking bro )"
                                ]);
                            } elseif ($file_type === 'video') {
                                $try = $bot->sendVideo([
                                    'chat_id' => env('DEV_ID'),
                                    'video' => $value,
                                    'caption' => "Video file id checking bro )"
                                ]);
                            }
                            if ($try['ok']) {
                                return true;
                            } else {
                                $fail('Fayl ID raqami ' . $file_type . ' uchun noto\'g\'ri');
                            }
                        }

                    ]),
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
                    ->visible(fn(Get $get) => in_array($get('type'), ['text', 'photo', 'video'])),

                Forms\Components\Textarea::make('buttons')
                    ->label('Tugmalar')
                    ->columnSpanFull()
                    ->visible(fn(Get $get) => in_array($get('type'), ['text', 'photo', 'video']))
                    ->rules([
                        fn(): Closure => function (string $attribute, $value, Closure $fail) {
                            function convertToTelegramInlineKeyboard($text, $limitPerRow = 5)
                            {
                                $keyboard = [];
                                $key = [];

                                // Split the input into rows based on newlines
                                $rows = explode("\n", $text);

                                foreach ($rows as $row) {
                                    // Match [text-url] pattern
                                    preg_match_all('/\[(.*?)\-(.*?)\]/', $row, $matches, PREG_SET_ORDER);

                                    foreach ($matches as $match) {
                                        $text = $match[1];
                                        $url = $match[2];

                                        // Add button to current row
                                        $key[] = ["text" => $text, "url" => $url];

                                        // If row reaches limit, add to keyboard and start a new row
                                        if (count($key) >= $limitPerRow) {
                                            $keyboard[] = $key;
                                            $key = [];
                                        }
                                    }

                                    // Add remaining buttons in the row
                                    if (!empty($key)) {
                                        $keyboard[] = $key;
                                        $key = [];
                                    }
                                }

                                return $keyboard;
                            }
                            $bot = new TelegramService();
                            $result = $bot->sendMessage([
                                'chat_id' => env('DEV_ID'),
                                'text' => 'Keyboard syntax checking bro )',
                                'reply_markup' => $bot->buildInlineKeyboard(convertToTelegramInlineKeyboard($value))
                            ]);
                            if ($result['ok']) {
                                return true;
                            } else {
                                $fail('Tugmalar xato yozilgan');
                            }
                        },
                    ]),
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
