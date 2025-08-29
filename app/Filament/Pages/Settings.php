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
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Configuration')->tabs([
                
                Tabs\Tab::make('Notifications Générales')->schema([
                    TextInput::make('admin_notification_email')->label('Email pour recevoir les notifications')->required()->email(),
                    Toggle::make('mail_notifications_active')->label('Activer les notifications par Email')->default(true),
                    Toggle::make('sms_notifications_active')->label('Activer les notifications par SMS')->default(true),
                ]),

                Tabs\Tab::make('Configuration Email (SMTP)')->schema([
                    Section::make('Paramètres du Serveur')->schema([
                        TextInput::make('mail_host')->label('SMTP Server Address')->required(),
                        TextInput::make('mail_port')->label('SMTP Port')->required(),
                        TextInput::make('mail_username')->label('SMTP Username')->required(),
                        TextInput::make('mail_password')->label('SMTP Password')->password()->required(),
                        Select::make('mail_encryption')->label('Encryption Type')->options(['tls' => 'TLS', 'ssl' => 'SSL'])->required(),
                    ])->columns(2),
                    Section::make('Paramètres d\'Expédition')->schema([
                        TextInput::make('mail_from_address')->label('Mail From Address')->email()->required(),
                        TextInput::make('mail_from_name')->label('Mail From Name')->required(),
                    ])->columns(2),
                ]),

                Tabs\Tab::make('Configuration SMS')->schema([
                    Select::make('active_sms_provider')
                        ->label('Fournisseur SMS Actif')
                        ->options(['twilio' => 'Twilio', 'nexmo' => 'Nexmo', 'fast2' => 'Fast2'])
                        ->default('twilio'),
                    Toggle::make('sms_sandbox_mode')->label('Activer le mode Bac à Sable (pas d\'envoi réel)')->default(true),

                    Section::make('Twilio')->schema([
                        TextInput::make('twilio_sid')->label('Twilio SMS SID'),
                        TextInput::make('twilio_auth_token')->label('Twilio SMS Auth Token')->password(),
                        TextInput::make('twilio_from')->label('Twilio SMS Number'),
                    ]),
                    Section::make('Nexmo (Vonage)')->schema([
                        TextInput::make('nexmo_key')->label('Nexmo SMS Key'),
                        TextInput::make('nexmo_secret')->label('Nexmo SMS Secret Key')->password(),
                    ]),
                    Section::make('Fast2')->schema([
                        TextInput::make('fast2_sender_id')->label('Fast2 SMS Sender ID'),
                        TextInput::make('fast2_entity_id')->label('Fast2 SMS Entity ID'),
                        TextInput::make('fast2_language')->label('Fast2 SMS Language'),
                        TextInput::make('fast2_auth_key')->label('Fast2 SMS Auth Key')->password(),
                    ]),
                ]),

            ])
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $this->getGroupForKey($key)]);
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