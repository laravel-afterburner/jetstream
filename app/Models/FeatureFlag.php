<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'enabled',
        'options',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'options' => 'array',
        ];
    }

    /**
     * Normalize the key to snake_case when setting.
     * This ensures consistency - all keys are stored in snake_case format (Laravel database convention).
     *
     * @param  string  $value
     * @return void
     */
    public function setKeyAttribute(string $value): void
    {
        // Convert hyphens to underscores to normalize to snake_case
        $this->attributes['key'] = str_replace('-', '_', $value);
    }

    /**
     * Find a feature flag by key, handling both kebab-case and snake_case formats.
     * This ensures backward compatibility during migration.
     *
     * @param  string  $key
     * @return \App\Models\FeatureFlag|null
     */
    public static function findByKey(string $key): ?self
    {
        // Normalize to snake_case (Laravel convention)
        $normalizedKey = str_replace('-', '_', $key);
        
        // Try normalized key first (snake_case)
        $flag = static::where('key', $normalizedKey)->first();
        
        // If not found, try original key (for backward compatibility)
        if ($flag === null) {
            $flag = static::where('key', $key)->first();
        }
        
        return $flag;
    }

    /**
     * Update or create a feature flag, handling key normalization to snake_case.
     *
     * @param  string  $key
     * @param  array  $values
     * @return \App\Models\FeatureFlag
     */
    public static function updateOrCreateByKey(string $key, array $values): self
    {
        // Normalize key to snake_case (Laravel database convention)
        $normalizedKey = str_replace('-', '_', $key);
        
        // First, try to find by normalized key (snake_case)
        $flag = static::where('key', $normalizedKey)->first();
        
        // If not found, try original key (for backward compatibility during migration)
        if ($flag === null) {
            $flag = static::where('key', $key)->first();
            
            // If found old format record, update it to snake_case
            if ($flag) {
                $flag->update(array_merge($values, ['key' => $normalizedKey]));
                return $flag->fresh();
            }
        }
        
        // If found snake_case record, just update it
        if ($flag) {
            $flag->update($values);
            return $flag->fresh();
        }
        
        // Create new record with normalized key (snake_case)
        return static::create(array_merge($values, ['key' => $normalizedKey]));
    }
}
