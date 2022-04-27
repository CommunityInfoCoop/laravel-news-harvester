<div class="news-item-block mb-6 mt-2 px-4 py-1 flex flex-row space-x-4 whitespace-normal">
    @php
        $record = $getRecord();
    @endphp
    <div class="grow flex flex-col">
        <div class="flex justify-between items-center">
            <div class="justify-start flex">
                <span class="px-4 py-3 text-gray-700 dark:text-gray-200">
                    @if($record->isSocial)
                        <x-heroicon-o-chat-alt-2 class="w-6 h-6" />
                    @else
                        <x-heroicon-o-rss class="w-6 h-6" />
                    @endif
                </span>
                <div class="news-item-meta">
                    <span class="news-item-source-name block text-base text-gray-900 dark:text-gray-100">{{ $record->source_info->name }}</span>
                    <span class="news-item-publish-timestamp block text-gray-600 dark:text-gray-300">{{ $record->publish_timestamp->timezone(config('news-harvester.display_time_zone'))->format('M d, g:i a') }}</span>
                </div>
            </div>
            <div class="px-4 py-3 whitespace-nowrap">
                <a wire:loading.attr="disabled" class="inline-flex items-center justify-center font-medium tracking-tight rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset filament-button dark:focus:ring-offset-0 h-8 px-3 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700 filament-tables-button-action" href="{{ $record->url }}" target="_blank">
                    <x-heroicon-o-external-link class="w-5 h-5" />
                </a>
            </div>
        </div>
        <a class="flex flex-col items-start mt-2" href="{{ $record->url }}" target="_blank">
        @if($record->isSocial)
            <div class="news-item-content text-base text-gray-900 dark:text-gray-100 pr-8">{{ $record->content }}</div>
            @if($record->media_url)
                <div class="items-center justify-center overflow-hidden py-2">
                    <img class="self-center" src="{{ $record->media_url }}" alt="Media image for {{ $record->title }}" />
                </div>
            @endif
        @else
            <div class="flex flex-row items-start">
                @if($record->media_url)
                    <img class="hidden sm:flex object-cover w-32 max-h-16 px-4 py-2" src="{{ $record->media_url }}" alt="Media image for {{ $record->title }}" />
                @endif
                <div class="flex flex-col justify-between leading-normal">
                    <div class="news-item-title mb-2 text-xl font-bold tracking-tight hover:underline focus:outline-none focus:underline filament-tables-link text-black-600 hover:text-black-500 dark:text-primary-500 dark:hover:text-primary-400">{{ $record->title }}</div>
                    <div class="news-item-excerpt mb-3 text-base font-normal text-gray-900 dark:text-gray-100">{{ $record->excerpt }}</div>
                </div>
            </div>

        @endif
        </a>
    </div>
</div>
