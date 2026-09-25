@extends('admin.layouts.app')

@section('title', 'Store Profile')
@section('page-title', 'Store Profile')
@section('page-subtitle', 'Company name, logo, address, and contact details used across the website')

@section('content')
<form action="{{ route('admin.settings.store.update') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <div>
            <h3 class="font-bold text-lg text-slate-900 mb-1">Brand identity</h3>
            <p class="text-sm text-slate-500">These details appear in the header, footer, emails, invoices, and shipping labels.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Company name</label>
                <input type="text" name="business_name" value="{{ old('business_name', $values['business_name']) }}" required
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                @error('business_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tagline</label>
                <input type="text" name="business_tagline" value="{{ old('business_tagline', $values['business_tagline']) }}"
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Elegant ethnic and contemporary womenswear">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Short about text</label>
                <textarea name="business_about" rows="3"
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">{{ old('business_about', $values['business_about']) }}</textarea>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-6">
            <label class="block text-sm font-semibold text-slate-700 mb-3">Logo</label>
            <div class="flex flex-col sm:flex-row gap-5 items-start">
                <div class="w-24 h-24 shrink-0 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden p-2">
                    <x-store-logo height="72px" maxWidth="72px" />
                </div>
                <div class="flex-1 space-y-3">
                    <input type="file" name="business_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                        class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-slate-400">PNG, JPG, WEBP, or SVG. Max 2 MB. Used on the storefront, admin, emails, and invoices.</p>
                    @if($store->hasCustomLogo())
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Remove uploaded logo and use the default mark
                    </label>
                    @endif
                    @error('business_logo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <div>
            <h3 class="font-bold text-lg text-slate-900 mb-1">Contact & address</h3>
            <p class="text-sm text-slate-500">Shown to customers and printed on invoices and shipping labels.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Support email</label>
                <input type="email" name="business_email" value="{{ old('business_email', $values['business_email']) }}" required
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                @error('business_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Support phone</label>
                <input type="text" name="business_phone" value="{{ old('business_phone', $values['business_phone']) }}" required
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="9743663260">
                @error('business_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Street address</label>
                <input type="text" name="business_address" value="{{ old('business_address', $values['business_address']) }}"
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">City</label>
                <input type="text" name="business_city" value="{{ old('business_city', $values['business_city']) }}"
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">State</label>
                <input type="text" name="business_state" value="{{ old('business_state', $values['business_state']) }}"
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Pincode</label>
                <input type="text" name="business_pincode" value="{{ old('business_pincode', $values['business_pincode']) }}"
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Country</label>
                <input type="text" name="business_country" value="{{ old('business_country', $values['business_country']) }}"
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">GSTIN</label>
                <input type="text" name="business_gstin" value="{{ old('business_gstin', $values['business_gstin']) }}"
                    class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional">
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">Save store details</button>
    </div>
</form>
@endsection
