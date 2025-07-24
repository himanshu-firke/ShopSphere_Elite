import api from './api';

export interface OrderItem {
    id: number;
    product_id: number;
    quantity: number;
    price: number;
    total: number;
    product: {
        id: number;
        name: string;
        slug: string;
        sku: string;
        primary_image?: {
            url: string;
            alt_text?: string;
        };
    };
}

export interface Order {
    id: number;
    order_number: string;
    user_id: number;
    status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded';
    payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
    payment_method: string;
    subtotal: number;
    tax_amount: number;
    shipping_amount: number;
    discount_amount: number;
    total_amount: number;
    currency: string;
    billing_address: {
        first_name: string;
        last_name: string;
        company?: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        state: string;
        postal_code: string;
        country: string;
        phone?: string;
    };
    shipping_address: {
        first_name: string;
        last_name: string;
        company?: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        state: string;
        postal_code: string;
        country: string;
        phone?: string;
    };
    items: OrderItem[];
    notes?: string;
    tracking_number?: string;
    shipped_at?: string;
    delivered_at?: string;
    created_at: string;
    updated_at: string;
}

export interface CheckoutRequest {
    billing_address: {
        first_name: string;
        last_name: string;
        company?: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        state: string;
        postal_code: string;
        country: string;
        phone?: string;
    };
    shipping_address: {
        first_name: string;
        last_name: string;
        company?: string;
        address_line_1: string;
        address_line_2?: string;
        city: string;
        state: string;
        postal_code: string;
        country: string;
        phone?: string;
    };
    payment_method: 'stripe' | 'paypal' | 'cod';
    notes?: string;
    coupon_code?: string;
}

class OrderService {
    // Create order from cart
    async createOrder(checkoutData: CheckoutRequest): Promise<Order> {
        const response = await api.post('/checkout', checkoutData);
        return response.data.data.order;
    }

    // Get user's orders
    async getOrders(page: number = 1, perPage: number = 10): Promise<{
        data: Order[];
        current_page: number;
        last_page: number;
        total: number;
    }> {
        const response = await api.get('/orders', {
            params: { page, per_page: perPage }
        });
        return response.data;
    }

    // Get single order
    async getOrder(orderNumber: string): Promise<Order> {
        const response = await api.get(`/orders/${orderNumber}`);
        return response.data.data;
    }

    // Cancel order
    async cancelOrder(orderNumber: string, reason?: string): Promise<Order> {
        const response = await api.post(`/orders/${orderNumber}/cancel`, { reason });
        return response.data.data;
    }

    // Track order
    async trackOrder(orderNumber: string): Promise<{
        order: Order;
        tracking_info: {
            status: string;
            location?: string;
            estimated_delivery?: string;
            history: Array<{
                status: string;
                location?: string;
                timestamp: string;
                description: string;
            }>;
        };
    }> {
        const response = await api.get(`/orders/${orderNumber}/track`);
        return response.data.data;
    }

    // Request return/refund
    async requestReturn(orderNumber: string, items: Array<{
        order_item_id: number;
        quantity: number;
        reason: string;
    }>, reason: string): Promise<void> {
        await api.post(`/orders/${orderNumber}/return`, {
            items,
            reason
        });
    }

    // Download invoice
    async downloadInvoice(orderNumber: string): Promise<Blob> {
        const response = await api.get(`/orders/${orderNumber}/invoice`, {
            responseType: 'blob'
        });
        return response.data;
    }
}

export default new OrderService();
