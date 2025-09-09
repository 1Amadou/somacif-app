<x-mail::message>
# Réponse à votre demande

Bonjour,

Voici une réponse de notre équipe concernant votre récent message :

<x-mail::panel>
{{ $replyMessage }}
</x-mail::panel>

N'hésitez pas à nous recontacter si vous avez d'autres questions.

Cordialement,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>