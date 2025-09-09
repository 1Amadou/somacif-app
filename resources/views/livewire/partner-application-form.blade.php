<div>
    {{-- On vérifie d'abord si un client est connecté --}}
    @if ($isClientLoggedIn)
        
        {{-- Message pour les clients déjà connectés --}}
        <div class="bg-blue-900/50 border border-blue-700 text-blue-300 p-8 rounded-lg text-center">
            <i class="fas fa-info-circle text-6xl mb-4"></i>
            <h3 class="text-3xl font-teko uppercase text-white mb-2">Vous êtes déjà partenaire</h3>
            <p class="mb-6">Ce formulaire est destiné aux nouveaux candidats. Vous pouvez accéder à votre tableau de bord pour gérer vos commandes.</p>
            <a href="{{ route('client.dashboard') }}" class="btn btn-primary">Accéder à mon tableau de bord</a>
        </div>

    @else

        {{-- Logique existante pour les visiteurs (formulaire ou message de succès) --}}
        @if ($applicationSubmitted)
            <div class="bg-green-900/50 border border-green-700 text-green-300 p-8 rounded-lg text-center">
                <i class="fas fa-check-circle text-6xl mb-4"></i>
                <h3 class="text-3xl font-teko uppercase text-white mb-2">Demande Envoyée !</h3>
                <p class="mb-6">Merci pour votre intérêt. Votre demande de partenariat est en cours d'examen par notre équipe.</p>
                <p class="mb-2 text-lg">Veuillez conserver précieusement votre identifiant temporaire :</p>
                <p class="text-3xl font-teko tracking-wider bg-slate-800 text-white py-3 px-6 rounded-md inline-block">{{ $generatedId }}</p>
            </div>
        @else
            <form wire:submit.prevent="submit" class="space-y-6">
                {{-- 
                    Le contenu de votre formulaire reste exactement le même ici.
                    Aucun changement n'est nécessaire à l'intérieur de la balise <form>.
                --}}
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="company-name" class="form-label">Nom de votre entreprise</label>
                        <input type="text" id="company-name" wire:model.defer="company_name" class="w-full form-input">
                        @error('company_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="company-type" class="form-label">Secteur d'activité</label>
                        <select id="company-type" wire:model.defer="company_type" class="w-full form-input">
                            <option value="">Sélectionnez un secteur</option>
                            <option value="Hôtel/Restaurant">Hôtel/Restaurant</option>
                            <option value="Grossiste">Grossiste</option>
                            <option value="Particulier">Particulier</option>
                        </select>
                        @error('company_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                {{-- ... autres champs du formulaire ... --}}
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact-name" class="form-label">Votre nom</label>
                        <input type="text" id="contact-name" wire:model.defer="contact_name" class="w-full form-input">
                        @error('contact_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="phone" class="form-label">Numéro de téléphone</label>
                        <input type="tel" id="phone" wire:model.defer="phone" class="w-full form-input">
                        @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                   <label for="email" class="form-label">Adresse e-mail</label>
                   <input type="email" id="email" wire:model.defer="email" class="w-full form-input">
                   @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="message" class="form-label">Votre besoin (produits, quantités estimées...)</label>
                    <textarea id="message" wire:model.defer="message" rows="5" class="w-full form-input"></textarea>
                    @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="text-center pt-4">
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="submit">Envoyer ma demande</span>
                        <span wire:loading wire:target="submit">Envoi en cours...</span>
                    </button>
                </div>
            </form>
        @endif

    @endif
</div>