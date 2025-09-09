<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use App\Mail\ContactReplyMail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model; // <-- LA CORRECTION EST ICI : On importe la bonne classe

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Contenu & Site Web';
    protected static ?string $label = 'Message de Contact';
    protected static ?string $pluralLabel = 'Messages de Contact';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nom')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('subject')->label('Sujet')->searchable()->limit(30),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')->sortable()
                    ->colors([
                        'warning' => 'new',
                        'primary' => 'read',
                        'success' => 'replied',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Nouveau',
                        'read' => 'Lu',
                        'replied' => 'Répondu',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Reçu le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'Nouveau',
                        'read' => 'Lu',
                        'replied' => 'Répondu',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('reply')
                    ->label('Répondre')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->form([
                        Forms\Components\RichEditor::make('reply_message')
                            ->label('Votre Réponse')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'strike', 'link', 'bulletList', 'orderedList',
                            ]),
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Pièce jointe (optionnel)')
                            ->disk('public')
                            ->directory('contact-replies')
                            ->image()
                            ->imageEditor(),
                    ])
                    ->action(function (ContactMessage $record, array $data) {
                        $attachmentPath = null;
                        if (!empty($data['attachment'])) {
                            $attachmentPath = Storage::disk('public')->path($data['attachment']);
                        }

                        try {
                            Mail::to($record->email)->send(new ContactReplyMail($data['reply_message'], $attachmentPath));
                            
                            $record->update([
                                'status' => 'replied',
                                'reply_message' => $data['reply_message'],
                                'replied_at' => now(),
                                'replied_by' => auth()->id(),
                            ]);
                            Notification::make()->title('Réponse envoyée avec succès !')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Erreur lors de l\'envoi')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (ContactMessage $record) => $record->status !== 'replied'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Détails du Message')
                ->schema([
                    Infolists\Components\TextEntry::make('name')->label('Nom'),
                    Infolists\Components\TextEntry::make('email')->label('Email'),
                    Infolists\Components\TextEntry::make('phone')->label('Téléphone'),
                    Infolists\Components\TextEntry::make('subject')->label('Sujet'),
                    Infolists\Components\TextEntry::make('message')->columnSpanFull()->markdown(),
                ])->columns(2),
            
            Infolists\Components\Section::make('Réponse de l\'Administrateur')
                ->schema([
                    Infolists\Components\TextEntry::make('replier.name')->label('Répondu par'),
                    Infolists\Components\TextEntry::make('replied_at')->label('Le')->dateTime('d/m/Y H:i'),
                    Infolists\Components\TextEntry::make('reply_message')->label('Message de Réponse')->columnSpanFull()->markdown(),
                ])
                ->columns(2)
                ->visible(fn (ContactMessage $record) => $record->status === 'replied'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
    
    public static function canCreate(): bool { return false; }
    
    // --- LA CORRECTION EST ICI : On utilise la classe Model que nous avons importée ---
    public static function canEdit(Model $record): bool { return false; }
}