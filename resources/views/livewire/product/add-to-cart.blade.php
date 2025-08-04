<div>
    <div class="flex items-center gap-4">
        <div>
            <label for="quantity" class="text-sm text-slate-400">Quantité</label>
            <input type="number" id="quantity" wire:model="quantity" min="1" class="w-24 bg-slate-800 border border-slate-700 rounded-md py-2 px-2 text-white text-center">
        </div>
        <button wire:click="addToCart" class="w-full bg-brand-red hover-bg-brand-red text-white font-bold tracking-widest uppercase transition duration-300 py-3 px-8 rounded-sm self-end">
            Ajouter au panier
        </button>
    </div>
    @if($message)
        <p class="text-green-400 text-sm mt-3">{{ $message }}</p>
    @endif
</div>