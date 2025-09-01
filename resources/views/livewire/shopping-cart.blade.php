<div>
    {{-- Le bouton flottant ne s'affiche que si le client est connecté --}}
    @if($client)
    <div class="fixed bottom-4 right-4 md:bottom-8 md:right-8 z-50">
        <button wire:click="$toggle('isCartOpen')" class="bg-brand-red hover:bg-red-700 text-white p-4 rounded-full shadow-lg transition-all duration-300 transform scale-100 hover:scale-110">
            <i class="fas fa-shopping-cart text-2xl"></i>
            @if($cartCount > 0)
                <span class="absolute top-0 right-0 bg-white text-black text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center -mt-1 -mr-1">
                    {{ $cartCount }}
                </span>
            @endif
        </button>
    </div>
    @endif

    {{-- Panier latéral rétractable --}}
    <div x-data="{ open: @entangle('isCartOpen') }" x-show="open" @keydown.escape.window="open = false" class="fixed inset-0 overflow-hidden z-50" style="display: none;">
        <div class="absolute inset-0 overflow-hidden">
            <div x-show="open" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="open = false"></div>
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="open" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="w-screen max-w-md">
                    <div class="h-full flex flex-col bg-dark-card shadow-xl overflow-y-scroll border-l border-border-dark">
                        <div class="flex-1 py-6 overflow-y-auto px-4 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-2xl font-teko text-white">Votre Panier</h2>
                                <button @click="open = false" class="-m-2 p-2 text-gray-400 hover:text-white">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                            <div class="mt-8">
                                <ul role="list" class="-my-6 divide-y divide-border-dark">
                                    @forelse ($cartItems as $rowId => $item)
                                        <li class="py-6 flex">
                                            <div class="ml-4 flex-1 flex flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-white">
                                                        <h3>{{ $item['name'] }}</h3>
                                                        <p class="ml-4">{{ number_format($item['price'] * $item['qty'], 0, ',', ' ') }} FCFA</p>
                                                    </div>
                                                </div>
                                                <div class="flex-1 flex items-end justify-between text-sm">
                                                    <p class="text-slate-400">Qté {{ $item['qty'] }}</p>
                                                    <button wire:click="removeFromCart('{{ $rowId }}')" type="button" class="font-medium text-brand-red hover:text-red-500">Retirer</button>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <p class="text-center text-slate-400 py-12">Votre panier est vide.</p>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        @if($cartCount > 0)
                        <div class="border-t border-border-dark py-6 px-4 sm:px-6">
                            <div class="flex justify-between text-lg font-teko text-white">
                                <p>Sous-total</p>
                                <p>{{ $cartTotal }} FCFA</p>
                            </div>
                            <div class="mt-6">
                                {{-- CORRECTION : Utilise 'client.checkout' --}}
                                <a href="{{ route('client.checkout') }}" wire:navigate class="btn btn-primary w-full text-center">Finaliser la commande</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>