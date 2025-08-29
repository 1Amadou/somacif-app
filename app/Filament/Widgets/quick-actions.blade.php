@php
    $actions = $this->getActions();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach ($actions as $action)
                <a
                    href="{{ $action['url'] }}"
                    @class([
                        'flex items-center justify-center gap-x-2 rounded-lg px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors',
                        'bg-primary-600 hover:bg-primary-500' => $action['color'] === 'primary',
                        'bg-success-600 hover:bg-success-500' => $action['color'] === 'success',
                        'bg-gray-600 hover:bg-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700' => $action['color'] === 'gray',
                    ])
                >
                    <x-filament::icon
                        :icon="$action['icon']"
                        class="h-5 w-5"
                    />
                    <span>{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>