function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function firstValidationError(data) {
    if (!data?.errors) {
        return null;
    }

    const messages = Object.values(data.errors).flat();

    return messages.find(Boolean) || null;
}

export const Shop = {
    toast(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('shop-toast', {
            detail: { message, type },
        }));
    },

    async request(url, { method = 'POST', body } = {}) {
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
        };

        let payload = body;

        if (body && !(body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            payload = JSON.stringify(body);
        }

        const response = await fetch(url, {
            method,
            headers,
            credentials: 'same-origin',
            body: payload,
        });

        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json')
            ? await response.json()
            : {};

        if (response.status === 401) {
            window.location.href = data.redirect || '/login';
            throw new Error('Please sign in to continue.');
        }

        if (response.status === 419) {
            throw new Error(data.message || 'Session expired. Please refresh and try again.');
        }

        if (!response.ok) {
            throw new Error(firstValidationError(data) || data.message || 'Something went wrong.');
        }

        return data;
    },

    async addToCart(url, { quantity = 1, variant_id = null } = {}) {
        const payload = { quantity: Number(quantity) || 1 };

        if (variant_id) {
            payload.variant_id = variant_id;
        }

        const data = await this.request(url, { body: payload });

        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: data.count ?? 0 },
        }));

        this.toast(data.message || 'Added to cart.');

        return data;
    },

    async toggleWishlist(url) {
        const data = await this.request(url);

        window.dispatchEvent(new CustomEvent('wishlist-updated', {
            detail: { count: data.count ?? 0, wishlisted: data.wishlisted },
        }));

        this.toast(data.message || (data.wishlisted ? 'Added to wishlist.' : 'Removed from wishlist.'));

        return data;
    },
};

export function registerShopAlpine(Alpine) {
    Alpine.data('addToCartForm', (config) => ({
        url: config.url,
        variants: config.variants || [],
        selectedId: config.selectedId,
        quantity: config.quantity || 1,
        showSelect: Boolean(config.showSelect),
        loading: false,
        async submit() {
            if (this.loading) {
                return;
            }

            this.loading = true;

            try {
                await Shop.addToCart(this.url, {
                    quantity: Number(this.quantity) || 1,
                    variant_id: this.selectedId || null,
                });
            } catch (error) {
                Shop.toast(error.message || 'Could not add to cart.', 'error');
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('wishlistToggle', (config) => ({
        url: config.url,
        loginUrl: config.loginUrl,
        guest: Boolean(config.guest),
        wishlisted: Boolean(config.wishlisted),
        loading: false,
        async toggle(event) {
            event?.preventDefault();
            event?.stopPropagation();

            if (this.guest) {
                window.location.href = this.loginUrl;
                return;
            }

            if (this.loading) {
                return;
            }

            this.loading = true;

            try {
                const data = await Shop.toggleWishlist(this.url);
                this.wishlisted = Boolean(data.wishlisted);
            } catch (error) {
                Shop.toast(error.message || 'Could not update wishlist.', 'error');
            } finally {
                this.loading = false;
            }
        },
    }));
}
