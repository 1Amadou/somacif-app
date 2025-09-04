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
use Illuminate\Support\Facades\Config; // Ajouté

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
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Configuration')->tabs([
                Tabs\Tab::make('Notifications Générales')->schema([
                    TextInput::make('admin_notification_email')->label('Email pour recevoir les notifications')->helperText('L\'adresse email principale qui recevra les alertes (nouvelle commande, etc.).')->required()->email(),
                    Toggle::make('mail_notifications_active')->label('Activer les notifications par Email')->default(true),
                    Toggle::make('sms_notifications_active')->label('Activer les notifications par SMS')->default(true),
                ]),
                Tabs\Tab::make('Configuration Email (SMTP)')->schema([
                    Section::make('Paramètres du Serveur')->schema([
                        TextInput::make('mail_host')->label('Adresse du serveur SMTP')->required(),
                        TextInput::make('mail_port')->label('Port SMTP')->required()->numeric(),
                        TextInput::make('mail_username')->label('Nom d\'utilisateur SMTP')->required(),
                        TextInput::make('mail_password')->label('Mot de passe SMTP')->password()->required()->dehydrateStateUsing(fn ($state) => base64_encode($state)),
                        Select::make('mail_encryption')->label('Type d\'encryption')->options(['tls' => 'TLS', 'ssl' => 'SSL'])->required(),
                    ])->columns(2),
                    Section::make('Paramètres d\'Expédition')->schema([
                        TextInput::make('mail_from_address')->label('Adresse d\'expédition')->email()->required(),
                        TextInput::make('mail_from_name')->label('Nom de l\'expéditeur')->required(),
                    ])->columns(2),
                ]),
                Tabs\Tab::make('Configuration SMS')->schema([
                    Select::make('active_sms_provider')->label('Fournisseur SMS Actif')
                        ->options(['twilio' => 'Twilio', 'nexmo' => 'Nexmo (Vonage)', 'fast2' => 'Fast2'])
                        ->helperText('Sélectionnez le service que vous utiliserez pour envoyer les SMS.')
                        ->live(),
                    Section::make('Configuration Twilio')->visible(fn (Get $get): bool => $get('active_sms_provider') === 'twilio')->schema([
                        TextInput::make('twilio_sid')->label('Twilio Account SID')->required(),
                        TextInput::make('twilio_auth_token')->label('Twilio Auth Token')->password()->required()->dehydrateStateUsing(fn ($state) => base64_encode($state)),
                        TextInput::make('twilio_from')->label('Numéro d\'expédition Twilio')->required(),
                    ]),
                    Section::make('Configuration Nexmo (Vonage)')->visible(fn (Get $get): bool => $get('active_sms_provider') === 'nexmo')->schema([
                        TextInput::make('nexmo_key')->label('Nexmo API Key')->required(),
                        TextInput::make('nexmo_secret')->label('Nexmo API Secret')->password()->required()->dehydrateStateUsing(fn ($state) => base64_encode($state)),
                    ]),
                    Section::make('Configuration Fast2')->visible(fn (Get $get): bool => $get('active_sms_provider') === 'fast2')->schema([
                        TextInput::make('fast2_sender_id')->label('Fast2 Sender ID')->required(),
                        TextInput::make('fast2_auth_key')->label('Fast2 Auth Key')->password()->required()->dehydrateStateUsing(fn ($state) => base64_encode($state)),
                    ]),
                ]),
            ])
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value ?? '',
                    'group' => $this->getGroupForKey($key),
                ]
            );
        }
        Notification::make()->title('Paramètres sauvegardés !')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Sauvegarder')->submit('save'),
            Action::make('testMail')->label('Envoyer un email de test')->action('sendTestMail')->color('gray')->requiresConfirmation(),
        ];
    }

    public function sendTestMail(): void
    {
        $recipient = $this->form->getState()['admin_notification_email'] ?? null;
        if (!$recipient) {
            Notification::make()->title('Erreur')->body('Veuillez d\'abord définir et sauvegarder un email pour les notifications.')->danger()->send();
            return;
        }

        // IMPORTANT : Re-configurer le mailer avec les données du formulaire avant d'envoyer
        $formData = $this->form->getState();
        Config::set('mail.mailers.smtp.host', $formData['mail_host']);
        Config::set('mail.mailers.smtp.port', $formData['mail_port']);
        Config::set('mail.mailers.smtp.username', $formData['mail_username']);
        Config::set('mail.mailers.smtp.password', base64_decode($formData['mail_password']));
        Config::set('mail.from.address', $formData['mail_from_address']);
        Config::set('mail.from.name', $formData['mail_from_name']);
        
        try {
            Mail::to($recipient)->send(new TestSmtpMail());
            Notification::make()->title('Email de test envoyé !')->body("Un email de test a été envoyé à {$recipient}.")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Échec de l\'envoi')->body('Vérifiez vos paramètres SMTP. Erreur : ' . $e->getMessage())->danger()->send();
        }
    }

    private function getGroupForKey(string $key): string
    {
        if (str_starts_with($key, 'twilio_')) return 'twilio';
        if (str_starts_with($key, 'nexmo_')) return 'nexmo';
        if (str_starts_with($key, 'fast2_')) return 'fast2';
        if (str_starts_with($key, 'mail_')) return 'smtp';
        return 'general';
    }
}