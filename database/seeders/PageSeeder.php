<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {

        // PAGE SYSTÈME : HEADER
        Page::updateOrCreate(['slug' => '_header'], [
            'titres' => ['site_title' => 'SOMACIF'],
            'contenus' => [
                'menu_items' => [
                    ['label' => 'Accueil', 'url' => '/'],
                    ['label' => 'Société', 'url' => '/societe'],
                    ['label' => 'Produits', 'url' => '/produits'],
                    ['label' => 'Actualités', 'url' => '/actualites'],
                    ['label' => 'Contact', 'url' => '/contact'],
                ]
            ],
            'images' => ['logo' => null]
        ]);

        // PAGE SYSTÈME : FOOTER
        Page::updateOrCreate(['slug' => '_footer'], [
            'contenus' => [
                'description' => 'Le leader de la distribution de poisson congelé au Mali.',
                'quick_links' => [
                    ['label' => 'Notre société', 'url' => '/societe'],
                    ['label' => 'Nos produits', 'url' => '/produits'],
                    ['label' => 'Points de vente', 'url' => '/points-de-vente'],
                ],
                'legal_links' => [
                    ['label' => 'Politique de Confidentialité', 'url' => '/politique-confidentialite'],
                    ['label' => 'Conditions Générales', 'url' => '/conditions-generales'],
                ],
                'contact_info' => [
                    'address' => 'ACI 2000, Bamako, Mali',
                    'email' => 'contact@somacif.com',
                ],
                'social_links' => [
                    ['icon' => 'fab fa-facebook-f', 'url' => '#'],
                    ['icon' => 'fab fa-instagram', 'url' => '#'],
                    ['icon' => 'fab fa-tiktok', 'url' => '#'],
                ]
            ]
        ]);

        // PAGES LÉGALES
        Page::updateOrCreate(['slug' => 'politique-confidentialite'], [
            'titres' => ['header_title' => 'Politique de Confidentialité'],
            'contenus' => ['main_content' => '<p>Contenu à rédiger...</p>']
        ]);
        Page::updateOrCreate(['slug' => 'conditions-generales'], [
            'titres' => ['header_title' => 'Conditions Générales d\'Utilisation'],
            'contenus' => ['main_content' => '<p>Contenu à rédiger...</p>']
        ]);
        // Page d'Accueil MISE À JOUR
        Page::updateOrCreate(['slug' => 'accueil'], [
            'titres' => [
                'hero_title' => 'La Puissance <br><span class="brand-red">au service du frais</span>',
                'products_title' => 'Nos Incontournables',
                'clients_title' => 'Nous servons tous les marchés',
                'infra_title' => 'Des infrastructures inégalées',
                'news_title' => 'Le Marché, vu par le Leader',
                'pos_title' => 'Retrouvez-nous à Bamako',
            ],
            'contenus' => [
                'hero_subtitle' => 'Le plus grand stock de produits de la mer au Mali, au service de tous.',
                'products_subtitle' => 'Une disponibilité garantie',
                'clients_subtitle' => 'Un service pour chacun',
                'clients_grossistes_title' => 'Grossistes & Revendeurs',
                'clients_grossistes_text' => 'Commandes en grande quantité, tarifs préférentiels et logistique impeccable.',
                'clients_hr_title' => 'Hôtels & Restaurants',
                'clients_hr_text' => 'Qualité constante, découpes sur mesure et livraisons fiables pour votre établissement.',
                'clients_particuliers_title' => 'Particuliers & Familles',
                'clients_particuliers_text' => 'La meilleure qualité de poisson pour vos repas quotidiens, disponible près de chez vous.',
                'infra_subtitle' => 'Notre Force',
                'infra_text' => 'Avec nos entrepôts modernes et notre flotte de camions réfrigérés, nous garantissons une chaîne du froid ininterrompue, de la mer à votre assiette.',
                'news_subtitle' => 'Notre Expertise',
                'pos_subtitle' => 'Notre Réseau',
                'pos_text' => 'La qualité SOMACIF est accessible partout dans la capitale. Visitez nos points de vente pour découvrir l\'ensemble de notre gamme et bénéficier des conseils de nos équipes.',
            ],
            'images' => [
                'hero_gallery' => [], // Changé pour une galerie
                'clients_grossistes_bg' => null,
                'clients_hr_bg' => null,
                'clients_particuliers_bg' => null,
                'infra_image' => null,
                'pos_map_image' => null,
            ],
        ]);

        // NOUVELLE PAGE : GROSSISTES
        Page::updateOrCreate(['slug' => 'grossistes'], [
            'titres' => [
                'header_title' => 'Devenez notre Partenaire Grossiste',
                'presentation_title' => 'La Puissance SOMACIF au Service de Votre Marge',
                'services_title' => 'Vos Avantages Exclusifs',
                'how_it_works_title' => 'Comment ça marche ?',
                'form_title' => 'Prêt à Développer Votre Activité ?',
            ],
            'contenus' => [
                'header_subtitle' => 'Accédez au plus grand stock du Mali et à des tarifs imbattables.',
                'presentation_text' => 'En tant que grossiste ou revendeur, votre succès dépend de la fiabilité de votre approvisionnement et de la compétitivité de vos prix. SOMACIF est le partenaire stratégique qui vous garantit les deux.',
                'services' => [
                    ['icon' => 'fas fa-warehouse', 'title' => 'Tarifs Dégressifs', 'description' => 'Bénéficiez de prix ultra-compétitifs sur les commandes en grande quantité.'],
                    ['icon' => 'fas fa-truck', 'title' => 'Logistique Prioritaire', 'description' => 'Vos commandes sont préparées et expédiées en priorité pour ne jamais être en rupture de stock.'],
                    ['icon' => 'fas fa-box-open', 'title' => 'Accès à Tout le Catalogue', 'description' => 'Offrez à vos clients la gamme la plus large de produits de la mer au Mali.'],
                ],
                'how_it_works_steps' => [
                    ['title' => '1. Postulez', 'description' => 'Remplissez le formulaire ci-dessous. Notre équipe B2B vous contacte sous 24h.'],
                    ['title' => '2. Validez', 'description' => 'Après signature du contrat, nous activons votre compte et votre identifiant unique.'],
                    ['title' => '3. Commandez', 'description' => 'Connectez-vous à votre portail pour commander en quelques clics aux tarifs qui vous sont réservés.'],
                ],
                'form_subtitle' => 'Remplissez ce formulaire pour être contacté par notre équipe commerciale.',
            ],
            'images' => ['header_background' => null, 'presentation_image' => null]
        ]);

        // NOUVELLE PAGE : HÔTELS & RESTAURANTS
        Page::updateOrCreate(['slug' => 'hotels-restaurants'], [
            'titres' => [
                'header_title' => 'Solutions pour Hôtels & Restaurants',
                'presentation_title' => 'La Qualité et la Constance que vos Clients Méritent',
                'services_title' => 'Un Service Pensé pour les Chefs',
                'how_it_works_title' => 'Comment ça marche ?',
                'form_title' => 'Prêt à Sublimer votre Carte ?',
            ],
            'contenus' => [
                'header_subtitle' => 'Un approvisionnement fiable pour une cuisine d\'exception.',
                'presentation_text' => 'La réputation de votre établissement repose sur la qualité de vos plats. SOMACIF vous assure une constance et une fraîcheur irréprochables pour que vous puissiez vous concentrer sur votre art.',
                 'services' => [
                    ['icon' => 'fas fa-star', 'title' => 'Qualité Gastronomique', 'description' => 'Des produits sélectionnés pour leur calibre et leur saveur, parfaits pour vos plats signatures.'],
                    ['icon' => 'fas fa-ruler-combined', 'title' => 'Découpes sur Mesure', 'description' => 'Optimisez votre temps en cuisine avec nos services de préparation personnalisés.'],
                    ['icon' => 'fas fa-calendar-alt', 'title' => 'Livraisons Planifiées', 'description' => 'Mettez en place un calendrier de livraison régulier pour un approvisionnement sans faille.'],
                ],
                'how_it_works_steps' => [
                    ['title' => '1. Contactez-nous', 'description' => 'Remplissez le formulaire ci-dessous. Un conseiller dédié à la restauration vous rappelle.'],
                    ['title' => '2. Définissons vos besoins', 'description' => 'Nous validons ensemble vos produits, calibres et fréquences de livraison.'],
                    ['title' => '3. Commandez en toute simplicité', 'description' => 'Utilisez votre portail personnalisé pour passer ou ajuster vos commandes.'],
                ],
                'form_subtitle' => 'Remplissez ce formulaire pour être contacté par notre équipe dédiée CHR.',
            ],
            'images' => ['header_background' => null, 'presentation_image' => null]
        ]);
        
        // NOUVELLE PAGE : PARTICULIERS
Page::updateOrCreate(['slug' => 'particuliers'], [
    'titres' => [
        'header_title' => 'La Qualité SOMACIF pour Tous',
        'presentation_title' => 'Le Meilleur de la Mer, Directement chez Vous',
        'services_title' => 'Une Expérience d\'Achat Simplifiée',
        'how_it_works_title' => 'Comment ça marche ?',
        'form_title' => 'Envie de devenir un point de vente ?',
    ],
    'contenus' => [
        'header_subtitle' => 'Savourez des produits de la mer d\'une fraîcheur et d\'une qualité exceptionnelles, sans vous déplacer.',
        'presentation_text' => 'SOMACIF rend l\'excellence accessible à chaque famille malienne. Nous vous offrons une gamme variée de poissons et fruits de mer congelés, sélectionnés avec le plus grand soin pour garantir saveur et valeur nutritive.',
        'services' => [
            ['icon' => 'fas fa-fish', 'title' => 'Qualité Imbattable', 'description' => 'Chaque produit est pêché et surgelé dans les meilleures conditions pour préserver sa fraîcheur.'],
            ['icon' => 'fas fa-store', 'title' => 'Points de Vente Locaux', 'description' => 'Retrouvez nos produits dans nos boutiques partenaires et les meilleurs supermarchés près de chez vous.'],
            ['icon' => 'fas fa-heart', 'title' => 'Une Gamme pour la Famille', 'description' => 'Des poissons entiers aux filets, nous avons tout ce qu\'il vous faut pour des repas équilibrés et savoureux.'],
        ],
        'how_it_works_steps' => [
            ['title' => '1. Trouvez-nous', 'description' => 'Utilisez notre carte interactive pour localiser le point de vente SOMACIF le plus proche.'],
            ['title' => '2. Faites votre choix', 'description' => 'Explorez notre sélection de produits, depuis le capitaine au tilapia en passant par la crevette.'],
            ['title' => '3. Savourez', 'description' => 'Préparez un repas délicieux et sain pour votre famille en quelques minutes.'],
        ],
        'form_subtitle' => 'Si vous êtes une boutique ou un supermarché et que vous souhaitez distribuer nos produits, remplissez ce formulaire.',
    ],
    'images' => ['header_background' => null, 'presentation_image' => null]
]);

        // Page Société MISE À JOUR
        Page::updateOrCreate(['slug' => 'societe'], [
            'titres' => [
                'header_title' => 'Notre Société',
                'history_title' => 'Connecter le Mali à la richesse des océans',
                'infra_title' => 'Des Infrastructures de Leader',
                'commitments_title' => 'Notre Promesse Client',
                'partner_cta_title' => 'Devenez Partenaire',
                'products_cta_title' => 'Nos Produits du Jour',
            ],
            'contenus' => [
                'header_subtitle' => "Bâtir l'excellence, de l'océan jusqu'à vous.",
                'history_subtitle' => 'Notre Vision',
                'history_text' => "<p>Depuis sa création, SOMACIF poursuit un objectif ambitieux : devenir le pilier de la distribution de produits de la mer au Mali...</p>",
                'infra_subtitle' => 'Notre Puissance',
                'infra_text' => "Notre capacité à vous servir repose sur des fondations solides...",
                'infra_conclusion' => "Ces chiffres ne sont pas que des nombres. Ils représentent notre promesse...",
                'commitments_subtitle' => 'Nos Valeurs',
                'stats' => [
                    ['stat' => '5000+', 'label' => 'Tonnes de Stockage'],
                    ['stat' => '-20°C', 'label' => 'Température Contrôlée'],
                    ['stat' => '24/7', 'label' => 'Surveillance Continue'],
                    ['stat' => '100%', 'label' => 'Chaîne du Froid Maîtrisée'],
                ],
                'partner_cta_subtitle' => "Remplissez ce formulaire pour être contacté par notre équipe commerciale.",
                'products_cta_subtitle' => "Découvrez notre sélection quotidienne et préparez votre prochaine commande.",
                'engagements' => [
                    ['icon' => 'fas fa-award', 'title' => 'Qualité Supérieure', 'description' => 'Une sélection intransigeante des meilleurs produits auprès de partenaires certifiés.'],
                    ['icon' => 'fas fa-handshake', 'title' => 'Fiabilité Absolue', 'description' => 'Grâce à notre stock massif, nous honorons nos engagements et assurons un approvisionnement sans interruption.'],
                    ['icon' => 'fas fa-users', 'title' => 'Partenariat Durable', 'description' => 'Nous construisons des relations de confiance avec nos clients, qu\'ils soient professionnels ou particuliers.'],
                    ['icon' => 'fas fa-leaf', 'title' => 'Pêche Responsable', 'description' => 'Nous privilégions les sources d\'approvisionnement qui s\'engagent pour la préservation des ressources marines.'],
                ],
            ],
            'images' => [
                'header_background' => null,
                'history_image' => null,
                'infra_gallery' => [],
            ]
        ]);

        // Page Nos Offres
        Page::updateOrCreate(['slug' => 'nos-offres'], [
            'titres' => [
                'header_title' => 'Solutions Professionnelles',
                'offer_hr_title' => 'Hôtels & Restaurants',
                'offer_gros_title' => 'Grossistes & Revendeurs',
                'form_title' => 'Devenez Partenaire',
            ],
            'contenus' => [
                'header_subtitle' => "Votre partenaire de croissance pour un approvisionnement d'excellence.",
                'offer_hr_subtitle' => 'Pour les Chefs Exigeants',
                'offer_hr_text' => "Nous comprenons que la qualité et la constance sont les clés de votre réputation. C'est pourquoi nous vous offrons un service sur mesure pour répondre aux plus hautes exigences de la gastronomie.",
                'offer_gros_subtitle' => 'Pour les Partenaires de Croissance',
                'offer_gros_text' => "Devenez un distributeur de premier plan en vous appuyant sur la puissance de SOMACIF. Nous vous donnons les moyens de développer votre activité avec des solutions pensées pour le volume.",
                'form_subtitle' => "Remplissez ce formulaire pour être contacté par notre équipe commerciale. Ensemble, construisons un partenariat fructueux.",
            ],
            'images' => [
                'header_background' => null,
                'offer_hr_image' => null,
                'offer_gros_image' => null,
            ]
        ]);
        
        // Page Produits
        Page::updateOrCreate(['slug' => 'produits'], [
            'titres' => ['header_title' => 'Notre Catalogue'],
            'contenus' => ['header_subtitle' => 'Une sélection rigoureuse des meilleurs produits de la mer.'],
            'images' => ['header_background' => null]
        ]);

        // Page Actualités
        Page::updateOrCreate(['slug' => 'actualites'], [
            'titres' => ['header_title' => 'Actualités'],
            'contenus' => ['header_subtitle' => 'Le marché des produits de la mer, analysé par le leader malien.'],
            'images' => ['header_background' => null]
        ]);
        
        // Page Points de Vente
        Page::updateOrCreate(['slug' => 'points-de-vente'], [
            'titres' => ['header_title' => 'Nos Points de Vente'],
            'contenus' => ['header_subtitle' => 'Trouvez le magasin SOMACIF le plus proche de chez vous à Bamako.'],
            'images' => ['header_background' => null]
        ]);

        // Page Contact
        Page::updateOrCreate(['slug' => 'contact'], [
            'titres' => ['header_title' => 'Contactez-nous'],
            'contenus' => ['header_subtitle' => 'Notre équipe est à votre disposition pour toute demande.'],
            'images' => ['header_background' => null]
        ]);

        Page::updateOrCreate(['slug' => 'catalogue-visiteur'], [
            'titres' => [
            'header_title' => 'Découvrez Notre Sélection de Produits de la Mer',
            'why_choose_title' => 'Pourquoi Choisir SOMACIF ?',
            'how_to_order_title' => 'Comment ça Marche pour Commander ?',
            'login_title' => 'Déjà Partenaire ? Connectez-vous',
            'become_partner_title' => 'Devenez Partenaire et Accédez à Notre Catalogue Complet',
            ],
            'contenus' => [
            'header_subtitle' => 'Une variété de produits frais et de qualité pour tous vos besoins.',
            'slider_placeholder' => 'Ajouter des images de vos produits ici via l\'admin !',
            'why_choose_text' => 'SOMACIF vous garantit une fraîcheur et une qualité irréprochables. Nos produits sont rigoureusement sélectionnés pour satisfaire les palais les plus exigeants.',
            'avantages' => [
            ['icon' => 'fas fa-fish', 'titre' => 'Fraîcheur Garantie', 'description' => 'Des produits fraîchement pêchés et livrés rapidement.'],
            ['icon' => 'fas fa-certificate', 'titre' => 'Qualité Supérieure', 'description' => 'Une sélection rigoureuse pour une qualité optimale.'],
            ['icon' => 'fas fa-users', 'titre' => 'Pour Tous les Besoins', 'description' => 'Que vous soyez particulier ou professionnel, trouvez votre bonheur.'],
            ],
            'how_to_order_text' => 'Pour consulter nos prix et passer commande, devenez partenaire en quelques étapes simples.',
            'etapes_commande' => [
            ['titre' => '1. Inscrivez-vous', 'description' => 'Remplissez notre formulaire de demande de partenariat.'],
            ['titre' => '2. Validation', 'description' => 'Notre équipe vous contacte pour valider votre inscription.'],
            ['titre' => '3. Accédez au Catalogue', 'description' => 'Connectez-vous et découvrez nos offres et passez votre commande.'],
            ],
            'login_subtitle' => 'Saisissez vos identifiants pour accéder à votre espace personnalisé.',
            'become_partner_text' => 'Profitez de tarifs exclusifs et d\'un large choix de produits en rejoignant notre réseau de partenaires.',
            ],
            'images' => [
                'header_background' => null,
                'slider_gallery' => [],
                ]
            
            ]);
    }
}