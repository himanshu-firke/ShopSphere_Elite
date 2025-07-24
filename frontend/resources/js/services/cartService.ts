import api from './api';

export interface CartItem {
    id: number;
    product_id: number;
    quantity: number;
    price: number;
    product: {
        id: number;
        name: string;
        slug: string;
        price: number;
        sale_price?: number;
        sku: string;
        primary_image?: {
            url: string;
            alt_text?: string;
        };
    };
}

export interface Cart {
    id: number;
    user_id?: number;
    session_id?: string;
    items: CartItem[];
    item_count: number;
    subtotal: number;
    tax: number;
    discount: number;
    total: number;
    created_at: string;
    updated_at: string;
}

export interface AddToCartRequest {
    product_id: number;
    quantity: number;
    attributes?: Record<string, any>;
}

class CartService {
    // Get current cart
    async getCart(): Promise<Cart> {
        const response = await api.get('/cart');
        return response.data.data.cart;
    }

    // Add item to cart
    async addToCart(item: AddToCartRequest): Promise<Cart> {
        const response = await api.post('/cart/items', item);
        return response.data.data.cart;
    }

    // Update cart item quantity
    async updateCartItem(itemId: number, quantity: number): Promise<Cart> {
        const response = await api.put(`/cart/items/${itemId}`, { quantity });
        return response.data.data.cart;
    }

    // Remove item from cart
    async removeFromCart(itemId: number): Promise<Cart> {
        const response = await api.delete(`/cart/items/${itemId}`);
        return response.data.data.cart;
    }

    // Clear entire cart
    async clearCart(): Promise<void> {
        await api.delete('/cart');
    }

    // Apply coupon code
    async applyCoupon(code: string): Promise<Cart> {
        const response = await api.post('/cart/coupon', { code });
        return response.data.data.cart;
    }

    // Remove coupon
    async removeCoupon(): Promise<Cart> {
        const response = await api.delete('/cart/coupon');
        return response.data.data.cart;
    }

    // Get cart count (for header display)
    async getCartCount(): Promise<number> {
        const response = await api.get('/cart/count');
        return response.data.data.count;
    }
}

export default new CartService();
