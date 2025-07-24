import React from 'react';
import { Link } from 'react-router-dom';

interface CartSummaryProps {
    subtotal: number;
    shipping: number;
    discount?: number;
    couponCode?: string;
    onApplyCoupon?: (code: string) => void;
    onRemoveCoupon?: () => void;
}

const CartSummary: React.FC<CartSummaryProps> = ({
    subtotal,
    shipping,
    discount = 0,
    couponCode,
    onApplyCoupon,
    onRemoveCoupon
}) => {
    const [code, setCode] = React.useState('');

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    const total = subtotal + shipping - discount;

    return (
        <div className="bg-gray-50 rounded-lg p-6">
            <h2 className="text-lg font-semibold text-gray-900">Order Summary</h2>

            {/* Price Breakdown */}
            <div className="mt-6 space-y-4">
                <div className="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>{formatPrice(subtotal)}</span>
                </div>

                <div className="flex justify-between text-gray-600">
                    <span>Shipping</span>
                    {shipping === 0 ? (
                        <span className="text-green-600">Free</span>
                    ) : (
                        <span>{formatPrice(shipping)}</span>
                    )}
                </div>

                {discount > 0 && (
                    <div className="flex justify-between text-green-600">
                        <span>Discount</span>
                        <span>- {formatPrice(discount)}</span>
                    </div>
                )}

                <div className="pt-4 border-t">
                    <div className="flex justify-between text-lg font-semibold text-gray-900">
                        <span>Total</span>
                        <span>{formatPrice(total)}</span>
                    </div>
                    {discount > 0 && (
                        <p className="mt-1 text-sm text-green-600 text-right">
                            You save {formatPrice(discount)}
                        </p>
                    )}
                </div>
            </div>

            {/* Coupon Code */}
            {onApplyCoupon && (
                <div className="mt-6">
                    {couponCode ? (
                        <div className="flex items-center justify-between bg-green-50 px-4 py-3 rounded-md">
                            <div>
                                <span className="text-sm font-medium text-green-800">
                                    {couponCode}
                                </span>
                                <p className="text-xs text-green-700 mt-0.5">
                                    Coupon applied successfully
                                </p>
                            </div>
                            <button
                                onClick={onRemoveCoupon}
                                className="text-sm text-green-700 hover:text-green-800"
                            >
                                Remove
                            </button>
                        </div>
                    ) : (
                        <div className="flex gap-2">
                            <input
                                type="text"
                                value={code}
                                onChange={(e) => setCode(e.target.value)}
                                placeholder="Enter coupon code"
                                className="flex-1 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            />
                            <button
                                onClick={() => {
                                    onApplyCoupon(code);
                                    setCode('');
                                }}
                                disabled={!code}
                                className="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Apply
                            </button>
                        </div>
                    )}
                </div>
            )}

            {/* Checkout Button */}
            <Link
                to="/checkout"
                className="mt-6 block w-full bg-primary text-white text-center py-3 px-4 rounded-md hover:bg-primary-dark transition-colors"
            >
                Proceed to Checkout
            </Link>

            {/* Continue Shopping */}
            <Link
                to="/products"
                className="mt-4 block w-full text-center text-gray-600 hover:text-gray-900"
            >
                Continue Shopping
            </Link>

            {/* Payment Methods */}
            <div className="mt-6 pt-6 border-t">
                <p className="text-sm text-gray-500 text-center">We accept:</p>
                <div className="mt-2 flex justify-center gap-2">
                    <img
                        src="/images/payment/visa.png"
                        alt="Visa"
                        className="h-8"
                    />
                    <img
                        src="/images/payment/mastercard.png"
                        alt="Mastercard"
                        className="h-8"
                    />
                </div>
            </div>
        </div>
    );
};

export default CartSummary; 