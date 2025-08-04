<div>
    @if ($isSent)
        <div class="bg-green-900/50 border border-green-700 text-green-300 p-8 rounded-lg text-center">
            <i class="fas fa-check-circle text-6xl mb-4"></i>
            <h3 class="text-3xl font-teko uppercase text-white mb-2">Message Envoyé !</h3>
            <p>Merci. Votre message a bien été envoyé à notre équipe. Nous vous répondrons dans les plus brefs délais.</p>
        </div>
    @else
        <form wire:submit.prevent="submit" class="bg-slate-900/50 p-8 rounded-lg border border-slate-800 space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="form-label">Votre nom complet</label>
                    <input type="text" id="name" wire:model="name" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="phone" class="form-label">Votre numéro de téléphone</label>
                    <input type="tel" id="phone" wire:model="phone" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label for="subject" class="form-label">Sujet de votre demande</label>
                <select id="subject" wire:model="subject" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    <option>Question générale</option>
                    <option>Passer une commande (Particulier)</option>
                    <option>Demande de devis (Hôtel/Restaurant)</option>
                    <option>Demande de devis (Grossiste/Revendeur)</option>
                    <option>Autre</option>
                </select>
            </div>
            <div>
                <label for="message" class="form-label">Votre message</label>
                <textarea id="message" wire:model="message" rows="6" class="w-full bg-slate-800 border border-slate-700 rounded-md py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red" placeholder="Décrivez votre besoin ici..."></textarea>
                @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="text-right">
                <button type="submit" class="bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 transform hover:scale-105 py-3 px-8 rounded-sm">
                    Envoyer
                </button>
            </div>
        </form>
    @endif
</div>