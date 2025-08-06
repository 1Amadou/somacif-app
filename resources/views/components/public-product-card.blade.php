@props(['product'])

<div class="product-card-catalogue rounded-lg overflow-hidden group flex flex-col">
    <div class="overflow-hidden h-56">
        <img src="{{ $product->image_principale ? Storage::url($product->image_principale) : 'https://placehold.co/400x300/171717/FFFFFF?text=Image+Produit' }}" 
             alt="Image de {{ $product->nom }}" 
             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
    </div>
    <div class="p-6 flex flex-col flex-grow">
        <h3 class="text-2xl text-white mb-2 font-teko truncate">{{ $product->nom }}</h3>
        <p class="text-slate-400 text-sm mb-4 flex-grow">{{ $product->description_courte }}</p>
        <div class="mt-auto pt-4 border-t border-slate-800">
            <a href="{{ route('products.show', $product) }}" class="font-bold text-sm brand-red hover:text-red-400 transition-colors">
                Voir les détails <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>