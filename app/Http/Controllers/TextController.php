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
}
