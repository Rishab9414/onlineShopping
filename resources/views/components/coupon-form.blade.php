@props([
    'couponsEnabled' => true,
    'appliedCoupon' => null,
    'couponDiscount' => 0,
])

@if($couponsEnabled)
<div id="coupon-box" class="mb-4 pb-4 border-b border-zinc-100">
    <p class="text-sm font-semibold text-brand-black mb-2">Have a coupon?</p>

    <div id="coupon-applied" class="{{ $appliedCoupon ? '' : 'hidden' }}">
        <div class="flex items-center justify-between gap-2 bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2.5">
            <div>
                <p class="text-sm font-bold text-emerald-800" id="coupon-applied-code">{{ $appliedCoupon?->code }}</p>
                <p class="text-xs text-emerald-600">You save <span id="coupon-applied-savings">@money($couponDiscount)</span></p>
            </div>
            <button type="button" id="coupon-remove-btn"
                class="text-xs font-bold text-brand-red hover:text-red-700 px-2 py-1 rounded-lg hover:bg-red-50 transition-colors">
                Remove
            </button>
        </div>
    </div>

    <div id="coupon-input-wrap" class="{{ $appliedCoupon ? 'hidden' : '' }}">
        <div class="flex gap-2">
            <input type="text" id="coupon-code-input" placeholder="Enter coupon code"
                class="flex-1 rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm uppercase text-brand-black placeholder:text-zinc-400 focus:border-brand-red focus:ring-1 focus:ring-brand-red">
            <button type="button" id="coupon-apply-btn"
                class="shrink-0 bg-brand-red hover:bg-red-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-colors disabled:opacity-60">
                Apply
            </button>
        </div>
    </div>

    <p id="coupon-message" class="hidden mt-2 text-xs rounded-lg px-3 py-2"></p>
</div>
<script>
(() => {
    const applyBtn = document.getElementById('coupon-apply-btn');
    const removeBtn = document.getElementById('coupon-remove-btn');
    const input = document.getElementById('coupon-code-input');
    const message = document.getElementById('coupon-message');
    const appliedWrap = document.getElementById('coupon-applied');
    const inputWrap = document.getElementById('coupon-input-wrap');
    const appliedCode = document.getElementById('coupon-applied-code');
    const appliedSavings = document.getElementById('coupon-applied-savings');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!applyBtn || !csrf) return;

    const showMessage = (text, ok) => {
        if (!message) return;
        message.textContent = text;
        message.className = 'mt-2 text-xs rounded-lg px-3 py-2 ' + (ok ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700');
        message.classList.remove('hidden');
    };

    const setApplied = (code, discount) => {
        if (appliedCode) appliedCode.textContent = code || '';
        if (appliedSavings) appliedSavings.textContent = '₹' + Number(discount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        appliedWrap?.classList.toggle('hidden', !code);
        inputWrap?.classList.toggle('hidden', !!code);
        window.dispatchEvent(new CustomEvent('coupon-updated', { detail: { discount: Number(discount || 0), code } }));
    };

    applyBtn.addEventListener('click', async () => {
        const code = (input?.value || '').trim();
        if (!code) return showMessage('Enter a coupon code.', false);
        applyBtn.disabled = true;
        try {
            const res = await fetch(@json(route('cart.coupon.apply')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ code }),
            });
            const json = await res.json();
            if (json.success) {
                setApplied(json.coupon_code, json.coupon_discount);
                showMessage(json.message, true);
            } else {
                showMessage(json.message || 'Could not apply coupon.', false);
            }
        } catch (e) {
            showMessage(e.message, false);
        } finally {
            applyBtn.disabled = false;
        }
    });

    removeBtn?.addEventListener('click', async () => {
        try {
            const res = await fetch(@json(route('cart.coupon.remove')), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });
            const json = await res.json();
            setApplied('', 0);
            if (input) input.value = '';
            showMessage(json.message || 'Coupon removed.', true);
        } catch (e) {
            showMessage(e.message, false);
        }
    });
})();
</script>
@endif
