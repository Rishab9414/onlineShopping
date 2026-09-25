@extends('layouts.shop')

@section('title', 'Shop - ' . config('app.name'))

@php
    $heading = $activeCategory?->name ?? 'Shop the Collection';
    $resultCount = $products->total();
    $searchValue = request('search');
    $allUrl = route('products.index', array_filter(['search' => $searchValue]));
@endphp

@push('shop-subheader')
<div class="lg:hidden border-t border-amber-900/10">
    <div class="chip-scroll px-4 py-2">
        <a href="{{ $allUrl }}"
           class="chip {{ ! request('category') ? 'chip-active' : '' }}">All</a>
        @foreach($categories as $category)
            <a href="{{ route('products.index', array_filter(['category' => $category->slug, 'search' => $searchValue])) }}"
               class="chip {{ request('category') === $category->slug ? 'chip-active' : '' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>
</div>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3 pb-10 lg:py-10">
    <div class="mb-3 lg:mb-10 lg:text-center">
        <p class="hidden lg:block text-brand-red text-xs font-bold uppercase tracking-[.22em]">The womenswear edit</p>
        <div class="flex items-end justify-between gap-3 lg:mt-2 lg:block">
            <h1 class="font-display text-xl sm:text-3xl lg:text-5xl font-bold text-brand-black leading-tight">{{ $heading }}</h1>
            <p class="text-xs text-stone-500 shrink-0 lg:mt-3 lg:text-base">{{ $resultCount }} {{ Str::plural('style', $resultCount) }}</p>
        </div>
        <p class="hidden lg:block text-stone-500 mt-2">Timeless ethnic favourites and modern silhouettes, selected for her.</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="hidden lg:block lg:w-64 shrink-0">
            <div class="ethnic-card p-6 sticky top-28">
                <h2 class="font-semibold text-gray-900 mb-4">Categories</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ $allUrl }}" class="text-sm {{ ! request('category') ? 'text-brand-red font-medium' : 'text-gray-600 hover:text-brand-red' }}">
                            All Products
                        </a>
                    </li>
                    @foreach($categories as $category)
                        <li>
                            <a href="{{ route('products.index', array_filter(['category' => $category->slug, 'search' => $searchValue])) }}"
                               class="flex items-center justify-between gap-3 text-sm {{ request('category') === $category->slug ? 'text-brand-red font-medium' : 'text-gray-600 hover:text-brand-red' }}">
                                <span>{{ $category->name }}</span>
                                <span class="text-[11px] text-stone-400">{{ $category->products_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <div class="flex-1 min-w-0">
            <form method="GET" action="{{ route('products.index') }}" class="hidden lg:flex mb-6">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="flex gap-2">
                    <input type="search" name="search" value="{{ $searchValue }}" placeholder="Search kurtas, sarees, dresses…"
                        class="flex-1 min-w-0 rounded-full border border-amber-900/15 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none">
                    <button type="submit" class="shrink-0 bg-brand-red text-white px-4 sm:px-6 py-2.5 rounded-full text-sm font-semibold hover:bg-brand-dark">Search</button>
                </div>
            </form>

            @if($products->isEmpty())
                <div class="text-center py-16 ethnic-card">
                    <p class="text-gray-500 text-lg">No products found.</p>
                    <a href="{{ route('products.index') }}" class="text-brand-red font-medium mt-2 inline-block">Clear filters</a>
                </div>
            @else
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 lg:gap-6">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
