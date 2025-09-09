<x-filament-panels::page>

    {{-- On utilise Alpine.js pour gérer l'état de la page (quel onglet est actif) --}}
    <div x-data="{
        activeTab: 'introduction',
        setActiveTab(tab) {
            this.activeTab = tab;
            // Fait défiler la page vers le haut lors du changement de section
            $refs.contentArea.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }" class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- COLONNE DE NAVIGATION LATÉRALE --}}
        <div class="lg:col-span-3">
            <div class="sticky top-24">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Guide d'Utilisation SOMACIF</h3>
                <nav class="flex flex-col space-y-1">
                    @php
                        $navItems = [
                            'introduction' => ['icon' => 'heroicon-o-sparkles', 'label' => 'Introduction & Philosophie'],
                            'module1' => ['icon' => 'heroicon-o-tag', 'label' => 'Module 1 : Le Catalogue'],
                            'module2' => ['icon' => 'heroicon-o-archive-box-arrow-down', 'label' => 'Module 2 : L\'Arrivage'],
                            'module3' => ['icon' => 'heroicon-o-shopping-cart', 'label' => 'Module 3 : La Commande'],
                            'module4' => ['icon' => 'heroicon-o-banknotes', 'label' => 'Module 4 : Le Règlement'],
                            'module5' => ['icon' => 'heroicon-o-bolt', 'label' => 'Module 5 : La Vente Directe'],
                            'module6' => ['icon' => 'heroicon-o-arrows-right-left', 'label' => 'Module 6 : La Réallocation'],
                            'module7' => ['icon' => 'heroicon-o-chart-pie', 'label' => 'Module 7 : L\'Analyse'],
                            'module8' => ['icon' => 'heroicon-o-users', 'label' => 'Module 8 : Gestion des Partenaires'],
                        ];
                    @endphp

                    @foreach ($navItems as $tab => $item)
                        <a @click.prevent="setActiveTab('{{ $tab }}')" href="#"
                           :class="{
                               'bg-primary-600 text-white': activeTab === '{{ $tab }}',
                               'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800': activeTab !== '{{ $tab }}'
                           }"
                           class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200">
                            <x-dynamic-component :component="$item['icon']" class="h-5 w-5 mr-3"/>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- CONTENU PRINCIPAL --}}
        <div class="lg:col-span-9 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 max-h-[80vh] overflow-y-auto" x-ref="contentArea">
            <div class="p-8 prose dark:prose-invert max-w-none">

                {{-- Chaque section est un bloc conditionnel Alpine.js --}}
                <div x-show="activeTab === 'introduction'" class="animate-fade-in">
                    <h2>Introduction : La Philosophie SOMACIF</h2>
                    <p>
                        Cette application a été conçue sur deux piliers fondamentaux pour garantir une gestion sans faille, précise et traçable de l'ensemble de vos opérations. Comprendre cette philosophie est la clé pour maîtriser l'outil.
                    </p>
                    <div class="space-y-4">
                        <div class="info-box border-primary-500 bg-primary-50 dark:bg-primary-500/10">
                            <h4>Pilier n°1 : La Traçabilité Absolue</h4>
                            <p>Chaque mouvement de stock, de l'entrée initiale à la vente finale, est enregistré. Il n'existe aucune action "magique" ou "cachée". Si un carton est déplacé, le système sait d'où il vient, où il va, et pourquoi. Cela garantit qu'aucun produit ne peut être "perdu" dans le système.</p>
                        </div>
                        <div class="info-box border-success-500 bg-success-50 dark:bg-success-500/10">
                            <h4>Pilier n°2 : L'Inventaire comme Source Unique de Vérité</h4>
                            <p>La quantité de stock d'un produit dans un lieu (Entrepôt ou Point de Vente) est définie <strong>uniquement</strong> par la table d'inventaire. Toutes les actions (Arrivage, Commande, Règlement, Transfert) ne font que modifier cette table de manière contrôlée. Il n'y a pas d'autre source d'information sur le stock, ce qui élimine tout risque de incohérence.</p>
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'module1'" class="animate-fade-in">
                    <h2>Module 1 : Le Catalogue</h2>
                    <h3>Objectif</h3>
                    <p>Définir précisément chaque type de marchandise que vous vendez et ses déclinaisons commerciales (calibres, poids).</p>
                    <h3>Workflow Détaillé</h3>
                    <ol>
                        <li><strong>Créer un Produit :</strong> Allez dans <strong>Catalogue > Produits</strong>. Créez un produit générique (ex: `Tilapia`).</li>
                        <li><strong>Créer ses Unités de Vente :</strong> Sur la page du produit, allez dans l'onglet <strong>Unités de Vente</strong>. C'est ici que vous créez les versions commercialisables (ex: `Carton 10kg - Moyen (200-300g)`) et que vous définissez les différents prix.</li>
                    </ol>
                    <h3>Conséquences et Impacts</h3>
                    <ul>
                        <li><strong>Impact sur le Stock :</strong> Le stock est toujours géré au niveau de l'<strong>Unité de Vente</strong>.</li>
                        <li><strong>Impact sur les Commandes :</strong> Les commandes se font toujours en sélectionnant une <strong>Unité de Vente</strong> spécifique.</li>
                        <li class="warning-box"><strong>Point de Vigilance :</strong> Une Unité de Vente ne devrait pas être supprimée si elle a déjà été utilisée, pour préserver l'historique des données.</li>
                    </ul>
                </div>
                
                <div x-show="activeTab === 'module2'" class="animate-fade-in">
                    <h2>Module 2 : L'Arrivage</h2>
                    <h3>Objectif</h3>
                    <p>Enregistrer toute nouvelle marchandise entrant dans votre <strong>Entrepôt Principal</strong>. C'est la seule porte d'entrée du stock dans tout le système.</p>
                    <h3>Workflow Détaillé</h3>
                    <ol>
                        <li><strong>Créer un Arrivage :</strong> Allez dans <strong>Gestion de Stock > Arrivages</strong>. Remplissez les informations du bon de livraison.</li>
                        <li><strong>Détailler les Articles :</strong> Ajoutez chaque <strong>Unité de Vente</strong> reçue, avec la <strong>quantité exacte</strong> de cartons et le <strong>coût d'achat unitaire</strong>.</li>
                    </ol>
                    <h3>Conséquences et Impacts</h3>
                    <ul>
                        <li><strong>Impact sur l'Inventaire :</strong> Le système <strong>augmente automatiquement</strong> le stock de chaque Unité de Vente, mais <strong>uniquement</strong> dans l'Entrepôt Principal.</li>
                        <li><strong>Impact sur l'Analyse :</strong> Cet arrivage devient la base de calcul pour le rapport "Suivi par Arrivage".</li>
                        <li class="warning-box"><strong>Point de Vigilance :</strong> Le <strong>coût d'achat unitaire</strong> est une donnée cruciale pour le calcul de la rentabilité. Assurez-vous qu'il soit précis.</li>
                    </ul>
                </div>
                
                <div x-show="activeTab === 'module3'" class="animate-fade-in">
                    <h2>Module 3 : La Commande</h2>
                    <h3>Objectif</h3>
                    <p>Gérer le transfert de la marchandise de votre entrepôt vers le stock de vos clients distributeurs.</p>
                    <h3>Workflow Détaillé</h3>
                    <ol>
                        <li><strong>Création :</strong> Allez dans <strong>Ventes & Commandes > Commandes</strong>. Créez une commande pour un client. Le statut initial est <strong>"En attente"</strong>. Aucun mouvement de stock n'a lieu.</li>
                        <li><strong>Validation (Étape Clé) :</strong> Modifiez la commande et changez son statut à <strong>"Validée"</strong>.</li>
                    </ol>
                    <h3>Conséquences et Impacts</h3>
                    <ul>
                        <li><strong>Impact sur l'Inventaire :</strong> Le système va instantanément <strong>décrémenter</strong> le stock de l'Entrepôt Principal et <strong>incrémenter</strong> celui du Point de Vente du client.</li>
                        <li><strong>Résultat :</strong> La marchandise a quitté votre entrepôt et est maintenant sous la responsabilité du client.</li>
                    </ul>
                </div>
                
                <div x-show="activeTab === 'module4'" class="animate-fade-in">
                    <h2>Module 4 : Le Règlement</h2>
                    <h3>Objectif</h3>
                    <p>Enregistrer la vente finale au consommateur, déclarée par votre distributeur. C'est la sortie définitive du stock.</p>
                    <h3>Workflow Détaillé</h3>
                    <ol>
                        <li><strong>Création :</strong> Sur la page d'une commande, cliquez sur <strong>"Enregistrer un Nouveau Règlement"</strong>.</li>
                        <li><strong>Détailler les Ventes :</strong> Listez les articles réellement vendus et leur prix de vente final. Le système vérifiera que le stock est bien disponible dans le point de vente du client.</li>
                    </ol>
                    <h3>Conséquences et Impacts</h3>
                    <ul>
                        <li><strong>Impact sur l'Inventaire :</strong> Le système <strong>décrémente définitivement</strong> le stock du Point de Vente concerné.</li>
                        <li><strong>Impact Financier :</strong> Le statut de paiement de la commande est automatiquement mis à jour (Non payée, Partiellement réglé, Complètement réglé).</li>
                    </ul>
                </div>

                <div x-show="activeTab === 'module5'" class="animate-fade-in">
                    <h2>Module 5 : La Vente Directe</h2>
                    <h3>Objectif</h3>
                    <p>Simuler le processus complet (Commande + Règlement) en une seule action pour les ventes au comptoir, tout en respectant la traçabilité.</p>
                    <h3>Workflow Détaillé</h3>
                    <ol>
                        <li><strong>Utiliser le Formulaire :</strong> Allez dans <strong>Ventes & Commandes > Vente Directe</strong>. Remplissez toutes les informations.</li>
                        <li><strong>Action Atomique :</strong> À la validation, le système exécute une transaction unique qui :
                             <ul>
                                <li>Crée une Commande avec le statut <strong>"Validée"</strong> (le stock passe de l'Entrepôt au PDV).</li>
                                <li>Crée immédiatement un Règlement qui solde cette commande (le stock quitte le PDV).</li>
                            </ul>
                        </li>
                    </ol>
                    <h3>Conséquences et Impacts</h3>
                    <ul>
                        <li><strong>Résultat :</strong> Le flux de stock est parfait (Entrepôt -> PDV -> Sortie) et la vente est tracée comme une commande normale, garantissant la cohérence de tous vos rapports.</li>
                    </ul>
                </div>

                <div x-show="activeTab === 'module6'" class="animate-fade-in">
                    <h2>Module 6 : La Réallocation de Commande</h2>
                    <h3>Objectif</h3>
                    <p>Gérer les ajustements logistiques et déplacer du stock d'une commande existante vers une nouvelle commande pour un autre client.</p>
                    <h3>Workflow Détaillé</h3>
                    <ol>
                        <li><strong>Créer un Transfert :</strong> Allez dans <strong>Gestion de Stock > Réallocation de Commande</strong>.</li>
                        <li><strong>Sélectionner la Source et la Destination :</strong> Choisissez la commande source, puis le client et le point de vente de destination.</li>
                    </ol>
                    <h3>Conséquences et Impacts</h3>
                    <ul>
                        <li><strong>Impact sur la Commande Source :</strong> La quantité des articles transférés est <strong>réduite</strong> et son montant total est recalculé.</li>
                        <li><strong>Impact sur la Destination :</strong> Une <strong>nouvelle commande</strong> est créée pour le client de destination avec les articles transférés.</li>
                        <li><strong>Impact sur l'Inventaire :</strong> Le stock est physiquement déplacé du PDV source vers le PDV de destination.</li>
                    </ul>
                </div>
                
                <div x-show="activeTab === 'module7'" class="animate-fade-in">
                    <h2>Module 7 : L'Analyse</h2>
                    <h3>Objectif</h3>
                    <p>Fournir des outils de pilotage pour suivre votre activité et analyser la rentabilité.</p>
                    <h3>Les Rapports Disponibles</h3>
                    <ul>
                        <li><strong>Tableau de Bord Principal :</strong> Vue d'ensemble de l'activité, statistiques clés et raccourcis.</li>
                        <li><strong>Stock Entrepôt Principal :</strong> Indicateurs clés de votre entrepôt (valeur du stock, revenu potentiel) et vue visuelle de chaque produit.</li>
                        <li><strong>Suivi par Arrivage :</strong> Votre centre de profit. Calcule la rentabilité d'une cargaison en comparant le coût d'achat avec les revenus réels générés par les ventes.</li>
                    </ul>
                </div>

                <div x-show="activeTab === 'module8'" class="animate-fade-in">
                    <h2>Module 8 : Gestion des Partenaires</h2>
                    <h3>Objectif</h3>
                    <p>Gérer l'ensemble du cycle de vie de vos partenaires, de leur demande initiale à la gestion de leur compte.</p>
                    <h3>Workflow Détaillé</h3>
                    <ol>
                        <li><strong>Demande de Partenariat :</strong> Les candidats soumettent leur demande via le formulaire public. Les demandes arrivent dans <strong>Clients & Partenaires > Demandes de Partenariat</strong> avec le statut "En attente".</li>
                        <li><strong>Approbation :</strong> Vous pouvez revoir, modifier et approuver une demande. En approuvant :
                            <ul>
                                <li>Un <strong>compte Client</strong> est automatiquement créé.</li>
                                <li>Un e-mail est envoyé au nouveau partenaire avec son <strong>identifiant unique</strong> et un <strong>mot de passe temporaire</strong>.</li>
                                <li>La demande est "verrouillée" et ne peut plus être modifiée.</li>
                            </ul>
                        </li>
                        <li><strong>Gestion du Compte Client :</strong> Une fois créé, vous pouvez gérer le partenaire via <strong>Clients & Partenaires > Clients</strong>, y compris l'activation ou la désactivation de son compte.</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    
    {{-- CSS pour le style --}}
    <style>
        .animate-fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        [x-ref="contentArea"]::-webkit-scrollbar { width: 8px; }
        [x-ref="contentArea"]::-webkit-scrollbar-track { background: transparent; }
        [x-ref="contentArea"]::-webkit-scrollbar-thumb { background-color: rgba(var(--primary-500-rgb), 0.5); border-radius: 4px; }
        [x-ref="contentArea"]::-webkit-scrollbar-thumb:hover { background-color: rgba(var(--primary-600-rgb), 0.7); }
        .prose h2 { font-size: 1.875rem; line-height: 2.25rem; margin-bottom: 1.5rem; }
        .prose h3 { font-size: 1.25rem; line-height: 1.75rem; margin-top: 2rem; margin-bottom: 1rem; font-weight: 600; }
        .prose .info-box { padding: 1rem; border-left-width: 4px; }
        .prose .info-box h4 { margin-top: 0; margin-bottom: 0.5rem; font-size: 1rem; }
        .prose .warning-box { color: #b45309; } /* dark:text-amber-400 */
    </style>

</x-filament-panels::page>