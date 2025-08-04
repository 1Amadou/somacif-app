@props(['product'])

<div class="product-card-info rounded-lg overflow-hidden group">
    <a href="{{ route('products.show', $product) }}" class="block overflow-hidden h-56">
        <img src="{{ $product->image_principale ? Storage::url($product->image_principale) : 'https://placehold.co/400x300/171717/FFFFFF?text=Image+Produit' }}" 
             alt="Image de {{ $product->nom }}" 
             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
    </a>
    <div class="p-6">
        <h3 class="text-2xl text-white mb-2 font-teko truncate">{{ $product->nom }}</h3>
        <p class="text-slate-400 text-sm mb-4 h-20 overflow-hidden">
            {{ $product->description_courte }}
        </p>
        <a href="{{ route('products.show', $product) }}" class="font-bold text-sm brand-red hover:text-red-400 transition-colors">
            Voir les détails <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
</div>