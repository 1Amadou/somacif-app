<div class="bg-slate-900/50 p-8 rounded-lg border border-slate-800">

    @if($step === 1)
        <h3 class="text-3xl font-teko uppercase text-white mb-4">Portail de Commande Partenaire</h3>
        <p class="text-slate-400 mb-6">Veuillez entrer votre identifiant unique SOMACIF pour commencer.</p>
        
        <form wire:submit.prevent="checkIdentifiant">
            <div class="space-y-4">
                <div>
                    <label for="identifiant_unique" class="form-label">Votre Identifiant Unique</label>
                    <input type="text" id="identifiant_unique" wire:model="identifiant_unique" class="form-input" placeholder="Ex: CLI-GROS-DIARRA">
                    @error('identifiant_unique') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="text-right">
                    <button type="submit" wire:loading.attr="disabled" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-3 px-8 rounded-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="checkIdentifiant">Vérifier</span>
                        <span wire:loading wire:target="checkIdentifiant">Vérification...</span>
                    </button>
                </div>
            </div>
        </form>
        <div class="text-center mt-6">
            <a href="{{ route('devenir-partenaire') }}" class="text-sm text-slate-400 hover:text-white underline">
                Vous n'êtes pas encore partenaire ?
            </a>
        </div>

    @elseif($step === 2)
        <h3 class="text-3xl font-teko uppercase text-white mb-4">Vérification de Sécurité</h3>
        <p class="text-slate-400 mb-6">Un code à 6 chiffres a été envoyé par SMS. Veuillez le saisir ci-dessous.</p>
        <form wire:submit.prevent="verifyCode">
            <div class="space-y-4">
                <div>
                    <label for="verification_code" class="form-label">Code de Vérification</label>
                    <input type="text" id="verification_code" wire:model="verification_code" class="form-input text-center text-2xl tracking-[1em]">
                    @error('verification_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="text-right">
                    <button type="submit" wire:loading.attr="disabled" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-3 px-8 rounded-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="verifyCode">Confirmer</span>
                        <span wire:loading wire:target="verifyCode">Confirmation...</span>
                    </button>
                </div>
            </div>
        </form>

    @elseif($step === 4 || $step === 5)
        <div class="text-center">
            <i class="fas {{ $step === 4 ? 'fa-clock text-amber-400' : 'fa-times-circle text-red-500' }} text-6xl mb-4"></i>
            <h3 class="text-3xl font-teko uppercase text-white mb-2">{{ $step === 4 ? 'Dossier en cours d\'examen' : 'Demande non retenue' }}</h3>
            <p class="text-slate-300 text-lg">Bonjour, <span class="font-bold text-white">{{ $client->nom ?? '' }}</span>.</p>
            <p class="text-slate-400 mt-4 max-w-md mx-auto">
                @if($step === 4)
                    Votre demande de partenariat est en cours de traitement. Nous vous contacterons très prochainement.
                @else
                    Après examen, nous ne pouvons donner une suite favorable à votre demande pour le moment.
                @endif
            </p>
            <button wire:click="logout" class="mt-6 text-sm text-slate-400 hover:text-white underline">Fermer</button>
        </div>
    @endif
    
    @if($message)
        <div class="mt-4 text-center @if(str_contains($message, 'incorrect') || str_contains($message, 'inconnu')) text-red-400 @else text-green-400 @endif">
            {{ $message }}
        </div>
    @endif
</div>