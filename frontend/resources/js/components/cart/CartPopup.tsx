import React from 'react';
import { Link } from 'react-router-dom';
import { FiX, FiShoppingBag } from 'react-icons/fi';
import { useCart } from '../../contexts/CartContext';

interface CartPopupProps {
    isOpen: boolean;
    onClose: () => void;
}

const CartPopup: React.FC<CartPopupProps> = ({ isOpen, onClose }) => {
    const { state, removeItem } = useCart();

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 overflow-hidden">
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                onClick={onClose}
            />

            {/* Popup */}
            <div className="fixed inset-y-0 right-0 max-w-sm w-full bg-white shadow-xl">
                <div className="flex flex-col h-full">
                    {/* Header */}
                    <div className="flex items-center justify-between p-4 border-b">
                        <h2 className="text-lg font-bold text-gray-900">Shopping Cart</h2>
                        <button
                            onClick={onClose}
                            className="p-2 text-gray-400 hover:text-gray-500"
                        >
                            <FiX className="w-5 h-5" />
                        </button>
                    </div>

                    {/* Cart Items */}
                    <div className="flex-1 overflow-y-auto p-4">
                        {state.items.length === 0 ? (
                            <div className="text-center py-8">
                                <FiShoppingBag className="w-12 h-12 mx-auto text-gray-400" />
                                <p className="mt-4 text-gray-500">Your cart is empty</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {state.items.map((item) => (
                                    <div
                                        key={`${item.id}-${item.variant?.id || 'default'}`}
                                        className="flex gap-4"
                                    >
                                        <Link to={`/product/${item.id}`} className="flex-shrink-0">
                                            <img
                                                src={item.image}
                                                alt={item.name}
                                                className="w-16 h-16 object-cover rounded-md"
                                            />
                                        </Link>
                                        <div className="flex-1 min-w-0">
                                            <Link
                                                to={`/product/${item.id}`}
                                                className="text-sm font-medium text-gray-900 hover:text-primary"
                                            >
                                                {item.name}
                                            </Link>
                                            {item.variant && (
                                                <p className="text-xs text-gray-500 mt-1">
                                                    {item.variant.type}: {item.variant.name}
                                                </p>
                                            )}
                                            <div className="flex items-center justify-between mt-2">
                                                <div className="text-sm text-gray-500">
                                                    Qty: {item.quantity}
                                                </div>
                                                <div className="text-sm font-medium text-gray-900">
                                                    {formatPrice(item.price * item.quantity)}
                                                </div>
                                            </div>
                                        </div>
                                        <button
                                            onClick={() => removeItem(item.id, item.variant?.id)}
                                            className="text-gray-400 hover:text-red-500"
                                        >
                                            <FiX className="w-5 h-5" />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Footer */}
                    {state.items.length > 0 && (
                        <div className="border-t p-4">
                            <div className="space-y-2 mb-4">
                                <div className="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal</span>
                                    <span>{formatPrice(state.subtotal)}</span>
                                </div>
                                <div className="flex justify-between text-sm text-gray-600">
                                    <span>Shipping</span>
                                    <span>{state.shipping === 0 ? 'Free' : formatPrice(state.shipping)}</span>
                                </div>
                                {state.discount > 0 && (
                                    <div className="flex justify-between text-sm text-green-600">
                                        <span>Discount</span>
                                        <span>-{formatPrice(state.discount)}</span>
                                    </div>
                                )}
                                <div className="flex justify-between text-base font-bold text-gray-900 pt-2">
                                    <span>Total</span>
                                    <span>{formatPrice(state.total)}</span>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Link
                                    to="/cart"
                                    onClick={onClose}
                                    className="block w-full px-4 py-2 text-center bg-primary text-white rounded-md hover:bg-primary-dark"
                                >
                                    View Cart
                                </Link>
                                <Link
                                    to="/checkout"
                                    onClick={onClose}
                                    className="block w-full px-4 py-2 text-center bg-gray-900 text-white rounded-md hover:bg-gray-800"
                                >
                                    Checkout
                                </Link>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default CartPopup; 