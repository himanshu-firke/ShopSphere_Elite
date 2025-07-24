import React from 'react';
import { Link, useLocation, Navigate } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import { FiCheckCircle, FiArrowRight } from 'react-icons/fi';
import { useCart } from '../../contexts/CartContext';

interface OrderConfirmationState {
    orderNumber: string;
    shippingInfo: {
        firstName: string;
        lastName: string;
        email: string;
        phone: string;
        address: string;
        apartment?: string;
        city: string;
        state: string;
        pincode: string;
    };
    paymentInfo: {
        method: 'card' | 'cod' | 'upi';
        cardNumber?: string;
        cardExpiry?: string;
        cardCvv?: string;
        upiId?: string;
    };
    orderItems: Array<{
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
    }>;
    orderTotal: number;
}

const OrderConfirmation: React.FC = () => {
    const location = useLocation();
    const { clearCart } = useCart();
    const orderDetails = location.state as OrderConfirmationState;

    // Clear cart on successful order
    React.useEffect(() => {
        if (orderDetails) {
            clearCart();
        }
    }, [orderDetails, clearCart]);

    // Redirect to home if no order details
    if (!orderDetails) {
        return <Navigate to="/" replace />;
    }

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    const getPaymentMethodDisplay = () => {
        switch (orderDetails.paymentInfo.method) {
            case 'card':
                return `Credit/Debit Card (ending in ${orderDetails.paymentInfo.cardNumber?.slice(-4)})`;
            case 'cod':
                return 'Cash on Delivery';
            case 'upi':
                return `UPI (${orderDetails.paymentInfo.upiId})`;
            default:
                return 'Not specified';
        }
    };

    return (
        <>
            <Helmet>
                <title>Order Confirmation - Kanha Furniture</title>
                <meta name="description" content="Your order has been successfully placed" />
            </Helmet>

            <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                {/* Success Message */}
                <div className="text-center mb-12">
                    <div className="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                        <FiCheckCircle className="w-8 h-8 text-green-600" />
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900 mb-2">
                        Thank you for your order!
                    </h1>
                    <p className="text-gray-600">
                        Your order #{orderDetails.orderNumber} has been placed successfully.
                        We'll send you an email confirmation shortly.
                    </p>
                </div>

                {/* Order Details */}
                <div className="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 className="text-lg font-medium text-gray-900 mb-6">Order Details</h2>

                    {/* Shipping Information */}
                    <div className="mb-6">
                        <h3 className="text-sm font-medium text-gray-900 mb-2">Shipping To</h3>
                        <div className="bg-gray-50 rounded-md p-4">
                            <p className="text-sm text-gray-600">
                                {orderDetails.shippingInfo.firstName} {orderDetails.shippingInfo.lastName}<br />
                                {orderDetails.shippingInfo.address}
                                {orderDetails.shippingInfo.apartment && <><br />{orderDetails.shippingInfo.apartment}</>}<br />
                                {orderDetails.shippingInfo.city}, {orderDetails.shippingInfo.state} {orderDetails.shippingInfo.pincode}<br />
                                {orderDetails.shippingInfo.phone}<br />
                                {orderDetails.shippingInfo.email}
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
                            {orderDetails.orderItems.map((item) => (
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

                    {/* Order Total */}
                    <div className="border-t pt-4">
                        <div className="flex justify-between text-base font-medium text-gray-900">
                            <span>Total</span>
                            <span>{formatPrice(orderDetails.orderTotal)}</span>
                        </div>
                    </div>
                </div>

                {/* Next Steps */}
                <div className="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">What's Next?</h2>
                    <ul className="space-y-4 text-sm text-gray-600">
                        <li className="flex items-start">
                            <span className="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-primary mt-1.5 mr-2" />
                            You'll receive an email confirmation with your order details.
                        </li>
                        <li className="flex items-start">
                            <span className="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-primary mt-1.5 mr-2" />
                            We'll process your order and notify you when it's ready for shipping.
                        </li>
                        <li className="flex items-start">
                            <span className="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-primary mt-1.5 mr-2" />
                            You can track your order status in your account dashboard.
                        </li>
                    </ul>
                </div>

                {/* Actions */}
                <div className="flex flex-col sm:flex-row justify-center gap-4">
                    <Link
                        to="/profile?tab=orders"
                        className="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        View Order Status
                        <FiArrowRight className="ml-2 -mr-1 w-5 h-5" />
                    </Link>
                    <Link
                        to="/"
                        className="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        Continue Shopping
                    </Link>
                </div>
            </div>
        </>
    );
};

export default OrderConfirmation; 