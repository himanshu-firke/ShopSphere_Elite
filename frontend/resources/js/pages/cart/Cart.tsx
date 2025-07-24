import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import { FiMinus, FiPlus, FiTrash2, FiArrowLeft } from 'react-icons/fi';
import { useCart } from '../../contexts/CartContext';

const Cart: React.FC = () => {
    const navigate = useNavigate();
    const { state, removeItem, updateQuantity, applyDiscount } = useCart();
    const [promoCode, setPromoCode] = useState('');
    const [promoError, setPromoError] = useState('');

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    const handleQuantityChange = (id: number, newQuantity: number, variantId?: number) => {
        updateQuantity(id, newQuantity, variantId);
    };

    const handleRemoveItem = (id: number, variantId?: number) => {
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            removeItem(id, variantId);
        }
    };

    const handlePromoCode = (e: React.FormEvent) => {
        e.preventDefault();
        // Mock promo code validation
        if (promoCode.toUpperCase() === 'WELCOME10') {
            const discountAmount = state.subtotal * 0.1; // 10% discount
            applyDiscount(discountAmount);
            setPromoError('');
            setPromoCode('');
        } else {
            setPromoError('Invalid promo code');
        }
    };

    if (state.items.length === 0) {
        return (
            <>
                <Helmet>
                    <title>Shopping Cart - Kanha Furniture</title>
                    <meta name="description" content="Your shopping cart at Kanha Furniture." />
                </Helmet>

                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div className="text-center py-12">
                        <h1 className="text-2xl font-bold text-gray-900 mb-4">Your Cart is Empty</h1>
                        <p className="text-gray-500 mb-8">
                            Looks like you haven't added anything to your cart yet.
                        </p>
                        <Link
                            to="/products"
                            className="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-md hover:bg-primary-dark"
                        >
                            <FiArrowLeft className="w-5 h-5" />
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Helmet>
                <title>Shopping Cart ({state.items.length}) - Kanha Furniture</title>
                <meta name="description" content="Review and manage items in your shopping cart at Kanha Furniture." />
            </Helmet>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <h1 className="text-2xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

                <div className="flex flex-col lg:flex-row gap-8">
                    {/* Cart Items */}
                    <div className="flex-1">
                        <div className="space-y-6">
                            {state.items.map((item) => (
                                <div
                                    key={`${item.id}-${item.variant?.id || 'default'}`}
                                    className="flex gap-6 p-4 bg-white rounded-lg shadow-sm"
                                >
                                    <Link to={`/product/${item.id}`} className="flex-shrink-0">
                                        <img
                                            src={item.image}
                                            alt={item.name}
                                            className="w-24 h-24 object-cover rounded-md"
                                        />
                                    </Link>

                                    <div className="flex-1 min-w-0">
                                        <div className="flex justify-between">
                                            <div>
                                                <Link
                                                    to={`/product/${item.id}`}
                                                    className="text-lg font-medium text-gray-900 hover:text-primary"
                                                >
                                                    {item.name}
                                                </Link>
                                                {item.variant && (
                                                    <p className="text-sm text-gray-500 mt-1">
                                                        {item.variant.type}: {item.variant.name}
                                                    </p>
                                                )}
                                            </div>
                                            <button
                                                onClick={() => handleRemoveItem(item.id, item.variant?.id)}
                                                className="text-gray-400 hover:text-red-500"
                                            >
                                                <FiTrash2 className="w-5 h-5" />
                                            </button>
                                        </div>

                                        <div className="flex items-center justify-between mt-4">
                                            <div className="flex items-center border rounded-md">
                                                <button
                                                    onClick={() => handleQuantityChange(item.id, item.quantity - 1, item.variant?.id)}
                                                    className="p-2 hover:bg-gray-100"
                                                    disabled={item.quantity <= 1}
                                                >
                                                    <FiMinus className="w-4 h-4" />
                                                </button>
                                                <span className="px-4 py-2 text-center min-w-[3rem]">
                                                    {item.quantity}
                                                </span>
                                                <button
                                                    onClick={() => handleQuantityChange(item.id, item.quantity + 1, item.variant?.id)}
                                                    className="p-2 hover:bg-gray-100"
                                                    disabled={item.quantity >= item.maxQuantity}
                                                >
                                                    <FiPlus className="w-4 h-4" />
                                                </button>
                                            </div>

                                            <div className="text-right">
                                                <div className="text-lg font-bold text-gray-900">
                                                    {formatPrice(item.price * item.quantity)}
                                                </div>
                                                {item.originalPrice && (
                                                    <div className="text-sm text-gray-500 line-through">
                                                        {formatPrice(item.originalPrice * item.quantity)}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Cart Summary */}
                    <div className="lg:w-96">
                        <div className="bg-white rounded-lg shadow-sm p-6">
                            <h2 className="text-lg font-bold text-gray-900 mb-6">Order Summary</h2>

                            {/* Promo Code */}
                            <form onSubmit={handlePromoCode} className="mb-6">
                                <label
                                    htmlFor="promo"
                                    className="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Promo Code
                                </label>
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        id="promo"
                                        value={promoCode}
                                        onChange={(e) => setPromoCode(e.target.value)}
                                        className="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                                        placeholder="Enter code"
                                    />
                                    <button
                                        type="submit"
                                        className="px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800"
                                    >
                                        Apply
                                    </button>
                                </div>
                                {promoError && (
                                    <p className="mt-2 text-sm text-red-600">{promoError}</p>
                                )}
                                {state.discount > 0 && (
                                    <p className="mt-2 text-sm text-green-600">
                                        Promo code applied successfully!
                                    </p>
                                )}
                            </form>

                            {/* Summary */}
                            <div className="space-y-4">
                                <div className="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span>{formatPrice(state.subtotal)}</span>
                                </div>
                                <div className="flex justify-between text-gray-600">
                                    <span>Shipping</span>
                                    <span>{state.shipping === 0 ? 'Free' : formatPrice(state.shipping)}</span>
                                </div>
                                {state.discount > 0 && (
                                    <div className="flex justify-between text-green-600">
                                        <span>Discount</span>
                                        <span>-{formatPrice(state.discount)}</span>
                                    </div>
                                )}
                                <div className="border-t pt-4">
                                    <div className="flex justify-between text-lg font-bold text-gray-900">
                                        <span>Total</span>
                                        <span>{formatPrice(state.total)}</span>
                                    </div>
                                </div>
                            </div>

                            <button
                                onClick={() => navigate('/checkout')}
                                className="w-full mt-6 px-6 py-3 bg-primary text-white rounded-md hover:bg-primary-dark"
                            >
                                Proceed to Checkout
                            </button>

                            <Link
                                to="/products"
                                className="block text-center mt-4 text-gray-600 hover:text-gray-900"
                            >
                                Continue Shopping
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default Cart; 