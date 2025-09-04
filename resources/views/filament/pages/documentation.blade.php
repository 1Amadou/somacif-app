<x-filament-panels::page>

    {{-- MENU DE NAVIGATION RAPIDE --}}
    <x-filament::section>
        <x-slot name="heading">
            Sommaire
        </x-slot>
        <div class="prose dark:prose-invert max-w-none">
            <ol class="space-y-2">
                <li><a href="#section-workflow" class="text-primary-600 hover:underline">Le Workflow Général</a></li>
                <li>
                    <a href="#section-details" class="text-primary-600 hover:underline">Détail des Modules</a>
                    <ul class="ml-4 space-y-1 mt-1">
                        <li><a href="#module-catalogue" class="text-gray-600 dark:text-gray-300 hover:underline">3.1 Le Catalogue (Produits & Unités de Vente)</a></li>
                        <li><a href="#module-arrivage" class="text-gray-600 dark:text-gray-300 hover:underline">3.2 Gestion des Stocks : L'Arrivage</a></li>
                        <li><a href="#module-commande" class="text-gray-600 dark:text-gray-300 hover:underline">3.3 La Distribution : Les Commandes</a></li>
                        <li><a href="#module-vente" class="text-gray-600 dark:text-gray-300 hover:underline">3.4 La Vente (Vente Directe & Règlement)</a></li>
                        <li><a href="#module-suivi" class="text-gray-600 dark:text-gray-300 hover:underline">3.5 Le Suivi & les Rapports</a></li>
                        <li><a href="#module-frontend" class="text-gray-600 dark:text-gray-300 hover:underline">3.6 Le Site Public (Frontend)</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#section-scenarios" class="text-primary-600 hover:underline">Scénarios d'Utilisation</a>
                    <ul class="ml-4 space-y-1 mt-1">
                        <li><a href="#scenario-arrivage" class="text-gray-600 dark:text-gray-300 hover:underline">Scénario 1 : Gérer l'Arrivage d'une Nouvelle Cargaison</a></li>
                        <li><a href="#scenario-vente" class="text-gray-600 dark:text-gray-300 hover:underline">Scénario 2 : Effectuer une Vente Directe</a></li>
                    </ul>
                </li>
            </ol>
        </div>
    </x-filament::section>

    <div class="space-y-12">
        {{-- SECTION 1 : INTRODUCTION --}}
        <x-filament::section id="section-introduction">
            <x-slot name="heading">
                Guide d'Utilisation de l'Application SOMACIF
            </x-slot>
            
            <p class="text-gray-600 dark:text-gray-300">
                Bienvenue. Ce guide a pour but de vous expliquer en détail le fonctionnement de chaque module de l'application. Conçue pour le comptable de SOMACIF, l'application est la pierre angulaire de la gestion des produits et de la comptabilité. Elle suit le flux réel de la marchandise, de son arrivée à sa vente finale, en s'adaptant à la réalité de la vente de poisson.
            </p>
        </x-filament::section>

        {{-- SECTION 2 : LE WORKFLOW GLOBAL --}}
        <x-filament::section id="section-workflow">
            <x-slot name="heading">
                Le Workflow Général
            </x-slot>

            <div class="prose dark:prose-invert max-w-none">
                <p>L'application est construite autour d'un cycle de vie logique et traçable de la marchandise :</p>
                <ol>
                    <li>
                        <strong>1. Le Catalogue :</strong> Créez les <a href="{{ route('filament.admin.resources.products.index') }}" class="text-primary-600 hover:underline">Produits</a> (ex: Tilapia) et leurs <strong>Unités de Vente</strong> (ex: Carton 10kg, Calibre M).
                    </li>
                    <li>
                        <strong>2. L'Arrivage :</strong> Enregistrez la marchandise via un <a href="{{ route('filament.admin.resources.arrivages.index') }}" class="text-primary-600 hover:underline">Arrivage</a>. Cette action <strong>augmente le stock de l'entrepôt principal</strong>.
                    </li>
                    <li>
                        <strong>3. La Commande :</strong> Assignez le stock aux distributeurs via une <a href="{{ route('filament.admin.resources.orders.index') }}" class="text-primary-600 hover:underline">Commande</a>. Une fois la commande <strong>"Validée"</strong>, le stock est transféré de l'entrepôt principal vers l'inventaire du point de vente du distributeur.
                    </li>
                    <li>
                        <strong>4. La Vente :</strong> Enregistrez les ventes par <strong>Vente Directe</strong> (pour une vente au comptoir) ou <strong>Règlement Client</strong> (pour un compte-rendu du distributeur).
                    </li>
                    <li>
                        <strong>5. Le Suivi :</strong> Suivez la rentabilité et l'état des stocks d'une cargaison spécifique sur la page <a href="{{ route('filament.admin.pages.suivi-par-arrivage') }}" class="text-primary-600 hover:underline">Suivi par Arrivage</a>.
                    </li>
                </ol>
            </div>
        </x-filament::section>

        {{-- SECTION 3 : DÉTAILS DES MODULES --}}
        <x-filament::section id="section-details">
            <x-slot name="heading">
                Détail des Modules
            </x-slot>
            <div class="space-y-8">
                {{-- Module : Le Catalogue (Produits & Unités) --}}
                <div id="module-catalogue">
                    <h3 class="text-xl font-bold mb-2">3.1 Le Catalogue (Produits & Unités de Vente)</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p>
                            Le module de catalogue est la fondation de tout votre inventaire. C'est ici que vous définissez tous les articles que SOMACIF achète, stocke et vend. La logique est simple : un <strong>produit</strong> représente l'espèce ou le type de marchandise, tandis que les <strong>unités de vente</strong> (U.V.) représentent les différentes manières de conditionner et de vendre ce produit.
                        </p>
                        
                        <h4>Création d'un Produit</h4>
                        <p>
                            Un produit est l'entité de base. Pour en créer un, suivez ces étapes :
                        </p>
                        <ol>
                            <li>Dans la barre de navigation latérale, cliquez sur <strong>"Produits"</strong>.</li>
                            <li>Cliquez sur le bouton <strong>"Nouveau Produit"</strong>.</li>
                            <li>Entrez le nom du produit (ex. : <code>Tilapia</code>, <code>Sardine</code>, <code>Dorade Rose</code>).</li>
                            <li>Enregistrez.</li>
                        </ol>
                        <p>
                            Cette action crée l'entité principale. C'est après cette étape que vous pourrez y attacher des unités de vente.
                        </p>

                        <h4>Création d'une Unité de Vente (U.V.)</h4>
                        <p>
                            Chaque produit doit avoir au moins une unité de vente. L'unité de vente est cruciale car elle définit la manière dont le stock est géré et vendu. C'est l'U.V. qui porte les informations de stock et de prix.
                        </p>
                        <p>Pour ajouter une U.V. à un produit :</p>
                        <ol>
                            <li>Cliquez sur le produit concerné dans la liste.</li>
                            <li>Dans la section <strong>"Unités de Vente"</strong> en bas de la page, cliquez sur <strong>"Nouvelle Unité de Vente"</strong>.</li>
                            <li>Remplissez les champs :
                                <ul>
                                    <li><strong>Nom</strong> (ex. : <code>Carton 10kg</code>, <code>Pièce</code>, <code>Sachet 1kg</code>).</li>
                                    <li><strong>Prix de Vente</strong> (ex. : <code>4500</code> pour un carton de 10kg de Tilapia).</li>
                                    <li><strong>Calibre</strong> (ex. : <code>200-300g</code>, <code>Taille M</code>). Ce champ est essentiel pour la traçabilité des poissons de différentes tailles.</li>
                                </ul>
                            </li>
                            <li>Enregistrez l'unité de vente.</li>
                        </ol>

                        <h4>Logique de l'Inventaire : L'U.V. au Cœur du Stock</h4>
                        <p>
                            Il est important de noter que l'application ne gère pas le stock par "Produit" (ex. : stock total de "Tilapia"), mais par <strong>Unité de Vente</strong> (ex. : stock de "Tilapia Calibre L, Caisse 25kg"). Chaque arrivage, chaque commande et chaque vente incrémentent ou décrémentent la quantité de stock associée à une U.V. spécifique.
                        </p>
                    </div>
                </div>

                <hr class="border-t border-gray-200 dark:border-gray-700">

                {{-- Module : La Gestion des Stocks (Arrivage) --}}
                <div id="module-arrivage">
                    <h3 class="text-xl font-bold mb-2">3.2 Gestion des Stocks : L'Arrivage</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p>
                            L'Arrivage est le point d'entrée de la marchandise dans le système. C'est l'étape où vous enregistrez une nouvelle cargaison de poisson dès son arrivée sur le site. Un arrivage est plus qu'un simple ajout de stock ; il s'agit d'un <strong>document de traçabilité</strong> qui permet de lier le stock entrant à des informations clés, comme le fournisseur, la date de réception, et le coût d'achat unitaire.
                        </p>
                        
                        <h4>Processus d'enregistrement d'un arrivage</h4>
                        <ol>
                            <li>Dans le menu de navigation, cliquez sur <strong>"Arrivages"</strong>.</li>
                            <li>Cliquez sur le bouton <strong>"Nouvel Arrivage"</strong>.</li>
                            <li><strong>Informations générales :</strong> Remplissez la date de réception, le nom du fournisseur, et toute note pertinente.</li>
                            <li><strong>Ajout des produits :</strong>
                                <ul>
                                    <li>Cliquez sur <strong>"Ajouter un article"</strong>.</li>
                                    <li>Sélectionnez le <strong>Produit</strong> (ex. : <code>Tilapia</code>).</li>
                                    <li>Sélectionnez l'<strong>Unité de Vente</strong> (ex. : <code>Carton 10kg</code>). C'est l'U.V. qui définit la manière dont le stock sera géré.</li>
                                    <li>Indiquez la <strong>Quantité</strong> reçue.</li>
                                    <li>Entrez le <strong>Coût unitaire</strong> de cet U.V. (le prix que SOMACIF a payé).</li>
                                    <li>Répétez ces étapes pour chaque produit et chaque U.V. de la cargaison.</li>
                                </ul>
                            </li>
                            <li><strong>Validation :</strong> Une fois toutes les informations saisies, <strong>créez l'arrivage</strong>.</li>
                        </ol>
                        <p>
                            Dès que l'arrivage est créé, l'application <strong>augmente automatiquement le stock</strong> de chaque unité de vente correspondante dans l'entrepôt principal.
                        </p>
                    </div>
                </div>

                <hr class="border-t border-gray-200 dark:border-gray-700">

                {{-- Module : La Distribution (Commande) --}}
                <div id="module-commande">
                    <h3 class="text-xl font-bold mb-2">3.3 La Distribution : Les Commandes</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p>
                            Le module de <strong>commandes</strong> est le maillon essentiel entre votre entrepôt principal et les points de vente de vos distributeurs. C'est un document de <strong>transfert de stock</strong>. Son rôle principal est de déplacer une quantité de produits de votre stock central vers l'inventaire personnel d'un client de type "distributeur".
                        </p>
                        
                        <h4>Fonctionnement d'une commande</h4>
                        <ol>
                            <li><strong>Création :</strong> Lorsque vous préparez une commande pour un distributeur, vous la créez dans ce module. Vous y associez un client (qui doit être un "distributeur") et la liste des produits avec leurs unités de vente et les quantités correspondantes.</li>
                            <li><strong>Statut :</strong> Une commande passe par deux statuts :
                                <ul>
                                    <li><strong>"En attente" :</strong> C'est le statut par défaut. La commande est enregistrée, mais le stock n'a pas encore été transféré. Votre entrepôt principal n'est pas encore débité.</li>
                                    <li><strong>"Validée" :</strong> C'est l'action cruciale. Une fois la commande validée, l'application effectue deux actions importantes :
                                        <ul>
                                            <li><strong>Le stock de votre entrepôt principal est déduit</strong> de la quantité de chaque produit de la commande.</li>
                                            <li><strong>Le stock du point de vente du distributeur est augmenté</strong> de la même quantité.</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li><strong>Facturation :</strong> Une fois la commande validée, elle génère automatiquement une facture. Le montant total de cette facture est crédité sur le compte du distributeur, qui devient alors votre débiteur.</li>
                        </ol>
                    </div>
                </div>

                <hr class="border-t border-gray-200 dark:border-gray-700">

                {{-- Module : La Vente --}}
                <div id="module-vente">
                    <h3 class="text-xl font-bold mb-2">3.4 La Vente (Vente Directe & Règlement)</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p>
                            Ce module gère la sortie des produits du stock et la génération des revenus. Il existe deux types de ventes :
                        </p>
                        
                        <h4>Vente Directe</h4>
                        <p>
                            Ce type de vente est utilisé pour les transactions rapides, effectuées directement au comptoir. Il permet de vendre une petite quantité de stock de votre entrepôt principal à un client.
                        </p>
                        <ul>
                            <li><strong>Objectif :</strong> Enregistrer une vente immédiate.</li>
                            <li><strong>Impact sur le stock :</strong> Le stock est directement déduit de l'entrepôt principal.</li>
                            <li><strong>Processus :</strong>
                                <ol>
                                    <li>Rendez-vous dans la section <strong>"Ventes Directes"</strong>.</li>
                                    <li>Sélectionnez le client et les produits vendus avec les quantités.</li>
                                    <li>Créez la vente. L'application mettra à jour le stock et la comptabilité du client.</li>
                                </ol>
                            </li>
                        </ul>

                        <h4>Règlement Client</h4>
                        <p>
                            Ce module est spécifiquement conçu pour les distributeurs. Il permet au comptable d'enregistrer le compte-rendu d'un distributeur sur les ventes qu'il a effectuées.
                        </p>
                        <ul>
                            <li><strong>Objectif :</strong> Enregistrer les ventes d'un distributeur pour déduire le stock de son inventaire et mettre à jour sa créance.</li>
                            <li><strong>Impact sur le stock :</strong> Le stock est déduit de l'inventaire **personnel** du distributeur.</li>
                            <li><strong>Processus :</strong>
                                <ol>
                                    <li>Rendez-vous dans la section <strong>"Règlements"</strong>.</li>
                                    <li>Sélectionnez le distributeur et la ou les commandes concernées.</li>
                                    <li>Pour chaque commande, renseignez les quantités réellement vendues.</li>
                                    <li>Validez le règlement. L'application déduira les produits vendus de l'inventaire du distributeur et mettra à jour sa créance en fonction des paiements reçus.</li>
                                </ol>
                            </li>
                        </ul>
                    </div>
                </div>

                <hr class="border-t border-gray-200 dark:border-gray-700">

                {{-- Module : Le Suivi & Rapport --}}
                <div id="module-suivi">
                    <h3 class="text-xl font-bold mb-2">3.5 Le Suivi & les Rapports</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p>
                            Ce module vous offre une vue d'ensemble sur l'état de votre marchandise et de votre comptabilité.
                        </p>
                        
                        <h4>Suivi par Arrivage</h4>
                        <p>
                            C'est l'outil de traçabilité ultime. Il vous permet de suivre un arrivage spécifique de sa réception à sa vente finale.
                        </p>
                        <ul>
                            <li><strong>Objectif :</strong> Analyser la rentabilité d'une cargaison.</li>
                            <li><strong>Informations affichées :</strong> Vous pouvez voir les quantités restantes en entrepôt, les quantités distribuées, et les quantités vendues. L'application calcule aussi la **marge bénéficiaire** sur cet arrivage.</li>
                        </ul>
                        
                        <h4>Autres Rapports</h4>
                        <p>
                            L'application fournit des aperçus rapides sur la page d'accueil :
                        </p>
                        <ul>
                            <li><strong>Bilan de stock :</strong> Vue d'ensemble du stock total par produit, en incluant l'entrepôt principal et les inventaires des distributeurs.</li>
                            <li><strong>Statistiques de ventes :</strong> Graphiques et chiffres clés sur les ventes récentes et les performances globales.</li>
                        </ul>
                    </div>
                </div>

                <hr class="border-t border-gray-200 dark:border-gray-700">
                
                {{-- Module : Le Site Public (Frontend) --}}
                <div id="module-frontend">
                    <h3 class="text-xl font-bold mb-2">3.6 Le Site Public (Frontend)</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p>
                            Le site public de l'application SOMACIF agit comme une plateforme de communication et de génération de leads, destinée à trois types d'audiences : les grossistes, les hôtels & restaurants et les particuliers. Contrairement au tableau de bord de gestion, le frontend est principalement informatif, mais il intègre également une logique de conversion des visiteurs en partenaires.
                        </p>

                        <h4>Structure et Pages Clés</h4>
                        <p>
                            Le site est organisé en plusieurs pages, chacune ayant un objectif précis :
                        </p>
                        <ul>
                            <li><strong>Accueil (<code>/</code>) :</strong> La page d'accueil est la vitrine de la société. Elle met en avant la puissance de SOMACIF, ses produits phares, ses infrastructures et ses différents types de clients.</li>
                            <li><strong>Société (<code>/societe</code>) :</strong> Cette page présente l'histoire de SOMACIF, sa vision, ses infrastructures (capacité de stockage, chaîne du froid) et ses engagements envers la qualité et la fiabilité.</li>
                            <li><strong>Produits (<code>/produits</code>) :</strong> Cette page sert de catalogue public. Elle affiche les produits de la société, mais sans les prix, pour inciter les professionnels à devenir partenaires.</li>
                            <li><strong>Nos Offres Professionnelles (<code>/nos-offres</code>) :</strong> Ce hub pour les professionnels se divise en deux sections (Hôtels & Restaurants et Grossistes & Revendeurs), chacune expliquant les avantages et les services dédiés. Elle mène vers les formulaires de contact pour de futures collaborations.</li>
                        </ul>

                        <h4>Workflow de Conversion des Visiteurs</h4>
                        <p>
                            La logique principale du site public est de transformer les visiteurs intéressés en <strong>partenaires commerciaux</strong>. Ce processus se déroule en plusieurs étapes :
                        </p>
                        <ol>
                            <li><strong>Découverte :</strong> Un visiteur navigue sur le site et découvre les produits et les services de SOMACIF.</li>
                            <li><strong>Intérêt :</strong> En voyant l'offre dédiée, le visiteur est invité à devenir partenaire pour accéder aux prix et aux services exclusifs.</li>
                            <li><strong>Formulaire de Contact :</strong> Le visiteur remplit le formulaire sur la page <code>/grossistes</code> ou <code>/hotels-restaurants</code>.</li>
                            <li><strong>Action du Comptable :</strong> Une fois le formulaire soumis, le comptable (ou l'équipe commerciale) reçoit une notification. Il peut alors traiter la demande, créer un client dans le système d'administration et lui donner un accès au portail client.</li>
                        </ol>
                    </div>
                </div>

            </div>
        </x-filament::section>

        {{-- SECTION 4 : SCÉNARIOS D'UTILISATION --}}
        <x-filament::section id="section-scenarios">
            <x-slot name="heading">
                Scénarios d'Utilisation
            </x-slot>
            <div class="space-y-8">
                {{-- Scénario 1 : Arrivée d'un nouveau camion --}}
                <div id="scenario-arrivage">
                    <h3 class="text-xl font-bold mb-2">Scénario 1 : Gérer l'Arrivage d'une Nouvelle Cargaison</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p>
                            Un camion arrive avec une cargaison de <strong>100 cartons de Tilapia (10kg, Calibre M)</strong> et <strong>50 caisses de Sardine (20kg, Calibre S)</strong>.
                        </p>
                        <ol>
                            <li>
                                <strong>Enregistrer l'arrivage :</strong> Naviguez vers **Arrivages** > **Nouvel Arrivage**. Renseignez la date, le fournisseur, puis ajoutez deux articles :
                                <ul>
                                    <li>Produit : Tilapia, Unité : Carton 10kg, Quantité : 100</li>
                                    <li>Produit : Sardine, Unité : Caisse 20kg, Quantité : 50</li>
                                </ul>
                                Validez. Le stock de votre entrepôt augmente de 100 unités de Tilapia et 50 unités de Sardine.
                            </li>
                            <li>
                                <strong>Transférer le stock à un distributeur :</strong> Un distributeur, M. Bamba, prend 20 cartons de Tilapia et 10 caisses de Sardine.
                                <ul>
                                    <li>Naviguez vers **Commandes** > **Nouvelle Commande**.</li>
                                    <li>Sélectionnez M. Bamba comme client.</li>
                                    <li>Ajoutez les produits à la commande (20 Tilapia, 10 Sardine).</li>
                                    <li>**Validez la commande.** Le stock de l'entrepôt diminue de 20 et 10, et le stock de M. Bamba augmente des mêmes quantités.
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <strong>Enregistrer les ventes du distributeur :</strong> Quelques jours plus tard, M. Bamba rapporte avoir vendu 15 cartons de Tilapia et 8 caisses de Sardine.
                                <ul>
                                    <li>Naviguez vers **Règlements** > **Nouveau Règlement**.</li>
                                    <li>Sélectionnez M. Bamba.</li>
                                    <li>Pour la commande que vous avez créée, renseignez les quantités vendues (15 Tilapia, 8 Sardine).</li>
                                    <li>Validez le règlement. Le stock de M. Bamba est déduit de 15 et 8, et le système calcule le montant dû par M. Bamba.
                                    </li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </div>
                
                <hr class="border-t border-gray-200 dark:border-gray-700">

                {{-- Scénario 2 : Vente d'un produit --}}
                <div id="scenario-vente">
                    <h3 class="text-xl font-bold mb-2">Scénario 2 : Effectuer une Vente Directe</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        <p>
                            Un client de passage veut acheter <strong>2 caisses de Tilapia</strong>.
                        </p>
                        <ol>
                            <li>
                                Naviguez vers **Ventes Directes** > **Nouvelle Vente Directe**.
                            </li>
                            <li>
                                Sélectionnez le client et ajoutez le produit : Tilapia, Unité : Carton 10kg, Quantité : 2.
                            </li>
                            <li>
                                Validez la vente. Le stock de votre entrepôt principal est déduit de 2 cartons, et la créance du client est mise à jour.
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </x-filament::section>

    </div>

</x-filament-panels::page>