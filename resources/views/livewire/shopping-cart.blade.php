<div>
    {{-- Bouton flottant du panier --}}
    @if($client)
    <div class="fixed bottom-4 right-4 md:bottom-8 md:right-8 z-50">
        <button wire:click="$toggle('isCartOpen')"
                class="bg-primary hover:bg-red-700 text-white p-4 rounded-full shadow-lg transition-all duration-300 transform scale-100 hover:scale-110">
            <i class="fas fa-shopping-cart text-2xl"></i>
            @if(count($cartItems) > 0)
                <span class="absolute top-0 right-0 bg-white text-black text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center -mt-1 -mr-1">
                    {{ array_sum(array_column($cartItems, 'quantity')) }}
                </span>
            @endif
        </button>
    </div>
    @endif

    {{-- Panier latéral rétractable --}}
    <div class="fixed top-0 right-0 h-full w-full md:w-96 bg-dark-card border-l-2 border-primary shadow-2xl z-40 transition-transform duration-300 ease-in-out
                {{ $isCartOpen ? 'translate-x-0' : 'translate-x-full' }}">
        <div class="p-6 h-full flex flex-col">
            <div class="flex justify-between items-center pb-4 border-b border-border-dark">
                <h3 class="font-teko text-2xl text-white">
                    <i class="fas fa-shopping-cart mr-2 brand-red"></i>
                    Récapitulatif de Commande
                </h3>
                <button wire:click="$set('isCartOpen', false)" class="text-white hover:text-primary">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="space-y-3 my-6 flex-1 overflow-y-auto pr-2">
                @if(empty($cartItems))
                    <p class="text-center text-slate-400 mt-10">Votre panier est vide.</p>
                @else
                    @foreach($cartItems as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div>
                                <p class="font-bold text-white">{{ $item['name'] }}</p>
                                <p class="text-xs text-slate-400">Calibre: {{ $item['calibre'] }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" value="{{ $item['quantity'] }}"
                                    wire:change="updateQuantity({{ $item['variant_id'] }}, $event.target.value)"
                                    min="1" class="w-16 form-input py-1 text-center">
                                <button wire:click="removeItem({{ $item['variant_id'] }})" class="text-slate-500 hover:text-red-500"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            @if(!empty($cartItems))
            <div class="mt-auto pt-4 border-t border-border-dark">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-slate-300">Total Cartons:</span>
                    <span class="font-bold text-2xl text-white">{{ array_sum(array_column($cartItems, 'quantity')) }}</span>
                </div>
                <a href="{{ route('checkout') }}" class="btn btn-primary w-full">Finaliser la Commande</a>
            </div>
            @endif
        </div>
    </div>
</div>