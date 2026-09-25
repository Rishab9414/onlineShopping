@extends('admin.layouts.app')

@section('title', 'Homepage Settings')
@section('page-title', 'Homepage Settings')
@section('page-subtitle', 'Control homepage sections for customers')

@section('content')
<form action="{{ route('admin.settings.homepage.update') }}" method="POST" class="max-w-2xl">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <div>
            <h3 class="font-bold text-lg text-slate-900 mb-1">Video Reels</h3>
            <p class="text-sm text-slate-500 mb-4">Short videos shown on the homepage after Shop by Category.</p>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 mb-3">
                <input type="hidden" name="home_reels_enabled" value="0">
                <input type="checkbox" name="home_reels_enabled" value="1" @checked(old('home_reels_enabled', $homeReelsEnabled))
                    class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">Enable Video Reels section</p>
                    <p class="text-sm text-slate-500 mt-0.5">Shows uploaded reel videos on the homepage.</p>
                </div>
            </label>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50">
                <input type="hidden" name="home_reels_autoplay" value="0">
                <input type="checkbox" name="home_reels_autoplay" value="1" @checked(old('home_reels_autoplay', $homeReelsAutoplay))
                    class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">Autoplay videos (muted)</p>
                    <p class="text-sm text-slate-500 mt-0.5">When off, videos show a play button — customer taps to play.</p>
                </div>
            </label>

            <p class="text-sm text-slate-500 mt-3">
                Manage videos in <a href="{{ route('admin.home-reels.index') }}" class="text-indigo-600 hover:underline font-medium">Home Reels</a>.
            </p>
        </div>

        <div class="border-t border-slate-100 pt-6">
            <h3 class="font-bold text-lg text-slate-900 mb-1">Coupons</h3>
            <p class="text-sm text-slate-500 mb-4">Allow customers to apply coupon codes on cart and checkout.</p>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50">
                <input type="hidden" name="coupons_enabled" value="0">
                <input type="checkbox" name="coupons_enabled" value="1" @checked(old('coupons_enabled', $couponsEnabled))
                    class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">Enable coupons</p>
                    <p class="text-sm text-slate-500 mt-0.5">Customers can enter a code and the discount is applied to the order.</p>
                </div>
            </label>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">Save Settings</button>
    </div>
</form>
@endsection
