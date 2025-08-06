<div>
    @if($client && !empty($cartItems))
        <div class="fixed bottom-0 left-0 right-0 md:left-auto md:bottom-8 md:right-8 bg-dark-card border-t-2 md:border-2 border-primary rounded-t-lg md:rounded-lg shadow-2xl md:w-96 z-50">
            <div class="p-6">
                <h3 class="font-teko text-2xl text-white mb-4">
                    <i class="fas fa-shopping-cart mr-2 brand-red"></i>
                    Récapitulatif de Commande
                </h3>
                <div class="space-y-3 max-h-48 md:max-h-64 overflow-y-auto pr-2">
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
                </div>
                <div class="mt-6 pt-4 border-t border-border-dark">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-slate-300">Total Cartons:</span>
                        <span class="font-bold text-2xl text-white">{{ array_sum(array_column($cartItems, 'quantity')) }}</span>
                    </div>
                    <a href="{{ route('checkout') }}" class="btn btn-primary w-full">Finaliser la Commande</a>
                </div>
            </div>
        </div>
    @endif
</div>