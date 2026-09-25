<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->invoiceRecord?->invoice_no ?? $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #32161f; margin: 24px; }
        h1, h2, h3, p { margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: #6b5b55; }
        .brand { color: #761737; }
        .header td { vertical-align: top; padding-bottom: 14px; }
        .logo { height: 42px; max-width: 160px; }
        .title { font-size: 20px; letter-spacing: 1px; text-transform: uppercase; }
        .box { border: 1px solid #e6d7c4; padding: 10px 12px; }
        .meta td { padding: 2px 0; }
        .items { margin-top: 16px; }
        .items th { background: #761737; color: #fff; text-align: left; padding: 7px 8px; font-size: 10px; text-transform: uppercase; }
        .items td { border-bottom: 1px solid #f0e6d8; padding: 7px 8px; vertical-align: top; }
        .right { text-align: right; }
        .totals { width: 260px; margin-left: auto; margin-top: 12px; }
        .totals td { padding: 4px 0; }
        .grand { border-top: 2px solid #761737; font-size: 13px; font-weight: bold; }
        .footer { margin-top: 28px; font-size: 10px; color: #6b5b55; border-top: 1px solid #e6d7c4; padding-top: 10px; }
    </style>
</head>
<body>
<table class="header">
    <tr>
        <td style="width:62%">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" class="logo" alt="{{ $store->name() }}"><br>
            @endif
            <h1 class="brand">{{ $store->name() }}</h1>
            <p class="muted">{{ $store->tagline() }}</p>
            @if($store->formattedAddress())
                <p class="muted" style="white-space:pre-line;margin-top:6px">{{ $store->formattedAddress() }}</p>
            @endif
            @if($store->gstin())
                <p><strong>GSTIN:</strong> {{ $store->gstin() }}</p>
            @endif
        </td>
        <td style="width:38%;text-align:right">
            <div class="title brand">Tax Invoice</div>
            <table class="meta" style="margin-left:auto;width:auto;margin-top:8px">
                <tr><td class="muted">Invoice</td><td><strong>{{ $order->invoiceRecord?->invoice_no ?? 'DRAFT' }}</strong></td></tr>
                <tr><td class="muted">Order</td><td>{{ $order->order_number }}</td></tr>
                <tr><td class="muted">Date</td><td>{{ ($order->invoiceRecord?->invoice_date ?? now())->format('d M Y') }}</td></tr>
                <tr><td class="muted">Payment</td><td>{{ strtoupper($order->payment_method) }} · {{ $order->paymentStatusLabel() }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table style="margin-bottom:14px">
    <tr>
        <td class="box" style="width:49%">
            <p class="brand" style="font-weight:bold;margin-bottom:6px">Bill To</p>
            <p><strong>{{ $order->customer?->full_name ?? $order->user?->name ?? 'Customer' }}</strong></p>
            <p class="muted" style="white-space:pre-line">{{ $order->billing_address ?: $order->shipping_address }}</p>
            @if($order->customer?->mobile || $order->user?->phone)
                <p>Phone: {{ $order->customer?->mobile ?? $order->user?->phone }}</p>
            @endif
        </td>
        <td style="width:2%"></td>
        <td class="box" style="width:49%">
            <p class="brand" style="font-weight:bold;margin-bottom:6px">Ship To</p>
            <p><strong>{{ $order->customer?->full_name ?? $order->user?->name ?? 'Customer' }}</strong></p>
            <p class="muted" style="white-space:pre-line">{{ $order->shipping_address }}</p>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:36%">Product</th>
            <th>SKU</th>
            <th class="right">Qty</th>
            <th class="right">Price</th>
            <th class="right">GST</th>
            <th class="right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>
                {{ $item->product_name }}
                @if($item->size_snapshot || $item->color_snapshot || $item->material_snapshot)
                    <br><span class="muted">{{ collect([$item->color_snapshot, $item->size_snapshot, $item->material_snapshot])->filter()->implode(' / ') }}</span>
                @endif
            </td>
            <td>{{ $item->variant_sku ?? $item->sku }}</td>
            <td class="right">{{ $item->quantity }}</td>
            <td class="right">Rs {{ number_format((float) $item->price, 2) }}</td>
            <td class="right">Rs {{ number_format((float) ($item->gst ?? 0), 2) }}</td>
            <td class="right">Rs {{ number_format($item->lineTotal(), 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td>Subtotal</td><td class="right">Rs {{ number_format((float) $order->subtotal, 2) }}</td></tr>
    @if((float) $order->discount > 0)
    <tr><td>Discount</td><td class="right">- Rs {{ number_format((float) $order->discount, 2) }}</td></tr>
    @endif
    <tr><td>Shipping</td><td class="right">Rs {{ number_format((float) $order->shipping_charge, 2) }}</td></tr>
    <tr><td>Tax</td><td class="right">Rs {{ number_format((float) $order->tax_amount, 2) }}</td></tr>
    <tr class="grand"><td>Grand Total</td><td class="right">Rs {{ number_format($order->displayTotal(), 2) }}</td></tr>
</table>

<div class="footer">
    <p>This is a computer-generated tax invoice. No courier partner is attached — goods are packed and dispatched by {{ $store->name() }}.</p>
    <p>Support: {{ $store->email() }} | {{ $store->displayPhone() }}</p>
</div>
</body>
</html>
