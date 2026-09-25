@extends('layouts.shop')

@section('title', config('seo.pages.home.title'))

@section('content')
@if(!empty($promoPopup))
<x-promo-popup :popup="$promoPopup" />
@endif

@if(($banners ?? collect())->isNotEmpty())
<section
    x-data="{
        current: 0,
        total: {{ $banners->count() }},
        timer: null,
        init() { this.startAutoplay(); },
        startAutoplay() {
            clearInterval(this.timer);
            if (this.total < 2) return;
            this.timer = setInterval(() => this.next(), 5500);
        },
        next() { this.current = (this.current + 1) % this.total; },
        prev() { this.current = (this.current - 1 + this.total) % this.total; },
        go(i) { this.current = i; this.startAutoplay(); }
    }"
    @mouseenter="clearInterval(timer)"
    @mouseleave="startAutoplay()"
    class="relative w-full overflow-hidden bg-brand-black group"
    style="height: 78vh; min-height: 520px; max-height: 860px;"
>
    @foreach($banners as $index => $banner)
    <a href="{{ $banner->targetUrl() }}"
       x-show="current === {{ $index }}"
       class="absolute inset-0 block cursor-pointer"
       @if($index !== 0) style="display:none" @endif
    >
        <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r {{ ($homeTheme ?? null)?->heroOverlayClasses() ?? 'from-brand-black/45 via-brand-black/15 to-transparent' }}"></div>
        <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
            <div class="max-w-2xl">
                @if(($homeTheme ?? null)?->hero_badge_text)
                <span class="inline-flex items-center gap-2 bg-brand-red/20 border border-brand-red/50 text-brand-red text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-5">{{ $homeTheme->hero_badge_text }}</span>
                @elseif($banner->category)
                <span class="inline-flex items-center gap-2 bg-brand-red/20 border border-brand-red/50 text-brand-red text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-5">{{ $banner->category->name }}</span>
                @endif
                <h1 class="font-display text-4xl sm:text-5xl lg:text-7xl font-bold text-white leading-[1.05] drop-shadow-[0_2px_12px_rgba(0,0,0,0.55)]">{{ $banner->title }}</h1>
                @if($banner->subtitle)
                <p class="mt-5 text-lg text-white/90 max-w-xl leading-relaxed">{{ $banner->subtitle }}</p>
                @endif
                <span class="mt-8 inline-flex items-center gap-3 bg-brand-red text-white font-bold px-8 py-4 rounded-xl">
                    {{ $banner->button_text }}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </span>
            </div>
        </div>
    </a>
    @endforeach
    @if($banners->count() > 1)
    <button type="button" @click.prevent="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/10 text-white hover:bg-brand-red transition-all">
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button type="button" @click.prevent="next()" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-white/10 text-white hover:bg-brand-red transition-all">
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
    <div class="absolute bottom-6 left-0 right-0 z-20 flex justify-center gap-2">
        @foreach($banners as $index => $banner)
        <button type="button" @click.prevent="go({{ $index }})" class="h-2 rounded-full transition-all" :class="current === {{ $index }} ? 'w-8 bg-white' : 'w-2 bg-white/50'"></button>
        @endforeach
    </div>
    @endif
</section>
@else
<section class="relative min-h-[68vh] flex items-center bg-brand-cream overflow-hidden">
    <img src="{{ asset('images/fashion-hero.svg') }}" alt="Ridhi Sidhi Garments womenswear collection" class="absolute inset-0 w-full h-full object-cover object-center">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 w-full">
        <div class="max-w-xl">
            <p class="text-brand-red font-bold text-xs uppercase tracking-[.3em] mb-4">{{ $store->name() }} · New Season</p>
            <h1 class="font-display text-5xl sm:text-6xl lg:text-7xl font-bold text-brand-black leading-[.95]">Grace, woven<br>for every day.</h1>
            <p class="mt-6 text-stone-600 max-w-md leading-relaxed">Discover elegant kurtas, sarees, co-ords and occasion styles curated exclusively for women.</p>
            <a href="{{ route('products.index') }}" class="mt-8 inline-block bg-brand-red text-white font-bold px-8 py-3.5 rounded-full hover:bg-brand-dark transition-colors">Explore the Collection</a>
        </div>
    </div>
</section>
@endif

