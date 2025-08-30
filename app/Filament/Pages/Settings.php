<?php

namespace App\Filament\Pages;

use App\Mail\TestSmtpMail;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Paramètres';
    protected static ?string $navigationGroup = 'Administration';

    public ?array $data = [];

    public function mount(): void
    {
        // On charge tous les paramètres existants dans le formulaire
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Configuration')->tabs([
                
                Tabs\Tab::make('Notifications Générales')->schema([
                    TextInput::make('admin_notification_email')
                        ->label('Email pour recevoir les notifications')
                        ->helperText('L\'adresse email principale qui recevra les alertes (nouvelle commande, etc.).')
                        ->required()->email(),
                    Toggle::make('mail_notifications_active')->label('Activer les notifications par Email')->default(true),
                    Toggle::make('sms_notifications_active')->label('Activer les notifications par SMS')->default(true),
                ]),

                Tabs\Tab::make('Configuration Email (SMTP)')->schema([
                    Section::make('Paramètres du Serveur')->schema([
                        TextInput::make('mail_host')->label('Adresse du serveur SMTP')->required(),
                        TextInput::make('mail_port')->label('Port SMTP')->required(),
                        TextInput::make('mail_username')->label('Nom d\'utilisateur SMTP')->required(),
                        TextInput::make('mail_password')->label('Mot de passe SMTP')->password()->required(),
                        Select::make('mail_encryption')->label('Type d\'encryption')->options(['tls' => 'TLS', 'ssl' => 'SSL'])->required(),
                    ])->columns(2),
                    Section::make('Paramètres d\'Expédition')->schema([
                        TextInput::make('mail_from_address')->label('Adresse d\'expédition')->email()->required(),
                        TextInput::make('mail_from_name')->label('Nom de l\'expéditeur')->required(),
                    ])->columns(2),
                ]),

                Tabs\Tab::make('Configuration SMS')->schema([
                    Select::make('active_sms_provider')
                        ->label('Fournisseur SMS Actif')
                        ->options(['twilio' => 'Twilio', 'nexmo' => 'Nexmo (Vonage)', 'fast2' => 'Fast2'])
                        ->helperText('Sélectionnez le service que vous utiliserez pour envoyer les SMS.')
                        ->live(), // Indispensable pour l'affichage conditionnel

                    // AMÉLIORATION : Les sections de configuration n'apparaissent que si le fournisseur correspondant est sélectionné.
                    Section::make('Configuration Twilio')
                        ->visible(fn (Get $get): bool => $get('active_sms_provider') === 'twilio')
                        ->schema([
                            TextInput::make('twilio_sid')->label('Twilio Account SID'),
                            TextInput::make('twilio_auth_token')->label('Twilio Auth Token')->password(),
                            TextInput::make('twilio_from')->label('Numéro d\'expédition Twilio'),
                        ]),
                    
                    Section::make('Configuration Nexmo (Vonage)')
                        ->visible(fn (Get $get): bool => $get('active_sms_provider') === 'nexmo')
                        ->schema([
                            TextInput::make('nexmo_key')->label('Nexmo API Key'),
                            TextInput::make('nexmo_secret')->label('Nexmo API Secret')->password(),
                        ]),
                        
                    Section::make('Configuration Fast2')
                        ->visible(fn (Get $get): bool => $get('active_sms_provider') === 'fast2')
                        ->schema([
                            TextInput::make('fast2_sender_id')->label('Fast2 Sender ID'),
                            TextInput::make('fast2_auth_key')->label('Fast2 Auth Key')->password(),
                        ]),
                ]),

            ])
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            // 'value' peut être null, on s'assure de sauvegarder une chaîne vide dans ce cas.
            Setting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
        }
        Notification::make()->title('Paramètres sauvegardés !')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Sauvegarder')->submit('save'),
            Action::make('testMail')
                ->label('Envoyer un email de test')
                ->action('sendTestMail')
                ->color('gray')
                ->requiresConfirmation(),
        ];
    }

    public function sendTestMail(): void
    {
        $recipient = $this->form->getState()['admin_notification_email'] ?? null;
        if (!$recipient) {
            Notification::make()->title('Erreur')->body('Veuillez d\'abord définir et sauvegarder un email pour les notifications.')->danger()->send();
            return;
        }
        try {
            // Note: Pour que ce test fonctionne, les paramètres SMTP doivent être
            // appliqués dynamiquement à la configuration de Laravel.
            // Cela se fait généralement via un ServiceProvider.
            Mail::to($recipient)->send(new TestSmtpMail());
            Notification::make()->title('Email de test envoyé !')->body("Un email de test a été envoyé à {$recipient}.")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Échec de l\'envoi')->body('Vérifiez vos paramètres SMTP. Erreur : ' . $e->getMessage())->danger()->send();
        }
    }
}