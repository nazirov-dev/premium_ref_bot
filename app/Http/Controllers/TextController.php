<?php

namespace App\Http\Controllers;

use App\Models\Text;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TextController extends Controller
{
    public static function get($key, $default = null)
    {
        $text = Cache::rememberForever('text_' . $key, function () use ($key, $default) {
            return Text::where('key', $key)->first()->value ?? $default;
        });

        return $text;
    }

    public static function set($key, $value)
    {
        $result = Cache::put('text_' . $key, $value);
        return $result;
    }
    public static function clearAllTexts()
    {
        // Assume you're using Redis as your cache store
        $keys = Redis::keys('text_*');

        foreach ($keys as $key) {
            Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
        }

        return true;
    }

    public static function clearText($key)
    {
        $result = Cache::forget('text_' . $key);
        return $result;
    }
    public static function clearTexts($keys)
    {
        $fail = [];
        $success = [];
        foreach ($keys as $key) {
            $result = Cache::forget('text_' . $key);
            if ($result) {
                $success[] = $key;
            } else {
                $fail[] = $key;
            }
        }
        return ['fail' => $fail, 'success' => $success];
    }
    public static function recacheAllTexts($returnAsArray = false)
    {
        $texts = Text::all();
        $result = [];
        foreach ($texts as $text) {
            Cache::put('text_' . $text->key, $text->value);
            $result['text_' . $text->key] = $text->value;
        }
        if ($returnAsArray) {
            return $result;
        }
        return $result;
    }

    public static function sanitizeHtmlForTelegram($html)
    {
        // Step 1: Replace non-breaking spaces (&nbsp;) with regular spaces
        $html = str_replace('&nbsp;', ' ', $html);
        $html = str_replace('</p>', "</p>\n", $html);

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
            '/<span\s+style="text-decoration:\s*underline;">(.*?)<\/span>/i', // <span style="text-decoration: underline;"> -> <u>
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
            '<u>$1</u>',   // Map <span style="text-decoration: underline;"> to <u>
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
    public static function sanitizeHtmlForFilament($html)
    {
        // Step 1: Convert newlines to <br> tags for line breaks
        $html = nl2br($html);  // Converts all \n to <br>

        // Step 2: Ensure paragraphs are handled properly
        // If the string doesn't start with a newline, wrap it with <p> tags
        if (strpos($html, "\n") !== 0) {
            $html = '<p>' . $html;  // Start the first paragraph
        }

        // Step 3: Replace all \n with closing </p> and opening <p>
        $html = str_replace("\n", "</p><p>", $html);

        // Step 4: Ensure the string ends with a closing </p> tag
        if (substr($html, -4) !== '</p>') {
            $html .= '</p>';
        }

        return $html;
    }

}
