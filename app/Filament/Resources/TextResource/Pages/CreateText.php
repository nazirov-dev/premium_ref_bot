<?php

namespace App\Filament\Resources\TextResource\Pages;

use App\Filament\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Text;
use App\Http\Controllers\TextController;
use Illuminate\Support\Facades\Log;
class CreateText extends CreateRecord
{
    protected static string $resource = TextResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        function cleanTelegramHtml($html)
        {
            // Step 1: Escape unsupported characters
            $html = htmlspecialchars($html, ENT_QUOTES, 'UTF-8');

            // Step 2: Replace supported tags or remove unsupported ones
            $html = preg_replace([
                '/<strong>(.*?)<\/strong>/i',    // <strong> -> <b>
                '/<b>(.*?)<\/b>/i',              // <b> -> <b>
                '/<em>(.*?)<\/em>/i',            // <em> -> <i>
                '/<i>(.*?)<\/i>/i',              // <i> -> <i>
                '/<ins>(.*?)<\/ins>/i',          // <ins> -> <u>
                '/<u>(.*?)<\/u>/i',              // <u> -> <u>
                '/<del>(.*?)<\/del>/i',          // <del> -> <s>
                '/<strike>(.*?)<\/strike>/i',    // <strike> -> <s>
                '/<s>(.*?)<\/s>/i',              // <s> -> <s>
                '/<span\s+class="tg-spoiler">(.*?)<\/span>/i', // <span class="tg-spoiler"> -> <tg-spoiler>
                '/<tg-spoiler>(.*?)<\/tg-spoiler>/i', // <tg-spoiler> -> <tg-spoiler>
                '/<a\s+href="(.*?)">(.*?)<\/a>/i',    // <a href="..."> -> <a href="...">
                '/<br\s*\/?>/i',                // <br> -> \n
                '/<pre>(.*?)<\/pre>/is',         // <pre> -> <pre>
                '/<code>(.*?)<\/code>/is',       // <code> -> <code>
                '/<blockquote>(.*?)<\/blockquote>/is', // <blockquote> -> <blockquote>
                '/<blockquote\s+expandable>(.*?)<\/blockquote>/is', // Expandable block quote
                '/<tg-emoji\s+emoji-id="(.*?)">(.*?)<\/tg-emoji>/i', // <tg-emoji> -> <tg-emoji>
            ], [
                '<b>$1</b>',   // Map <strong> to <b>
                '<b>$1</b>',
                '<i>$1</i>',   // Map <em> to <i>
                '<i>$1</i>',
                '<u>$1</u>',   // Map <ins> to <u>
                '<u>$1</u>',
                '<s>$1</s>',   // Map <del> to <s>
                '<s>$1</s>',
                '<s>$1</s>',
                '<tg-spoiler>$1</tg-spoiler>', // Map <span class="tg-spoiler"> to <tg-spoiler>
                '<tg-spoiler>$1</tg-spoiler>',
                '<a href="$1">$2</a>',   // Retain <a> tag for links
                "\n",                    // Replace <br> with newline
                '<pre>$1</pre>',         // Retain <pre> for code block
                '<code>$1</code>',       // Retain <code> for inline code
                '<blockquote>$1</blockquote>',   // Retain block quotes
                '<blockquote expandable>$1</blockquote>',   // Retain expandable block quotes
                '<tg-emoji emoji-id="$1">$2</tg-emoji>',    // Retain <tg-emoji>
            ], $html);

            // Step 3: Strip remaining unsupported tags (allowing only Telegram-supported tags)
            $html = strip_tags($html, '<b><i><u><s><tg-spoiler><a><code><pre><blockquote><tg-emoji>');

            return $html;
        }

        $cleaned = cleanTelegramHtml($data['value']);
        Log::info('CreateText: ', [$data, $cleaned]);
        TextController::set($data['key'], $data['value']);
        return $data;
    }

}
