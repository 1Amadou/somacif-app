<x-filament-panels::page>

    <div class="space-y-8">
        {{-- SECTION 1 : INTRODUCTION --}}
        <x-filament::section>
            <x-slot name="heading">
                Bienvenue sur le Guide d'Utilisation de l'Application SOMACIF
            </x-slot>
            
            <p class="text-gray-600 dark:text-gray-300">
                Ce guide a pour but de vous expliquer le fonctionnement de chaque module de l'application. La logique de l'application suit le flux réel de la marchandise, de son arrivée à sa vente finale.
            </p>
        </x-filament::section>

        {{-- SECTION 2 : WORKFLOW GLOBAL --}}
        <x-filament::section>
            <x-slot name="heading">
                Le Workflow Général
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p>L'application est construite autour d'un cycle de vie logique et traçable :</p>
                <ol>
                    <li>
                        <strong>1. Le Catalogue :</strong> Tout commence par la création des 
                        <a href="{{ route('filament.admin.resources.products.index') }}" class="text-primary-600 hover:underline">Produits</a> 
                        (ex: Tilapia) et de leurs variantes, les 
                        <strong>Unités de Vente</strong> (ex: Carton 10kg, Calibre M). C'est la base de tout.
                    </li>
                    <li>
                        <strong>2. L'Arrivage :</strong> Quand un camion arrive, on enregistre la marchandise via un 
                        <a href="{{ route('filament.admin.resources.arrivages.index') }}" class="text-primary-600 hover:underline">Arrivage</a>. 
                        Cette action <strong>augmente le stock de l'entrepôt principal</strong>.
                    </li>
                    <li>
                        <strong>3. La Distribution :</strong> On assigne le stock aux distributeurs via une 
                        <a href="{{ route('filament.admin.resources.orders.index') }}" class="text-primary-600 hover:underline">Commande</a>. 
                        Une fois la commande <strong>"Validée"</strong>, le stock est transféré de l'entrepôt vers l'inventaire personnel du point de vente du distributeur.
                    </li>
                    <li>
                        <strong>4. La Vente :</strong> Les ventes sont enregistrées de deux manières :
                        <ul>
                            <li><strong>Vente Directe :</strong> Pour une vente rapide au comptoir. On choisit un client et le point de vente d'où le stock est retiré.</li>
                            <li><strong>Règlement Client :</strong> Pour un distributeur qui fait un compte-rendu. On sélectionne ses commandes en cours et on déclare les quantités vendues pour chaque produit.</li>
                        </ul>
                    </li>
                    <li>
                        <strong>5. Le Suivi :</strong> À tout moment, la page 
                        <a href="{{ route('filament.admin.pages.suivi-par-arrivage') }}" class="text-primary-600 hover:underline">Suivi par Arrivage</a> 
                        permet de voir la rentabilité et l'état des stocks pour une cargaison spécifique.
                    </li>
                </ol>
            </div>
        </x-filament::section>
    </div>

</x-filament-panels::page>