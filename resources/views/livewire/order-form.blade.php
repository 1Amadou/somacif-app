<div class="bg-slate-900/50 p-8 rounded-lg border border-slate-800">

    @if($step === 1)
        {{-- ÉTAPE 1 : IDENTIFICATION --}}
        <h3 class="text-3xl font-teko uppercase text-white mb-4">Portail de Commande Partenaire</h3>
        <p class="text-slate-400 mb-6">Veuillez entrer votre identifiant unique SOMACIF pour commencer.</p>
        <form wire:submit.prevent="checkIdentifiant">
            <div class="space-y-4">
                <div>
                    <label for="identifiant_unique" class="form-label">Votre Identifiant Unique</label>
                    <input type="text" id="identifiant_unique" wire:model="identifiant_unique" class="form-input">
                    @error('identifiant_unique') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="text-right">
                    <button type="submit" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-3 px-8 rounded-sm">
                        <span wire:loading.remove>Vérifier</span>
                        <span wire:loading>Vérification...</span>
                    </button>
                </div>
            </div>
        </form>
        <div class="text-center mt-6">
            <a href="{{ route('devenir-partenaire') }}" class="text-sm text-slate-400 hover:text-white underline">
                Vous n'êtes pas encore partenaire ? Cliquez ici pour faire votre demande.
            </a>
        </div>

    @elseif($step === 2)
        {{-- ÉTAPE 2 : VÉRIFICATION SMS --}}
        <h3 class="text-3xl font-teko uppercase text-white mb-4">Vérification de Sécurité</h3>
        <p class="text-slate-400 mb-6">Nous avons envoyé un code à 6 chiffres par SMS au numéro de téléphone associé à ce compte. Veuillez le saisir ci-dessous.</p>
        <form wire:submit.prevent="verifyCode">
            <div class="space-y-4">
                <div>
                    <label for="verification_code" class="form-label">Code de Vérification</label>
                    <input type="text" id="verification_code" wire:model="verification_code" class="form-input text-center text-2xl tracking-[1em]">
                    @error('verification_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="text-right">
                    <button type="submit" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-3 px-8 rounded-sm">
                        <span wire:loading.remove>Confirmer</span>
                        <span wire:loading>Confirmation...</span>
                    </button>
                </div>
            </div>
        </form>
    
    @elseif($step === 3)
        {{-- ÉTAPE 3 : ACCÈS AU FORMULAIRE DE COMMANDE --}}
        <div class="text-center">
            <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
            <h3 class="text-3xl font-teko uppercase text-white mb-2">Authentification Réussie !</h3>
            <p class="text-slate-300 text-lg">Bienvenue, <span class="font-bold text-white">{{ $client->nom }}</span>.</p>
            <p class="text-slate-400 mt-4">Vous allez être redirigé vers le catalogue personnalisé pour passer votre commande.</p>
            {{-- Ici, nous construirons le véritable formulaire de commande --}}
        </div>
    
    @elseif($step === 4)
        {{-- ÉTAPE 4 : STATUT EN ATTENTE --}}
        <div class="text-center">
            <i class="fas fa-clock text-amber-400 text-6xl mb-4"></i>
            <h3 class="text-3xl font-teko uppercase text-white mb-2">Dossier en cours d'examen</h3>
            <p class="text-slate-300 text-lg">Bonjour, <span class="font-bold text-white">{{ $client->nom }}</span>.</p>
            <p class="text-slate-400 mt-4 max-w-md mx-auto">Votre demande de partenariat est en cours de traitement par notre équipe commerciale. Nous vous contacterons par téléphone très prochainement. Merci de votre patience.</p>
            <button wire:click="logout" class="mt-6 text-sm text-slate-400 hover:text-white underline">Se déconnecter</button>
        </div>
    
    @elseif($step === 5)
        {{-- ÉTAPE 5 : STATUT REJETÉ --}}
        <div class="text-center">
            <i class="fas fa-times-circle text-red-500 text-6xl mb-4"></i>
            <h3 class="text-3xl font-teko uppercase text-white mb-2">Demande non retenue</h3>
            <p class="text-slate-300 text-lg">Bonjour, <span class="font-bold text-white">{{ $client->nom }}</span>.</p>
            <p class="text-slate-400 mt-4 max-w-md mx-auto">Après examen de votre dossier, nous ne sommes malheureusement pas en mesure de donner une suite favorable à votre demande pour le moment. Pour plus d'informations, veuillez contacter notre service commercial.</p>
            <button wire:click="logout" class="mt-6 text-sm text-slate-400 hover:text-white underline">Fermer</button>
        </div>
    @endif

    {{-- AFFICHAGE DES MESSAGES --}}
    @if($message)
        <div class="mt-4 text-center @if(str_contains($message, 'incorrect') || str_contains($message, 'inconnu')) text-red-400 @else text-green-400 @endif">
            {{ $message }}
        </div>
    @endif
</div>