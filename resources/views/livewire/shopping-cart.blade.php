<div>
    @if($client && !empty($cartItems))
        <div class="fixed bottom-0 right-0 m-8 bg-dark-card border border-border-dark rounded-lg shadow-2xl w-96 z-50">
            <div class="p-6">
                <h3 class="font-teko text-2xl text-white mb-4">
                    <i class="fas fa-shopping-cart mr-2 brand-red"></i>
                    Récapitulatif de Commande
                </h3>
                <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                    @foreach($cartItems as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div>
                                <p class="font-bold text-white">{{ $item['name'] }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" value="{{ $item['quantity'] }}" 
                                       wire:change="updateQuantity({{ $item['product_id'] }}, $event.target.value)"
                                       min="1" class="w-16 bg-slate-800 border border-slate-700 rounded-md py-1 px-2 text-white text-center">
                                <button wire:click="removeItem({{ $item['product_id'] }})" class="text-slate-500 hover:text-red-500"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 pt-4 border-t border-border-dark">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-slate-300">Total Cartons:</span>
                        <span class="font-bold text-2xl text-white">{{ array_sum(array_column($cartItems, 'quantity')) }}</span>
                    </div>
                    <a href="{{ route('checkout') }}" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold tracking-widest uppercase transition duration-300 py-3 rounded-sm">
                        Finaliser la Commande
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>