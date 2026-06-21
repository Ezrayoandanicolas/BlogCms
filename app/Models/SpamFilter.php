<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamFilter extends Model
{
    protected $fillable = ['keyword', 'type'];

    public $timestamps = true;

    public static function containsSpam(string $text): ?self
    {
        $textLower = strtolower($text);
        $filters = self::all();
        foreach ($filters as $filter) {
            if (str_contains($textLower, strtolower($filter->keyword))) {
                return $filter;
            }
        }
        return null;
    }

    public static function containsUrl(string $text): bool
    {
        return (bool) preg_match('/https?:\/\/[^\s]+|www\.[^\s]+/i', $text);
    }

    public static function seedDefaults(): void
    {
        $keywords = [
            ['keyword' => 'slot', 'type' => 'reject'],
            ['keyword' => 'judi', 'type' => 'reject'],
            ['keyword' => 'togel', 'type' => 'reject'],
            ['keyword' => 'casino', 'type' => 'reject'],
            ['keyword' => 'poker', 'type' => 'reject'],
            ['keyword' => 'taruhan', 'type' => 'reject'],
            ['keyword' => 'bandar', 'type' => 'reject'],
            ['keyword' => 'prediksi', 'type' => 'pending'],
            ['keyword' => 'bonus', 'type' => 'pending'],
            ['keyword' => 'free', 'type' => 'pending'],
            ['keyword' => 'gratis', 'type' => 'pending'],
        ];

        foreach ($keywords as $kw) {
            self::firstOrCreate(
                ['keyword' => $kw['keyword']],
                ['type' => $kw['type']]
            );
        }
    }
}
