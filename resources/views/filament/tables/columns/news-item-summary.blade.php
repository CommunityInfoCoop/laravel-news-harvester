<div class="news-item-block mb-6 mt-2 pr-4 flex flex-row space-x-4 whitespace-normal">
    @php
        $record = $getRecord();
    @endphp
    <div class="grow flex flex-col">
        <div class="news-item-meta text-sm text-gray-600 dark:text-gray-300">
            <span class="news-item-source-name text-base block">{{ $record->source_info->name }}</span>
            <span class="news-item-publish-timestamp block">{{ $record->publish_timestamp->timezone(config('news-harvester.display_time_zone'))->format('M d, g:i a') }}</span>
        </div>
        @if(! $record->isSocial)
            <div class="news-item-title font-bold hover:underline focus:outline-none focus:underline filament-tables-link text-black-600 hover:text-black-500 dark:text-primary-500 dark:hover:text-primary-400">{{ $record->title }}</div>
            <div class="news-item-excerpt text-sm text-gray-600 dark:text-gray-300 pr-8 hidden sm:flex">{{ $record->excerpt }}</div>
        @else
            <div class="news-item-excerpt text-sm text-gray-600 dark:text-gray-300 pr-8 hidden sm:flex">{{ $record->content }}</div>
        @endif

    </div>
    @if($record->media_url)
        <div class="hidden sm:flex align-self-end items-center justify-center w-24 max-h-16 overflow-hidden">
            <img class="self-center" src="{{ $record->media_url }}" alt="Media image for {{ $record->title }}" />
        </div>
    @endif
</div>
