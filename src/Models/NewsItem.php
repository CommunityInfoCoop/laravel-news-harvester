<?php

namespace CommunityInfoCoop\NewsHarvester\Models;

use App\Settings\DisplaySettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsItem extends Model
{
    use HasFactory;

    protected $table = 'harvest_news_items';

    protected $fillable = [
        'feed_id',
        'source_id',
        'external_id',
        'feed_timestamp',
        'title',
        'url',
        'content',
        'media_url',
        'media_width',
        'media_height',
    ];

    protected $casts = [
        'feed_timestamp' => 'datetime',
    ];

    public function feed(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Feed::class)->withTrashed();
    }

    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Source::class)->withTrashed();
    }

    public function getPublishTimestampAttribute(): Carbon
    {
        if (! empty($this->feed_timestamp)) {
            return $this->feed_timestamp;
        } else {
            return $this->created_at;
        }
    }

    public function getPublishTimestampRelativeAttribute(): string
    {
        return $this->publish_timestamp->diffForHumans();
    }

    public function getSourceInfoAttribute()
    {
        if (! empty($this->feed->source)) {
            return $this->feed->source;
        } elseif (! empty($this->source)) {
            return $this->source;
        } else {
            return null;
        }
    }

    public function getExcerptAttribute()
    {
        $maxWords = 25;
        return Str::words(strip_tags($this->content), $maxWords);
    }

}
