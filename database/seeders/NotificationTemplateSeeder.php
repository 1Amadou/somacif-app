<?php
namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        NotificationTemplate::updateOrCreate(['key' => 'client.verify.sms'], [
            'name' => 'Client - Code de Vérification (SMS)',
            'channel' => 'sms',
            'body' => 'Votre code de vérification SOMACIF est : {code}',
            'description' => 'Variables disponibles : {code}, {client_name}',
        ]);
        NotificationTemplate::updateOrCreate(['key' => 'admin.new_order'], [
            'name' => 'Admin - Notification Nouvelle Commande',
            'channel' => 'mail',
            'subject' => 'Nouvelle Commande Reçue : {order_number}',
            'body' => "Bonjour,\nUne nouvelle commande a été passée.\n\nClient: {client_name}\nMontant: {order_total} FCFA\n\nCliquez sur le lien ci-dessous pour voir la commande.",
            'description' => 'Variables disponibles : {order_number}, {client_name}, {order_total}',
        ]);
         NotificationTemplate::updateOrCreate(['key' => 'admin.new_partner_request'], [
            'name' => 'Admin - Nouvelle Demande de Partenariat',
            'channel' => 'mail',
            'subject' => 'Nouvelle Demande de Partenariat : {partner_name}',
            'body' => "Bonjour,\nUne nouvelle demande de partenariat a été soumise.\n\nNom: {partner_name}\nTéléphone: {partner_phone}",
            'description' => 'Variables disponibles : {partner_name}, {partner_phone}',
        ]);
    }
}