<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Packing Slip {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #32161f; margin: 24px; }
        h1, h2, p { margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: #6b5b55; }
        .brand { color: #761737; }
        .title { font-size: 20px; letter-spacing: 1px; text-transform: uppercase; }
        .logo { height: 42px; max-width: 160px; }
        .box { border: 1px solid #e6d7c4; padding: 10px 12px; }
        .order-no { font-size: 18px; font-weight: bold; letter-spacing: 1px; }
        .items { margin-top: 16px; }
        .items th { background: #761737; color: #fff; text-align: left; padding: 7px 8px; font-size: 10px; text-transform: uppercase; }
        .items td { border-bottom: 1px solid #f0e6d8; padding: 7px 8px; }
        .right { text-align: right; }
        .pill { display: inline-block; border: 1px solid #761737; color: #761737; padding: 3px 8px; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .footer { margin-top: 28px; font-size: 10px; color: #6b5b55; border-top: 1px solid #e6d7c4; padding-top: 10px; }
        .note { margin-top: 16px; border: 1px dashed #c39a50; padding: 10px; }
    </style>
</head>
<body>
<table style="margin-bottom:16px">
    <tr>
        <td style="width:62%;vertical-align:top">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" class="logo" alt="{{ $store->name() }}"><br>
            @endif
            <h1 class="brand">{{ $store->name() }}</h1>
            @if($store->formattedAddress())
                <p class="muted" style="white-space:pre-line">{{ $store->formattedAddress() }}</p>
            @endif
            <p class="muted">{{ $store->email() }} | {{ $store->displayPhone() }}</p>
        </td>
        <td style="width:38%;text-align:right;vertical-align:top">
            <div class="title brand">Packing Slip</div>
            <p class="muted">Dispatch note · no courier AWB</p>
            <p class="order-no" style="margin-top:8px">{{ $order->order_number }}</p>
            <p>{{ $order->created_at?->format('d M Y') }}</p>
            <p class="pill" style="margin-top:8px">{{ strtoupper($order->payment_method) }}@if($order->payment_method === 'cod') · COLLECT Rs {{ number_format($order->displayTotal(), 2) }}@endif</p>
        </td>
    </tr>
</table>

<table style="margin-bottom:14px">
    <tr>
        <td class="box" style="width:49%">
            <p class="brand" style="font-weight:bold;margin-bottom:6px">From</p>
            <p><strong>{{ $store->name() }}</strong></p>
            <p class="muted" style="white-space:pre-line">{{ $store->formattedAddress() }}</p>
        </td>
        <td style="width:2%"></td>
        <td class="box" style="width:49%">
            <p class="brand" style="font-weight:bold;margin-bottom:6px">Deliver To</p>
            <p><strong>{{ $order->customer?->full_name ?? $order->user?->name ?? 'Customer' }}</strong></p>
            <p class="muted" style="white-space:pre-line">{{ $order->shipping_address }}</p>
            <p>Phone: {{ $order->customer?->mobile ?? $order->user?->phone ?? '—' }}</p>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:8%">#</th>
            <th>Item</th>
            <th>SKU / Variant</th>
            <th class="right">Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->product_name }}</td>
            <td>
                {{ $item->variant_sku ?? $item->sku }}
                @if($item->size_snapshot || $item->color_snapshot || $item->material_snapshot)
                    <br><span class="muted">{{ collect([$item->color_snapshot, $item->size_snapshot, $item->material_snapshot])->filter()->implode(' / ') }}</span>
                @endif
            </td>
            <td class="right">{{ $item->quantity }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="note">
    <p><strong>Packing checklist</strong></p>
    <p>1. Match size, colour, and quantity to this slip.</p>
    <p>2. Place this slip inside the packet.</p>
    <p>3. Hand over to your delivery person or the customer. No courier company is required.</p>
    @if($order->notes)
        <p style="margin-top:8px"><strong>Order notes:</strong> {{ $order->notes }}</p>
    @endif
</div>

<div class="footer">
    Packed by {{ $store->name() }} · {{ now()->format('d M Y, h:i A') }} · {{ $order->items->sum('quantity') }} piece(s)
</div>
</body>
</html>
