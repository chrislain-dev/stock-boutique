<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'logo_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Accessor : URL publique du logo ──────────────────────
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? Storage::url($this->logo_path)
            : null;
    }

    // ─── Relations ────────────────────────────────────────────
    public function productModels(): HasMany
    {
        return $this->hasMany(ProductModel::class);
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Product::class,
            \App\Models\ProductModel::class,
            'brand_id',
            'product_model_id',
            'id',
            'id'
        );
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
