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
    }
}