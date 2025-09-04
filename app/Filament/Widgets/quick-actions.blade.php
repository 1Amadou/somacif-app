<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($this->getActions() as $action)
                <a
                    href="{{ $action['url'] }}"
                    @class([
                        'flex flex-col items-center justify-center p-6 rounded-xl shadow-lg transition-transform transform hover:scale-105',
                        
                        // Définition des classes de couleurs pour chaque type
                        'bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' => ($action['color'] ?? 'gray') === 'secondary', // Couleur secondaire
                        'bg-primary-600 text-white hover:bg-primary-500' => ($action['color'] ?? 'gray') === 'primary', // Couleur principale Filament
                        'bg-success-600 text-white hover:bg-success-500' => ($action['color'] ?? 'gray') === 'success', // Couleur verte
                        'bg-warning-600 text-white hover:bg-warning-500' => ($action['color'] ?? 'gray') === 'warning', // Couleur jaune
                    ])
                >
                    <x-filament::icon
                        :icon="$action['icon']"
                        class="h-10 w-10 mb-2"
                    />
                    <span class="text-sm font-semibold">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>