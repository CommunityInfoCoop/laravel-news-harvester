<?php

namespace CommunityInfoCoop\NewsHarvester\Models;

use App\Settings\FeedSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Feed extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'harvest_feeds';

    protected $fillable = [
        'source_id',
        'name',
        'type',
        'location',
        'internal_notes',
        'check_frequency',
        'last_check_at',
        'last_succeed_at',
        'last_new_item_at',
        'last_fail_at',
        'last_fail_reason',
        'fail_count',
        'next_check_after',
        'is_admin_paused',
        'is_starred',
        'is_active',
    ];

    protected $casts = [
        'last_check_at' => 'datetime',
        'last_succeed_at' => 'datetime',
        'last_new_item_at' => 'datetime',
        'last_fail_at' => 'datetime',
        'next_check_after' => 'datetime',
        'is_admin_paused' => 'boolean',
        'is_starred' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Source::class)->withTrashed();
    }

    public function newsItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NewsItem::class);
    }

    public function getUrlAttribute()
    {
        if (! empty($this->location)
            && filter_var($this->location, FILTER_VALIDATE_URL)
        ) {
            return $this->location;
        }

        return null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFailing(Builder $query): Builder
    {
        return $query->where('fail_count', '>', 0);
    }

    public function scopeStarred(Builder $query): Builder
    {
        return $query->where('is_starred', true);
    }

    /**
     * Scope a query to only include things that should be checked
     *
     * @param  Builder  $query
     * @return mixed
     */
    public function scopeCheckable(Builder $query)
    {
        $query
            ->where('is_active', true);

        $query
            ->where('is_admin_paused', false);

        // TODO ideally this would be determined in the config file, feed class or database
        $query
            ->whereNotIn('type', ['facebook_page', 'facebook_group']);

        $query->where(function ($query) {
            switch (env('DB_CONNECTION')) {
                case 'mysql':
                    $query
                        // Never been checked
                        ->whereNull('last_check_at')
                        // Haven't been checked in the last X minutes given feed-specific update frequency
                        ->orWhereRaw('last_check_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL check_frequency MINUTE)');
                    break;
                default:
                    $query
                        // Never been checked
                        ->whereNull('last_check_at')
                        ->orWhere('last_check_at', '<=', Carbon::now()->subMinutes(60));
            }
        });
        // Sources where no next check is set or where it has passed.
        $query->where(function ($query) {
            $query
                // Never been checked
                ->whereNull('next_check_after')
                // Haven't been checked in the last X minutes given sitewide update frequency
                ->orWhere(
                    'next_check_after',
                    '<=',
                    Carbon::now()
                );
        });

        return $query;
    }
}
