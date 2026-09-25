<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\TaxService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private TaxService $tax,
        private CouponService $coupons,
    ) {}

    public function index()
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $taxSummary = $this->tax->summarizeCart($items);
        $taxLabel = $this->tax->taxLabel($items);
        $couponResult = $this->coupons->resolve(auth()->user(), $items, $taxSummary['items_total']);
        $appliedCoupon = $couponResult['coupon'] ?? null;
        $couponDiscount = (float) ($couponResult['discount'] ?? 0);
        $couponsEnabled = $this->coupons->enabled();
        $grandTotal = max(0, $taxSummary['items_total'] - $couponDiscount);
        $freeShippingEnabled = Setting::freeShippingEnabled();
        $freeShippingMinAmount = Setting::freeShippingMinAmount();
        $freeShippingQualified = Setting::qualifiesForFreeShipping($grandTotal);
        $freeShippingRemaining = max(0, $freeShippingMinAmount - $grandTotal);

        return view('shop.cart.index', compact(
            'items',
            'subtotal',
            'taxSummary',
            'taxLabel',
            'grandTotal',
            'appliedCoupon',
            'couponDiscount',
            'couponsEnabled',
            'freeShippingEnabled',
            'freeShippingMinAmount',
            'freeShippingQualified',
            'freeShippingRemaining',
        ));
    }

    public function store(Request $request, Product $product)
    {
        $variant = null;
        if ($request->filled('variant_id')) {
            $variant = $product->variants()
                ->where('id', $request->variant_id)
                ->where('is_active', true)
                ->firstOrFail();
        }

        if ($product->product_type === 'variable' && ! $variant) {
            $message = 'Please select a size, color, or material option.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['variant_id' => [$message]],
                ], 422);
            }

            return back()->withErrors(['variant_id' => $message]);
        }

        $availableStock = $variant?->availableStock() ?? $product->stock;
        abort_unless($product->is_active && $availableStock > 0, 404);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$availableStock],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
        ]);

        $this->cart->add($product, (int) $request->quantity, $variant);

        $variant?->loadMissing(['size', 'color', 'material']);
        $label = $variant?->label();
        $message = $label
            ? $product->name.' ('.$label.') added to cart.'
            : $product->name.' added to cart.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $this->cart->count(),
            ]);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.($cartItem->variant?->stock ?? $cartItem->product->stock)],
        ]);

        $this->cart->update($cartItem, (int) $request->quantity);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $this->cart->remove($cartItem);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    private function authorizeCartItem(CartItem $cartItem): void
    {
        if (auth()->check()) {
            abort_unless($cartItem->user_id === auth()->id(), 403);
        } else {
            abort_unless($cartItem->session_id === session()->getId(), 403);
        }
    }
}
