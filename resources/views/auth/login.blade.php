<x-layouts.app>
    @slot('metaTitle', 'Connexion - SOMACIF')

    <div class="py-24" x-data="{ tab: 'partenaire' }">
        <div class="container mx-auto px-6 max-w-lg">
            <div class="bg-dark-card border border-border-dark rounded-lg">
                <div class="flex border-b border-border-dark">
                    <button @click="tab = 'partenaire'" 
                            :class="{ 'bg-primary text-white': tab === 'partenaire', 'text-slate-400': tab !== 'partenaire' }"
                            class="flex-1 py-4 font-teko text-2xl uppercase tracking-wider transition-colors">
                        Partenaire
                    </button>
                    <button @click="tab = 'livreur'"
                            :class="{ 'bg-primary text-white': tab === 'livreur', 'text-slate-400': tab !== 'livreur' }"
                            class="flex-1 py-4 font-teko text-2xl uppercase tracking-wider transition-colors border-l border-border-dark">
                        Livreur
                    </button>
                </div>

                <div class="p-8">
                    <div x-show="tab === 'partenaire'" x-cloak>
                        <h3 class="text-3xl font-teko uppercase text-white mb-4 text-center">Portail Partenaire</h3>
                        {{-- Ce formulaire envoie ses données au ClientLoginController --}}
                        <form method="POST" action="{{ route('client.login.send_code') }}" class="space-y-6">
                            @csrf
                            <div>
                                <label for="identifiant_unique" class="form-label">Votre Identifiant Unique</label>
                                <input type="text" id="identifiant_unique" name="identifiant_unique" class="form-input" required value="{{ old('identifiant_unique') }}">
                                @error('identifiant_unique')<span class="text-red-500 text-xs mt-1">{{ $message }}</span>@enderror
                            </div>
                            <div class="pt-4">
                                <button type="submit" class="w-full btn btn-primary py-3">Recevoir le code SMS</button>
                            </div>
                        </form>
                        <p class="text-center text-sm text-slate-400 mt-6">
                            Pas encore partenaire ? 
                            <a href="{{ route('devenir-partenaire') }}" class="font-semibold text-primary hover:underline">Faites votre demande ici.</a>
                        </p>
                    </div>

                    <div x-show="tab === 'livreur'" x-cloak>
                        <h3 class="text-3xl font-teko uppercase text-white mb-4 text-center">Portail Livreur</h3>
                        {{-- Ce formulaire envoie ses données au LivreurLoginController --}}
                         <form method="POST" action="{{ route('livreur.login') }}" class="space-y-6">
                            @csrf
                            <div>
                                <label for="phone" class="form-label">Numéro de téléphone</label>
                                <input type="tel" id="phone" name="phone" class="form-input" required>
                                @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" id="password" name="password" class="form-input" required>
                            </div>
                            <div class="pt-4">
                                <button type="submit" class="w-full btn btn-primary py-3">Se Connecter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>