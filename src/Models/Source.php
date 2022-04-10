<?php

namespace CommunityInfoCoop\NewsHarvester\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Source extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'harvest_sources';

    protected $fillable = [
        'name',
        'url',
        'type',
        'internal_notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function feeds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Feed::class);
    }

    public function newsItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NewsItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
