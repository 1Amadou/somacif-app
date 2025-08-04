<div>
    @if ($applicationSubmitted)
        <div class="bg-green-900/50 border border-green-700 text-green-300 p-8 rounded-lg text-center">
            <i class="fas fa-check-circle text-6xl mb-4"></i>
            <h3 class="text-3xl font-teko uppercase text-white mb-2">Demande Envoyée !</h3>
            <p class="mb-6">Merci pour votre intérêt. Votre demande de partenariat est en cours d'examen par notre équipe.</p>
            <p class="mb-2 text-lg">Veuillez conserver précieusement votre identifiant temporaire :</p>
            <p class="text-3xl font-teko tracking-wider bg-slate-800 text-white py-3 px-6 rounded-md inline-block">{{ $generatedId }}</p>
            <p class="mt-6 text-sm">Vous pouvez utiliser cet identifiant sur le <a href="{{ route('nos-offres') }}" class="font-bold underline hover:text-white">portail de commande</a> pour suivre l'avancement de votre dossier.</p>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="company-name" class="form-label">Nom de votre entreprise</label>
                    <input type="text" id="company-name" wire:model="company_name" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    @error('company_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="company-type" class="form-label">Type d'entreprise</label>
                    <select id="company-type" wire:model="company_type" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                        <option>Hôtel/Restaurant</option>
                        <option>Grossiste</option>
                        <option>Particulier</option>
                    </select>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="contact-name" class="form-label">Votre nom</label>
                    <input type="text" id="contact-name" wire:model="contact_name" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                     @error('contact_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="phone" class="form-label">Numéro de téléphone</label>
                    <input type="tel" id="phone" wire:model="phone" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                     @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label for="message" class="form-label">Votre besoin (produits, quantités estimées...)</label>
                <textarea id="message" wire:model="message" rows="5" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red"></textarea>
                 @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="text-center pt-4">
                <button type="submit" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 transform hover:scale-105 py-4 px-12 rounded-sm">
                    <span wire:loading.remove>Envoyer ma demande</span>
                    <span wire:loading>Envoi en cours...</span>
                </button>
            </div>
        </form>
    @endif
</div>