<div>
    @if (!$codeSent)
        {{-- Étape 1 : Demander l'identifiant --}}
        <form wire:submit="sendCode">
            <h2 class="text-2xl font-bold text-center">Connexion</h2>
            <p class="text-center text-gray-600 mt-2">Entrez votre email ou identifiant SOMACIF</p>

            <div class="mt-6">
                <label for="identifier">Email ou Identifiant</label>
                <input type="text" wire:model="identifier" id="identifier" class="form-input w-full" autofocus>
                @error('identifier') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full mt-6">
                <span wire:loading.remove>Envoyer le code de connexion</span>
                <span wire:loading>Envoi en cours...</span>
            </button>
        </form>
    @else
        {{-- Étape 2 : Demander le code --}}
        <form wire:submit="verifyCode">
            <h2 class="text-2xl font-bold text-center">Vérifiez votre email</h2>
            <p class="text-center text-gray-600 mt-2">Nous avons envoyé un code à <strong>{{ $identifier }}</strong>.</p>
            
            <div class="mt-6">
                <label for="code">Code de vérification</label>
                <input type="text" wire:model="code" id="code" class="form-input w-full text-center tracking-widest" autofocus>
                @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full mt-6">
                <span wire:loading.remove>Se connecter</span>
                <span wire:loading>Vérification...</span>
            </button>
            <button wire:click.prevent="$set('codeSent', false)" class="text-center w-full mt-4 text-sm text-gray-600 hover:underline">
                Retour
            </button>
        </form>
    @endif
</div>