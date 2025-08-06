<x-filament-panels::page>
    {{-- Cette ligne affichera la table des factures --}}
    <x-filament-widgets::widgets 
        :widgets="$this->getWidgets()"
        :columns="$this->getWidgetColumns()"
    />
</x-filament-panels::page>