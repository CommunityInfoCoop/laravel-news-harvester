<?php
// config for CommunityInfoCoop/NewsHarvester
return [
    /*
    |--------------------------------------------------------------------------
    | What time zone should be used when dates are displayed to users
    |--------------------------------------------------------------------------
    */

    'display_time_zone' => 'America/New_York',

    /*
    |--------------------------------------------------------------------------
    | Feeds
    |--------------------------------------------------------------------------
    |
    | Inform the news harvest fetch commands how long to wait for a remote
    | site to timeout before giving up, and how often to wait in between
    | checking for new updates from individual feeds.
    */

    'feeds' => [
        'fetch_timeout' => 15, // in seconds
        'check_frequency' => 60, // default, in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Select menu options
    |--------------------------------------------------------------------------
    |
    | Set what values are allowed for various selection menus in the dashboard
    */

    'select_options' => [
        'source_types' => [
            'news' => 'News Publisher',
            'government' => 'Government Entity',
            'school' => 'School',
            'business' => 'Business',
            'organization' => 'Organization',
            'group' => 'Social Group',
            'person' => 'Individual',
        ],
        'feed_types' => [
            'rss' => 'RSS Feed',
            'facebook_group' => 'Facebook Group',
            'facebook_page' => 'Facebook Page',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Top Sources Tag
    |--------------------------------------------------------------------------
    |
    | Which source tag to use in default filtering of content
    */

    'top_sources_tag' => 'Top',

    /*
    |--------------------------------------------------------------------------
    | Module specific configuration options
    |--------------------------------------------------------------------------
    */

    'modules' => [
        'crowdtangle' => [
            // The CrowdTangle API key
            'api_token' => env('CROWDTANGLE_API_TOKEN', ''),
            // For newly imported accounts/feeds. If null, a new source will be created
            'default_source_id' => null,
            // How far back (in SQL age syntax) to go when fetching new posts
            'post_fetch_timeframe' => '2 HOUR',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Command scheduling
    |--------------------------------------------------------------------------
    |
    | Determine whether various news harvest commands should be scheduled
    | automatically ("auto") or if you want to schedule them manually ("off")
    */

    'command_scheduling' => 'auto',
];
