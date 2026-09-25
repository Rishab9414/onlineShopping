@php
    $img = $product->displayImageUrl();
    $price = $product->selling_price ?? $product->price;
    $mrp = $product->mrp ?? $product->compare_price;
    $discount = $mrp && $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0;
@endphp
<div class="group ethnic-card overflow-hidden">
    <div class="relative aspect-[4/5] bg-brand-gray overflow-hidden">
        <a href="{{ route('products.show', $product) }}" class="block w-full h-full">
            <img src="{{ $img }}" onerror="this.onerror=null;this.src='{{ asset('images/clothing-placeholder.svg') }}'" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
        </a>
        @if($discount > 0)
            <span class="absolute top-3 left-3 bg-brand-red text-white text-xs font-bold px-2.5 py-1 rounded-md z-10">{{ $discount }}% OFF</span>
        @endif
        @if($product->featured)
            <span class="absolute bottom-3 left-3 bg-brand-dark text-amber-50 text-xs font-bold px-2.5 py-1 rounded-full z-10">CURATED</span>
        @endif
        <div class="absolute top-3 right-3">
            <x-wishlist-button :product="$product" size="sm" />
        </div>
    </div>
    <div class="p-2.5 sm:p-4">
        <p class="text-[10px] sm:text-xs text-brand-red font-semibold uppercase tracking-wide mb-0.5 sm:mb-1 truncate">{{ $product->brand?->name ?? $product->category?->name }}</p>
        <a href="{{ route('products.show', $product) }}" class="block">
            <h3 class="font-semibold text-brand-black text-xs sm:text-sm leading-snug line-clamp-2 group-hover:text-brand-red transition-colors">{{ $product->name }}</h3>
        </a>
        <div class="mt-1.5 sm:mt-2 flex items-baseline gap-1.5 sm:gap-2">
            <span class="text-sm sm:text-lg font-bold text-brand-black">@money($price, 0)</span>
            @if($mrp && $mrp > $price)
                <span class="text-[11px] sm:text-sm text-zinc-400 line-through">@money($mrp, 0)</span>
            @endif
        </div>
        @if($product->isInStock())
            <x-add-to-cart-button :product="$product" />
        @else
            <p class="mt-3 text-sm text-brand-red font-semibold text-center py-2.5 bg-red-50 rounded-xl">Out of Stock</p>
        @endif
    </div>
</div>