<x-announcement-ticker :announcements="$tickerAnnouncements ?? collect()" />

{{-- WOMENSWEAR CATEGORIES --}}
@if($categories->isNotEmpty())
<section class="py-16 lg:py-24 bg-brand-gray" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 150)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 transition-all duration-700" :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
            <p class="text-brand-red font-bold text-sm uppercase tracking-widest mb-2">Find your silhouette</p>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-brand-black">Shop by Category</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
            @foreach($categories as $category)
            <a href="{{ route('products.index', ['category' => $category->slug]) }}"
               class="group relative rounded-2xl overflow-hidden aspect-[4/5] bg-zinc-100 shadow-lg hover:shadow-xl hover:shadow-brand-red/10 transition-all duration-500 hover:-translate-y-2"
               style="transition-delay: {{ $loop->index * 50 }}ms">
                <img src="{{ $category->imageUrl() }}" onerror="this.onerror=null;this.src='{{ asset('images/clothing-placeholder.svg') }}'" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/50 to-transparent pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 lg:p-5">
                    <h3 class="text-white font-bold text-sm lg:text-base">{{ $category->name }}</h3>
                    <p class="text-zinc-400 text-xs mt-0.5">{{ $category->products_count }} products</p>
                    <span class="inline-block mt-2 text-brand-red text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity translate-x-0 group-hover:translate-x-1 duration-300">Shop →</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(($homeReelsEnabled ?? false) && ($homeReels ?? collect())->isNotEmpty())
    <x-home-reels :reels="$homeReels" :autoplay="$homeReelsAutoplay ?? true" />
@endif

{{-- FEATURED PRODUCTS --}}
@if($featuredProducts->isNotEmpty())
<section class="py-16 lg:py-24" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 200)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10 transition-all duration-700" :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
            <div>
                <p class="text-brand-red font-bold text-sm uppercase tracking-widest mb-2">Handpicked</p>
                <h2 class="text-3xl lg:text-4xl font-black text-brand-black">FEATURED <span class="text-brand-red">PRODUCTS</span></h2>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-brand-red font-bold hover:gap-3 transition-all">View All Products →</a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
            @foreach($featuredProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- SPLIT: TRENDING + NEW ARRIVALS --}}
@if($trendingProducts->isNotEmpty() || $newArrivals->isNotEmpty())
<section class="py-16 bg-brand-gray">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12">
            @if($trendingProducts->isNotEmpty())
            <div x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 300)" class="transition-all duration-700" :class="shown ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-8'">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-8 bg-brand-red rounded-full"></div>
                    <h2 class="text-2xl font-black text-brand-black">TRENDING <span class="text-brand-red">NOW</span></h2>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    @foreach($trendingProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
            @endif
            @if($newArrivals->isNotEmpty())
            <div x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 450)" class="transition-all duration-700" :class="shown ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-8'">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-8 bg-brand-black rounded-full"></div>
                    <h2 class="text-2xl font-black text-brand-black">NEW <span class="text-brand-red">ARRIVALS</span></h2>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    @foreach($newArrivals as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- BRANDS MARQUEE --}}
@if($brands->isNotEmpty())
<section class="py-10 border-y border-zinc-100 bg-white overflow-hidden">
    <p class="text-center text-xs font-bold uppercase tracking-widest text-zinc-400 mb-6">Trusted Brands We Carry</p>
    <div class="relative flex overflow-hidden">
        <div class="flex animate-marquee whitespace-nowrap">
            @for($i = 0; $i < 2; $i++)
            <div class="flex shrink-0 items-center gap-14 px-8">
                @foreach($brands as $brand)
                <span class="text-xl lg:text-2xl font-black text-zinc-300 hover:text-brand-red transition-colors">{{ $brand->name }}</span>
                @endforeach
            </div>
            @endfor
        </div>
    </div>
</section>
@endif

