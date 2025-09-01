<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Contenu des Pages';
    protected static ?string $navigationGroup = 'Contenu & Site Web';
    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        $page = $form->getRecord();

        return $form->schema(
            array_merge(
                [
                    Forms\Components\Section::make('Identification')->schema([
                        Forms\Components\TextInput::make('slug')->disabled(),
                    ]),
                ],
                self::getSpecificFormSchema($page?->slug),
                [
                    Forms\Components\Section::make('SEO')->schema([
                        Forms\Components\TextInput::make('meta_titre'),
                        Forms\Components\Textarea::make('meta_description')->rows(3),
                    ]),
                ]
            )
        );
    }

    private static function getSpecificFormSchema(string $slug = null): array
    {
        return match ($slug) {
            'grossistes', 'hotels-restaurants', 'particuliers' => [
            Forms\Components\Tabs::make('Contenu')->tabs([
                Forms\Components\Tabs\Tab::make('En-tête')->schema([
                    Forms\Components\TextInput::make('titres.header_title')->label('Titre Principal'),
                    Forms\Components\Textarea::make('contenus.header_subtitle')->label('Sous-titre'),
                    Forms\Components\FileUpload::make('images.header_background')->label('Image de fond')->image()->disk('public')->directory('pages'),
                ]),
                Forms\Components\Tabs\Tab::make('Présentation')->schema([
                    Forms\Components\TextInput::make('titres.presentation_title')->label('Titre'),
                    Forms\Components\Textarea::make('contenus.presentation_text')->label('Texte'),
                    Forms\Components\FileUpload::make('images.presentation_image')->label('Image d\'illustration')->image()->disk('public')->directory('pages'),
                ]),
                Forms\Components\Tabs\Tab::make('Services / Avantages')->schema([
                    Forms\Components\TextInput::make('titres.services_title')->label('Titre de la section'),
                    Forms\Components\Repeater::make('contenus.services')->schema([
                        Forms\Components\TextInput::make('icon')->label('Icône FontAwesome (ex: fas fa-truck)')->required(),
                        Forms\Components\TextInput::make('title')->label('Titre du service')->required(),
                        Forms\Components\Textarea::make('description')->label('Description')->required(),
                    ])->columns(3),
                ]),
                Forms\Components\Tabs\Tab::make('Comment ça marche')->schema([
                    Forms\Components\TextInput::make('titres.how_it_works_title')->label('Titre de la section'),
                    Forms\Components\Repeater::make('contenus.how_it_works_steps')->schema([
                        Forms\Components\TextInput::make('title')->label('Titre de l\'étape')->required(),
                        Forms\Components\Textarea::make('description')->label('Description')->required(),
                    ])->columns(2),
                ]),
                 Forms\Components\Tabs\Tab::make('Formulaire')->schema([
                    Forms\Components\TextInput::make('titres.form_title')->label('Titre de la section formulaire'),
                    Forms\Components\Textarea::make('contenus.form_subtitle')->label('Sous-titre de la section formulaire'),
                ]),
            ]),
        ],
        // 'particuliers' => [
        //     // Formulaire simple pour la page Particuliers
        // ],

            '_header' => [
            Forms\Components\Section::make('Header Global')->schema([
                Forms\Components\FileUpload::make('images.logo')->label('Logo du site (PNG ou SVG)')->disk('public')->directory('site'),
                Forms\Components\Repeater::make('contenus.menu_items')->label('Éléments du menu principal')->schema([
                    Forms\Components\TextInput::make('label')->required(),
                    Forms\Components\TextInput::make('url')->required()->helperText('Ex: /contact'),
                ])->columns(2),
            ])
        ],
        '_footer' => [
            Forms\Components\Section::make('Footer Global')->schema([
                Forms\Components\Textarea::make('contenus.description')->label('Texte de description'),
                Forms\Components\Repeater::make('contenus.quick_links')->label('Colonne Liens Rapides')->schema([
                    Forms\Components\TextInput::make('label')->required(),
                    Forms\Components\TextInput::make('url')->required(),
                ])->columns(2),
                Forms\Components\Repeater::make('contenus.legal_links')->label('Colonne Liens Légaux')->schema([
                    Forms\Components\TextInput::make('label')->required(),
                    Forms\Components\TextInput::make('url')->required(),
                ])->columns(2),
                Forms\Components\Repeater::make('contenus.social_links')->label('Réseaux Sociaux')->schema([
                    Forms\Components\TextInput::make('icon')->label('Icône FontAwesome (ex: fab fa-facebook-f)')->required(),
                    Forms\Components\TextInput::make('url')->label('URL du profil')->required(),
                ])->columns(2),
            ])
        ],
        'politique-confidentialite', 'conditions-generales' => [
            Forms\Components\Section::make('Contenu de la Page')->schema([
                Forms\Components\TextInput::make('titres.header_title')->label('Titre principal'),
                Forms\Components\RichEditor::make('contenus.main_content')->label('Contenu légal')->required(),
            ])
        ],
            'accueil' => [
                Forms\Components\Tabs::make('Contenu de la page d\'accueil')->tabs([
                    Forms\Components\Tabs\Tab::make('Héros')->schema([
                        Forms\Components\TextInput::make('titres.hero_title')->label('Titre Principal (HTML autorisé)'),
                        Forms\Components\Textarea::make('contenus.hero_subtitle')->label('Sous-titre'),
                        Forms\Components\FileUpload::make('images.hero_gallery')
                            ->label('Galerie d\'images de fond (Slider)')
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('pages')
                            ->reorderable(),
                    ]),
                    Forms\Components\Tabs\Tab::make('Produits & Clients')->schema([
                        Forms\Components\TextInput::make('contenus.products_subtitle')->label('Sous-titre section Produits'),
                        Forms\Components\TextInput::make('titres.products_title')->label('Titre section Produits'),
                        Forms\Components\TextInput::make('contenus.clients_subtitle')->label('Sous-titre section Clients'),
                        Forms\Components\TextInput::make('titres.clients_title')->label('Titre section Clients'),
                        
                        Forms\Components\Fieldset::make('Carte Grossistes')->schema([
                            Forms\Components\TextInput::make('contenus.clients_grossistes_title')->label('Titre'),
                            Forms\Components\Textarea::make('contenus.clients_grossistes_text')->label('Texte'),
                            Forms\Components\FileUpload::make('images.clients_grossistes_bg')->label('Image de fond')->image()->disk('public')->directory('pages'),
                        ]),
                        Forms\Components\Fieldset::make('Carte Hôtels & Restaurants')->schema([
                            Forms\Components\TextInput::make('contenus.clients_hr_title')->label('Titre'),
                            Forms\Components\Textarea::make('contenus.clients_hr_text')->label('Texte'),
                            Forms\Components\FileUpload::make('images.clients_hr_bg')->label('Image de fond')->image()->disk('public')->directory('pages'),
                        ]),
                        Forms\Components\Fieldset::make('Carte Particuliers')->schema([
                            Forms\Components\TextInput::make('contenus.clients_particuliers_title')->label('Titre'),
                            Forms\Components\Textarea::make('contenus.clients_particuliers_text')->label('Texte'),
                            Forms\Components\FileUpload::make('images.clients_particuliers_bg')->label('Image de fond')->image()->disk('public')->directory('pages'),
                        ]),
                    ]),
                    Forms\Components\Tabs\Tab::make('Infrastructures & Actualités')->schema([
                        Forms\Components\TextInput::make('contenus.infra_subtitle')->label('Sous-titre section Infrastructures'),
                        Forms\Components\TextInput::make('titres.infra_title')->label('Titre section Infrastructures'),
                        Forms\Components\Textarea::make('contenus.infra_text')->label('Texte section Infrastructures'),
                        Forms\Components\FileUpload::make('images.infra_image')->label('Image section Infrastructures')->image()->disk('public')->directory('pages'),
                        Forms\Components\TextInput::make('contenus.news_subtitle')->label('Sous-titre section Actualités'),
                        Forms\Components\TextInput::make('titres.news_title')->label('Titre section Actualités'),
                    ]),
                    Forms\Components\Tabs\Tab::make('Points de Vente')->schema([
                        Forms\Components\TextInput::make('contenus.pos_subtitle')->label('Sous-titre section Points de Vente'),
                        Forms\Components\TextInput::make('titres.pos_title')->label('Titre section Points de Vente'),
                        Forms\Components\Textarea::make('contenus.pos_text')->label('Texte section Points de Vente'),
                        Forms\Components\FileUpload::make('images.pos_map_image')->label('Image carte Points de Vente')->image()->disk('public')->directory('pages'),
                    ]),
                ])
            ],
            'societe' => [
                Forms\Components\Tabs::make('Contenu de la page Société')->tabs([
                    Forms\Components\Tabs\Tab::make('En-tête & Histoire')->schema([
                        Forms\Components\TextInput::make('titres.header_title')->label('Titre de l\'en-tête'),
                        Forms\Components\Textarea::make('contenus.header_subtitle')->label('Sous-titre de l\'en-tête'),
                        Forms\Components\FileUpload::make('images.header_background')->label('Image de fond')->image()->disk('public')->directory('pages'),
                        Forms\Components\TextInput::make('contenus.history_subtitle')->label('Sous-titre section Histoire'),
                        Forms\Components\TextInput::make('titres.history_title')->label('Titre section Histoire'),
                        Forms\Components\RichEditor::make('contenus.history_text')->label('Texte section Histoire'),
                        Forms\Components\FileUpload::make('images.history_image')->label('Image section Histoire')->image()->disk('public')->directory('pages'),
                    ]),
                    Forms\Components\Tabs\Tab::make('Infrastructures & Engagements')->schema([
                        Forms\Components\TextInput::make('contenus.infra_subtitle')->label('Sous-titre section Infrastructures'),
                        Forms\Components\TextInput::make('titres.infra_title')->label('Titre section Infrastructures'),
                        Forms\Components\Textarea::make('contenus.infra_text')->label('Texte d\'introduction Infrastructures'),
                        Forms\Components\FileUpload::make('images.infra_gallery')
                            ->label('Galerie d\'images de la section')
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('pages')
                            ->reorderable(),
                             Forms\Components\Repeater::make('contenus.stats')
                        ->label('Statistiques Clés')
                        ->schema([
                            Forms\Components\TextInput::make('stat')->label('Chiffre ou Statistique (ex: 5000+)')->required(),
                            Forms\Components\TextInput::make('label')->label('Description (ex: Tonnes de Stockage)')->required(),
                        ])
                        ->columns(2)
                        ->defaultItems(4)
                        ->maxItems(4)
                        ->addActionLabel('Ajouter une statistique'),
                        Forms\Components\Textarea::make('contenus.infra_conclusion')->label('Texte de conclusion Infrastructures'),
                        Forms\Components\TextInput::make('contenus.commitments_subtitle')->label('Sous-titre section Engagements'),
                        Forms\Components\TextInput::make('titres.commitments_title')->label('Titre section Engagements'),
                        Forms\Components\Repeater::make('contenus.engagements')
                            ->schema([
                                Forms\Components\TextInput::make('icon')->label('Icône (ex: fas fa-award)'),
                                Forms\Components\TextInput::make('title')->label('Titre'),
                                Forms\Components\Textarea::make('description')->label('Description'),
                            ])->columns(3),
                    ]),
                    Forms\Components\Tabs\Tab::make('Appels à l\'action (CTA)')->schema([
                        Forms\Components\TextInput::make('titres.partner_cta_title')->label('Titre CTA Partenaire'),
                        Forms\Components\Textarea::make('contenus.partner_cta_subtitle')->label('Sous-titre CTA Partenaire'),
                        Forms\Components\TextInput::make('titres.products_cta_title')->label('Titre CTA Produits'),
                        Forms\Components\Textarea::make('contenus.products_cta_subtitle')->label('Sous-titre CTA Produits'),
                    ]),
                ])
            ],
            'nos-offres' => [
                Forms\Components\Section::make('En-tête de page')->schema([
                    Forms\Components\TextInput::make('titres.header_title')->label('Titre'),
                    Forms\Components\Textarea::make('contenus.header_subtitle')->label('Sous-titre'),
                    Forms\Components\FileUpload::make('images.header_background')->label('Image de fond')->image()->disk('public')->directory('pages'),
                ]),
                Forms\Components\Section::make('Section Hôtels & Restaurants')->schema([
                    Forms\Components\TextInput::make('contenus.offer_hr_subtitle')->label('Sous-titre'),
                    Forms\Components\TextInput::make('titres.offer_hr_title')->label('Titre'),
                    Forms\Components\Textarea::make('contenus.offer_hr_text')->label('Texte'),
                    Forms\Components\FileUpload::make('images.offer_hr_image')->label('Image')->image()->disk('public')->directory('pages'),
                ]),
                Forms\Components\Section::make('Section Grossistes & Revendeurs')->schema([
                    Forms\Components\TextInput::make('contenus.offer_gros_subtitle')->label('Sous-titre'),
                    Forms\Components\TextInput::make('titres.offer_gros_title')->label('Titre'),
                    Forms\Components\Textarea::make('contenus.offer_gros_text')->label('Texte'),
                    Forms\Components\FileUpload::make('images.offer_gros_image')->label('Image')->image()->disk('public')->directory('pages'),
                ]),
            ],
            'actualites'|'points-de-vente'|'contact' => [
                 Forms\Components\Section::make('En-tête de page')->schema([
                    Forms\Components\TextInput::make('titres.header_title')->label('Titre'),
                    Forms\Components\Textarea::make('contenus.header_subtitle')->label('Sous-titre'),
                    Forms\Components\FileUpload::make('images.header_background')->label('Image de fond')->image()->disk('public')->directory('pages'),
                ]),
            ],
            'catalogue-visiteur','produits' => [
    Forms\Components\Tabs::make('Contenu')->tabs([
        Forms\Components\Tabs\Tab::make('En-tête')->schema([
            Forms\Components\TextInput::make('titres.header_title')->label('Titre Principal'),
            Forms\Components\Textarea::make('contenus.header_subtitle')->label('Sous-titre'),
            Forms\Components\FileUpload::make('images.header_background')
                ->label('Image de fond')
                ->image()
                ->disk('public')
                ->directory('pages'),
        ]),
        Forms\Components\Tabs\Tab::make('Pourquoi Choisir SOMACIF')->schema([
            Forms\Components\TextInput::make('titres.why_choose_title')->label('Titre de la section'),
            Forms\Components\Textarea::make('contenus.why_choose_text')->label('Texte de présentation'),
            Forms\Components\Repeater::make('contenus.avantages')->schema([
                Forms\Components\TextInput::make('icon')->label('Icône FontAwesome (ex: fas fa-fish)')->required(),
                Forms\Components\TextInput::make('titre')->label('Titre de l\'avantage')->required(),
                Forms\Components\Textarea::make('description')->label('Description')->required(),
            ])->columns(3),
        ]),
        Forms\Components\Tabs\Tab::make('Comment Commander')->schema([
            Forms\Components\TextInput::make('titres.how_to_order_title')->label('Titre de la section'),
            Forms\Components\Textarea::make('contenus.how_to_order_text')->label('Texte d\'introduction'),
            Forms\Components\Repeater::make('contenus.etapes_commande')->schema([
                Forms\Components\TextInput::make('titre')->label('Titre de l\'étape')->required(),
                Forms\Components\Textarea::make('description')->label('Description')->required(),
            ])->columns(2),
        ]),
        Forms\Components\Tabs\Tab::make('Connexion Rapide')->schema([
            Forms\Components\TextInput::make('titres.login_title')->label('Titre de la section'),
            Forms\Components\Textarea::make('contenus.login_subtitle')->label('Sous-titre'),
        ]),
        Forms\Components\Tabs\Tab::make('Devenir Partenaire')->schema([
            Forms\Components\TextInput::make('titres.become_partner_title')->label('Titre de la section'),
            Forms\Components\Textarea::make('contenus.become_partner_text')->label('Texte d\'invitation'),
        ]),
        Forms\Components\Tabs\Tab::make('Slider')->schema([
            Forms\Components\Textarea::make('contenus.slider_placeholder')->label('Contenu Placeholder du Slider (Texte)'),

            // Nouveau champ : galerie d’images pour le slider
            Forms\Components\FileUpload::make('images.slider_gallery')
                ->label('Images du Slider de Produits')
                ->multiple()
                ->image()
                ->disk('public')
                ->directory('pages')
                ->reorderable()
                ->downloadable()
                ->openable()
                ->columnSpanFull(),
        ]),
    ]),
],

            
            default => [],
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')->label('Page'),
                Tables\Columns\TextColumn::make('updated_at')->label('Dernière modification')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPages::route('/'), 'edit' => Pages\EditPage::route('/{record}/edit')];
    }
}