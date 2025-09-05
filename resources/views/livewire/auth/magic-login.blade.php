<div class="py-24">
    <div class="container mx-auto px-6 max-w-lg">
        <div class="mb-12 text-center">
            <h1 class="text-5xl font-teko uppercase text-white">Connexion</h1>
            <p class="text-slate-400 mt-2">Accédez à votre espace partenaire SOMACIF.</p>
        </div>

        <div class="bg-dark-card border border-border-dark rounded-lg p-8 space-y-6">

            @if (!$codeSent)
                {{-- Étape 1 : Demander l'identifiant --}}
                <form wire:submit="sendCode" class="space-y-6">
                    <div>
                        <p class="text-center text-slate-400">Entrez votre email ou identifiant SOMACIF pour recevoir un code de connexion.</p>
                    </div>
                    <div>
                        <label for="identifier" class="text-white text-sm sr-only">Email ou Identifiant</label>
                        <input type="text" wire:model="identifier" id="identifier" class="form-input w-full" placeholder="Votre email ou identifiant..." autofocus>
                        @error('identifier') <span class="text-brand-red text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <span wire:loading.remove wire:target="sendCode">Envoyer le code</span>
                        <span wire:loading wire:target="sendCode">Envoi en cours...</span>
                    </button>
                </form>
            @else
                {{-- Étape 2 : Demander le code --}}
                <form wire:submit="login" class="space-y-6" x-data="{}" x-init="$wire.tick()" wire:poll.1s="tick">
                    <div>
                         <p class="text-center text-slate-400">Un code de vérification a été envoyé à <strong>{{ $identifier }}</strong>. Entrez-le ci-dessous.</p>
                    </div>
                    <div>
                        <label for="code" class="text-white text-sm sr-only">Code de vérification</label>
                        <input type="text" wire:model="code" id="code" class="form-input w-full text-center tracking-widest" placeholder="_ _ _ _ _ _" autofocus>
                        @error('code') <span class="text-brand-red text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <span wire:loading.remove wire:target="login">Se connecter</span>
                        <span wire:loading wire:target="login">Vérification...</span>
                    </button>

                    <div class="flex items-center justify-between pt-4 border-t border-border-dark">
                        <p class="text-sm text-slate-400">
                            @if ($cooldown > 0)
                                Renvoyer le code dans <span class="font-bold text-white">{{ $cooldown }}s</span>
                            @else
                                Code non reçu ?
                            @endif
                        </p>
                        
                        <button wire:click.prevent="sendCode" wire:loading.attr="disabled" @if ($cooldown > 0) disabled @endif
                                class="text-sm font-semibold {{ $cooldown > 0 ? 'text-slate-500 cursor-not-allowed' : 'text-brand-red hover:underline' }}">
                            Renvoyer
                        </button>
                    </div>

                    <button wire:click.prevent="$set('codeSent', false)" class="text-center w-full text-sm text-slate-400 hover:underline">
                        &larr; Utiliser un autre identifiant
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>