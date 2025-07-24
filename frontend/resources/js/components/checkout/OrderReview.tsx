import React from 'react';
import { FiArrowLeft } from 'react-icons/fi';

interface CartItem {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    image: string;
    quantity: number;
    variant?: {
        id: number;
        name: string;
        type: string;
    };
}

interface ShippingInfo {
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
    address: string;
    apartment?: string;
    city: string;
    state: string;
    pincode: string;
}

interface PaymentInfo {
    method: 'card' | 'cod' | 'upi';
    cardNumber?: string;
    cardExpiry?: string;
    cardCvv?: string;
    upiId?: string;
}

interface OrderReviewProps {
    shippingInfo: ShippingInfo;
    paymentInfo: PaymentInfo;
    cartItems: CartItem[];
    subtotal: number;
    shipping: number;
    discount: number;
    total: number;
    onBack: () => void;
    onPlaceOrder: () => void;
}

const OrderReview: React.FC<OrderReviewProps> = ({
    shippingInfo,
    paymentInfo,
    cartItems,
    subtotal,
    shipping,
    discount,
    total,
    onBack,
    onPlaceOrder
}) => {
    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    const getPaymentMethodDisplay = () => {
        switch (paymentInfo.method) {
            case 'card':
                return `Credit/Debit Card (ending in ${paymentInfo.cardNumber?.slice(-4)})`;
            case 'cod':
                return 'Cash on Delivery';
            case 'upi':
                return `UPI (${paymentInfo.upiId})`;
            default:
                return 'Not specified';
        }
    };

    return (
        <div className="space-y-6">
            {/* Order Summary */}
            <div className="bg-white rounded-lg shadow-sm p-6">
                <h2 className="text-lg font-medium text-gray-900 mb-6">Order Review</h2>

                {/* Shipping Information */}
                <div className="mb-6">
                    <h3 className="text-sm font-medium text-gray-900 mb-2">Shipping Information</h3>
                    <div className="bg-gray-50 rounded-md p-4">
                        <p className="text-sm text-gray-600">
                            {shippingInfo.firstName} {shippingInfo.lastName}<br />
                            {shippingInfo.address}
                            {shippingInfo.apartment && <><br />{shippingInfo.apartment}</>}<br />
                            {shippingInfo.city}, {shippingInfo.state} {shippingInfo.pincode}<br />
                            {shippingInfo.phone}<br />
                            {shippingInfo.email}
                        </p>
                    </div>
                </div>

                {/* Payment Method */}
                <div className="mb-6">
                    <h3 className="text-sm font-medium text-gray-900 mb-2">Payment Method</h3>
                    <div className="bg-gray-50 rounded-md p-4">
                        <p className="text-sm text-gray-600">
                            {getPaymentMethodDisplay()}
                        </p>
                    </div>
                </div>

                {/* Order Items */}
                <div className="mb-6">
                    <h3 className="text-sm font-medium text-gray-900 mb-4">Order Items</h3>
                    <div className="divide-y">
                        {cartItems.map((item) => (
                            <div key={`${item.id}-${item.variant?.id || 'default'}`} className="py-4 flex gap-4">
                                <img
                                    src={item.image}
                                    alt={item.name}
                                    className="w-20 h-20 object-cover rounded-md"
                                />
                                <div className="flex-1">
                                    <h4 className="text-sm font-medium text-gray-900">{item.name}</h4>
                                    {item.variant && (
                                        <p className="text-sm text-gray-500 mt-1">
                                            {item.variant.type}: {item.variant.name}
                                        </p>
                                    )}
                                    <div className="mt-1 flex justify-between">
                                        <p className="text-sm text-gray-500">Qty: {item.quantity}</p>
                                        <p className="text-sm font-medium text-gray-900">
                                            {formatPrice(item.price * item.quantity)}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Order Summary */}
                <div className="border-t pt-6">
                    <dl className="space-y-4">
                        <div className="flex justify-between">
                            <dt className="text-sm text-gray-600">Subtotal</dt>
                            <dd className="text-sm font-medium text-gray-900">{formatPrice(subtotal)}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-sm text-gray-600">Shipping</dt>
                            <dd className="text-sm font-medium text-gray-900">
                                {shipping === 0 ? 'Free' : formatPrice(shipping)}
                            </dd>
                        </div>
                        {discount > 0 && (
                            <div className="flex justify-between text-green-600">
                                <dt className="text-sm">Discount</dt>
                                <dd className="text-sm font-medium">-{formatPrice(discount)}</dd>
                            </div>
                        )}
                        <div className="flex justify-between border-t pt-4">
                            <dt className="text-base font-medium text-gray-900">Total</dt>
                            <dd className="text-base font-medium text-gray-900">{formatPrice(total)}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {/* Actions */}
            <div className="flex justify-between">
                <button
                    type="button"
                    onClick={onBack}
                    className="flex items-center text-gray-600 hover:text-gray-900"
                >
                    <FiArrowLeft className="w-5 h-5 mr-2" />
                    Back to Payment
                </button>

                <button
                    type="button"
                    onClick={onPlaceOrder}
                    className="bg-primary text-white py-3 px-6 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50"
                >
                    Place Order
                </button>
            </div>
        </div>
    );
};

export default OrderReview; 