{{-- WHY CHOOSE US --}}
<section class="py-16 lg:py-24" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 250)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 transition-all duration-700" :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'">
            <p class="text-brand-red font-bold text-sm uppercase tracking-widest mb-2">The Ridhi Sidhi promise</p>
            <h2 class="font-display text-4xl font-bold text-brand-black">Style with confidence</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                ['icon' => '✦', 'title' => 'Curated for Women', 'desc' => 'Thoughtful silhouettes selected for comfort, confidence and everyday elegance'],
                ['icon' => '❋', 'title' => 'Quality Fabrics', 'desc' => 'Beautiful materials, refined details and dependable finishing'],
                ['icon' => '♡', 'title' => 'Personal Support', 'desc' => 'Friendly help with sizing, styling, orders and returns'],
            ] as $feature)
            <div class="text-center p-6 rounded-2xl border border-zinc-100 hover:border-brand-red/30 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                <div class="text-4xl mb-4 group-hover:scale-125 transition-transform duration-300 animate-float" style="animation-delay: {{ $loop->index * 0.5 }}s">{{ $feature['icon'] }}</div>
                <h3 class="font-bold text-brand-black mb-2">{{ $feature['title'] }}</h3>
                <p class="text-sm text-zinc-500 leading-relaxed">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ANIMATED STATS + CTA --}}
<section class="relative py-20 lg:py-28 overflow-hidden" x-data="{
    shown: false,
    counts: { products: 0, brands: 0, customers: 0 },
    targets: { products: 500, brands: 50, customers: 10000 },
    animate() {
        const duration = 2000;
        const start = performance.now();
        const tick = (now) => {
            const p = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            this.counts.products = Math.floor(this.targets.products * ease);
            this.counts.brands = Math.floor(this.targets.brands * ease);
            this.counts.customers = Math.floor(this.targets.customers * ease);
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }
}" x-init="setTimeout(() => { shown = true; animate(); }, 500)">
    <div class="absolute inset-0 bg-brand-black">
        <img src="{{ asset('images/fashion-hero.svg') }}" alt="" class="w-full h-full object-cover opacity-10">
        <div class="absolute inset-0 bg-gradient-to-b from-brand-black/50 via-brand-black/90 to-brand-black"></div>
    </div>

    {{-- Floating decorative elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-[10%] text-5xl text-amber-200 opacity-20 animate-float">✦</div>
        <div class="absolute top-40 right-[15%] text-4xl text-amber-200 opacity-20 animate-float" style="animation-delay: 1s">❋</div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="transition-all duration-1000" :class="shown ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-12'">
                <x-store-logo class="mb-6" height="56px" maxWidth="180px" invert />
                <h2 class="font-display text-4xl lg:text-6xl font-bold text-white leading-tight">
                    Your next favourite look awaits.
                </h2>
                <p class="text-rose-100/70 mt-4 text-lg leading-relaxed">Join women who choose {{ $store->name() }} for beautiful, wearable style and attentive service.</p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('products.index') }}" class="bg-brand-red text-white font-bold px-8 py-4 rounded-xl hover:bg-red-700 transition-all shadow-lg shadow-brand-red/40 hover:-translate-y-0.5">Start Shopping</a>
                    @guest
                    <a href="{{ route('register') }}" class="border-2 border-white/30 text-white font-bold px-8 py-4 rounded-xl hover:bg-white hover:text-brand-black transition-all">Create Account</a>
                    @endguest
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 lg:gap-6 transition-all duration-1000 delay-200" :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
                <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-6 text-center hover:border-brand-red/50 hover:bg-white/10 transition-all duration-300 group">
                    <p class="text-3xl lg:text-4xl font-black text-brand-red" x-text="counts.products + '+'"></p>
                    <p class="text-xs text-zinc-400 uppercase tracking-wider mt-2 group-hover:text-white transition-colors">Products</p>
                </div>
                <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-6 text-center hover:border-brand-red/50 hover:bg-white/10 transition-all duration-300 group">
                    <p class="text-3xl lg:text-4xl font-black text-brand-red" x-text="counts.brands + '+'"></p>
                    <p class="text-xs text-zinc-400 uppercase tracking-wider mt-2 group-hover:text-white transition-colors">Brands</p>
                </div>
                <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-6 text-center hover:border-brand-red/50 hover:bg-white/10 transition-all duration-300 group">
                    <p class="text-3xl lg:text-4xl font-black text-amber-300" x-text="(counts.customers >= 1000 ? (counts.customers/1000).toFixed(0) + 'K' : counts.customers) + '+'"></p>
                    <p class="text-xs text-zinc-400 uppercase tracking-wider mt-2 group-hover:text-white transition-colors">Happy Customers</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
