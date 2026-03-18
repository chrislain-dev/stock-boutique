<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // ─── Récupérer une valeur avec fallback config ────────────
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) return $default ?? config("boutique.{$key}");
            return match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                default   => $setting->value,
            };
        });
    }

    // ─── Mettre à jour et vider le cache ─────────────────────
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'label' => $key, // fallback si pas encore en DB
                'type'  => 'string',
                'group' => explode('.', $key)[0] ?? 'general',
            ]
        );
        Cache::forget("setting_{$key}");
    }
}
