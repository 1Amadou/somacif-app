<x-layouts.livreur>
    <div class="py-24 flex items-center justify-center">
        <div class="bg-dark-card border border-border-dark rounded-lg p-8 w-full max-w-md">
            <h1 class="text-4xl font-teko uppercase text-white text-center mb-6">Portail Livreur</h1>
            <form method="POST" action="{{ route('livreur.login') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="phone" class="form-label">Numéro de téléphone</label>
                    <input type="tel" id="phone" name="phone" class="form-input">
                    @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-input">
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase py-3 rounded-sm">
                        Se Connecter
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.livreur>