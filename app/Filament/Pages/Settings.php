<?php
namespace App\Filament\Pages;

use App\Mail\TestSmtpMail;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Notifications')->schema([
                    TextInput::make('admin_notification_email')->label('Email pour les notifications admin')->email(),
                ]),
                Section::make('Twilio (SMS)')->schema([
                    Toggle::make('twilio_sandbox_mode')->label('Activer le mode Bac à Sable (pas d\'envoi réel)')->default(true),
                    TextInput::make('twilio_sid')->label('Twilio SID'),
                    TextInput::make('twilio_auth_token')->label('Twilio Auth Token')->password(),
                    TextInput::make('twilio_from')->label('Numéro de téléphone Twilio'),
                ]),
                Section::make('SMTP (Envoi d\'emails)')->schema([
                    TextInput::make('mail_host')->label('Hôte SMTP'),
                    TextInput::make('mail_port')->label('Port SMTP'),
                    TextInput::make('mail_username')->label('Nom d\'utilisateur SMTP'),
                    TextInput::make('mail_password')->label('Mot de passe SMTP')->password(),
                    TextInput::make('mail_from_address')->label('Adresse email d\'expédition'),
                    TextInput::make('mail_from_name')->label('Nom de l\'expéditeur'),
                ]),
            ])
            ->statePath('data');
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
        if (str_starts_with($key, 'mail_')) return 'smtp';
        return 'general';
    }
}