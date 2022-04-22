<?php

namespace CommunityInfoCoop\NewsHarvester\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Tags\HasTags;

class Source extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTags;

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

    public function newsItems(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(NewsItem::class, Feed::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTop(Builder $query): Builder
    {
        return $query->withAnyTags([config('news-harvester.top_sources_tag', 'Top')], 'source');
    }
}
