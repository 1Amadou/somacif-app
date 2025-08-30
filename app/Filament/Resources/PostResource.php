<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Contenu & Site Web';
    protected static ?string $label = 'Article';
    protected static ?string $pluralLabel = 'Articles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Colonne principale pour le contenu
                Group::make()
                    ->schema([
                        Section::make('Contenu Principal')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre de l\'article')
                                    ->required()->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->required()->maxLength(255)->unique(ignoreRecord: true),

                                RichEditor::make('content')
                                    ->label('Corps de l\'article')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Section::make('SEO / Référencement')
                            ->description('Ces informations aident les moteurs de recherche comme Google à mieux comprendre votre contenu.')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('Meta Titre')
                                    ->helperText('Si laissé vide, le titre de l\'article sera utilisé.'),
                                TextInput::make('meta_description')
                                    ->label('Meta Description')
                                    ->helperText('Une courte description (environ 160 caractères) pour les résultats de recherche.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // Colonne latérale pour les métadonnées
                Group::make()
                    ->schema([
                        Section::make('Statut & Publication')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'draft' => 'Brouillon',
                                        'published' => 'Publié',
                                    ])
                                    ->required()->default('draft'),
                                
                                DateTimePicker::make('published_at')
                                    ->label('Date de publication')
                                    ->default(now()),
                            ]),

                        Section::make('Organisation')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Image de couverture')
                                    ->image()->imageEditor(),
                                
                                    Select::make('category_id')->relationship('category', 'nom', fn (Builder $query) => $query->orderBy('nom'))->searchable()->required()->label('Catégorie'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Image'),
                TextColumn::make('title')->label('Titre')->searchable()->sortable(),
                TextColumn::make('category.nom')->label('Catégorie')->searchable()->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),
                TextColumn::make('published_at')->label('Publié le')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'nom'),
                SelectFilter::make('status')->options(['draft' => 'Brouillon', 'published' => 'Publié']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}