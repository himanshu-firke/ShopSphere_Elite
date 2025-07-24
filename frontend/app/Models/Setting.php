<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'json'
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting_{$key}";

        return Cache::rememberForever($cacheKey, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, ?string $type = null, ?string $group = null, ?string $description = null): void
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type ?? 'string',
                'group' => $group,
                'description' => $description
            ]
        );

        Cache::forget("setting_{$key}");
    }

    /**
     * Get all settings by group
     */
    public static function getGroup(string $group): array
    {
        $cacheKey = "setting_group_{$group}";

        return Cache::rememberForever($cacheKey, function () use ($group) {
            return static::where('group', $group)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->value];
                })
                ->toArray();
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        $settings = static::all();

        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
        }

        $groups = $settings->pluck('group')->unique()->filter();
        foreach ($groups as $group) {
            Cache::forget("setting_group_{$group}");
        }
    }

    /**
     * Get the setting's value with proper type casting
     */
    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'array' => (array) $this->value,
            default => $this->value
        };
    }
} 