@props(['product'])

@php
    $variants = $product->relationLoaded('variants')
        ? $product->variants->where('is_active', true)->values()
        : collect();

    $options = $variants->map(fn ($variant) => [
        'id' => $variant->id,
        'label' => $variant->shortLabel(),
        'stock' => (int) $variant->availableStock(),
    ])->values();

    $defaultVariant = $options->first(fn ($variant) => $variant['stock'] > 0) ?? $options->first();
@endphp

<div
    class="mt-2 sm:mt-3 space-y-1.5 sm:space-y-2"
    x-data="addToCartForm({
        url: @js(route('cart.store', $product)),
        variants: @js($options),
        selectedId: @js($defaultVariant['id'] ?? null),
        quantity: 1,
        showSelect: {{ $options->count() > 1 ? 'true' : 'false' }},
    })"
>
    <template x-if="showSelect">
        <select
            x-model.number="selectedId"
            class="w-full max-w-full rounded-lg sm:rounded-xl border border-zinc-200 bg-white px-2 sm:px-3 py-1.5 sm:py-2 text-[10px] sm:text-xs font-medium text-brand-black focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none"
        >
            <template x-for="variant in variants" :key="variant.id">
                <option :value="variant.id" :disabled="variant.stock < 1" x-text="variant.label + (variant.stock < 1 ? ' — Out of stock' : '')"></option>
            </template>
        </select>
    </template>
    <button
        type="button"
        @click="submit()"
        :disabled="loading"
        class="w-full bg-brand-red text-white py-2 sm:py-2.5 px-3 sm:px-4 rounded-full text-[11px] sm:text-sm font-semibold hover:bg-brand-dark transition-colors disabled:opacity-60"
    >
        <span class="sm:hidden" x-text="loading ? '…' : 'Add'"></span>
        <span class="hidden sm:inline" x-text="loading ? 'Adding…' : 'Add to Cart'"></span>
    </button>
</div>
