    <div>
        {{-- Formulaire de connexion --}}
        <div class="py-24">
            <div class="container mx-auto px-6 max-w-lg">
                <div class="mb-12 text-center">
                    <h1 class="text-5xl font-teko uppercase text-white">Connexion</h1>
                    <p class="text-slate-400 mt-2">Connectez-vous à votre espace client ou livreur.</p>
                </div>

                {{-- Afficher les notifications de session (facultatif, si vous en avez) --}}
                @if (session()->has('success'))
                    <div class="bg-green-900/50 border border-green-700 text-green-300 p-4 rounded-lg text-center mb-8">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="bg-dark-card border border-border-dark rounded-lg p-8 space-y-6">

                    @if (!$codeSent)
                        {{-- Étape 1 : Demander l'identifiant --}}
                        <form wire:submit="sendCode">
                            <p class="text-center text-slate-400">Entrez votre email ou identifiant SOMACIF pour recevoir un code de connexion.</p>

                            <div class="mt-6">
                                <label for="identifier" class="text-white text-sm">Email ou Identifiant</label>
                                <input type="text" wire:model="identifier" id="identifier" class="form-input w-full" autofocus>
                                @error('identifier') <span class="text-brand-red text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-full mt-6">
                                <span wire:loading.remove wire:target="sendCode">Envoyer le code</span>
                                <span wire:loading wire:target="sendCode">Envoi en cours...</span>
                            </button>
                        </form>
                    @else
                        {{-- Étape 2 : Demander le code --}}
                        <form wire:submit="verifyCode">
                            <p class="text-center text-slate-400">Un code de vérification a été envoyé à <strong>{{ $identifier }}</strong>. Entrez-le ci-dessous.</p>
                            
                            <div class="mt-6">
                                <label for="code" class="text-white text-sm">Code de vérification</label>
                                <input type="text" wire:model="code" id="code" class="form-input w-full text-center tracking-widest" autofocus>
                                @error('code') <span class="text-brand-red text-sm mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-full mt-6">
                                <span wire:loading.remove wire:target="verifyCode">Se connecter</span>
                                <span wire:loading wire:target="verifyCode">Vérification...</span>
                            </button>

                            <div class="flex items-center justify-between mt-4">
                                {{-- Compte à rebours intelligent --}}
                                <p class="text-sm text-slate-400">
                                    @if ($cooldown > 0)
                                        Renvoyer le code dans <span class="font-bold text-white">{{ $cooldown }}s</span>
                                    @else
                                        Code non reçu ?
                                    @endif
                                </p>
                                
                                {{-- Bouton pour renvoyer le code (actif après le cooldown) --}}
                                <button wire:click.prevent="sendCode" 
                                        wire:loading.attr="disabled" 
                                        @if ($cooldown > 0) disabled @endif
                                        class="text-sm font-semibold {{ $cooldown > 0 ? 'text-slate-500 cursor-not-allowed' : 'text-brand-red hover:underline' }}">
                                    Renvoyer le code
                                </button>
                            </div>
                            
                            <button wire:click.prevent="$set('codeSent', false)" class="text-center w-full mt-4 text-sm text-slate-400 hover:underline">
                                &larr; Retour à l'identifiant
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>