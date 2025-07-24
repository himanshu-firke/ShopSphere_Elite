import React from 'react';
import { Link } from 'react-router-dom';
import { FiMinus, FiPlus, FiTrash2 } from 'react-icons/fi';

interface CartItemProps {
    id: number;
    name: string;
    image: string;
    price: number;
    originalPrice?: number;
    quantity: number;
    maxQuantity: number;
    variant?: string;
    onUpdateQuantity: (id: number, quantity: number) => void;
    onRemove: (id: number) => void;
}

const CartItem: React.FC<CartItemProps> = ({
    id,
    name,
    image,
    price,
    originalPrice,
    quantity,
    maxQuantity,
    variant,
    onUpdateQuantity,
    onRemove
}) => {
    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    const calculateDiscount = () => {
        if (!originalPrice) return null;
        const discount = ((originalPrice - price) / originalPrice) * 100;
        return Math.round(discount);
    };

    return (
        <div className="flex gap-6 py-6 border-b">
            {/* Product Image */}
            <Link to={`/products/${id}`} className="flex-shrink-0">
                <img
                    src={image}
                    alt={name}
                    className="w-24 h-24 object-cover rounded-md"
                />
            </Link>

            {/* Product Info */}
            <div className="flex-1 min-w-0">
                <div className="flex justify-between">
                    <div>
                        <Link
                            to={`/products/${id}`}
                            className="text-gray-900 font-medium hover:text-primary"
                        >
                            {name}
                        </Link>
                        {variant && (
                            <p className="mt-1 text-sm text-gray-500">
                                Variant: {variant}
                            </p>
                        )}
                    </div>
                    <div className="text-right">
                        <div className="text-gray-900 font-medium">
                            {formatPrice(price * quantity)}
                        </div>
                        {originalPrice && (
                            <>
                                <div className="text-sm text-gray-500 line-through">
                                    {formatPrice(originalPrice * quantity)}
                                </div>
                                <div className="text-sm text-green-600 font-medium">
                                    {calculateDiscount()}% Off
                                </div>
                            </>
                        )}
                    </div>
                </div>

                {/* Quantity Controls */}
                <div className="mt-4 flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <div className="flex items-center border border-gray-300 rounded-md">
                            <button
                                onClick={() => onUpdateQuantity(id, quantity - 1)}
                                disabled={quantity <= 1}
                                className="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <FiMinus className="w-4 h-4" />
                            </button>
                            <span className="px-4 py-2 text-center min-w-[3rem]">
                                {quantity}
                            </span>
                            <button
                                onClick={() => onUpdateQuantity(id, quantity + 1)}
                                disabled={quantity >= maxQuantity}
                                className="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <FiPlus className="w-4 h-4" />
                            </button>
                        </div>
                        {quantity >= maxQuantity && (
                            <span className="text-sm text-red-500">
                                Max quantity reached
                            </span>
                        )}
                    </div>

                    {/* Remove Button */}
                    <button
                        onClick={() => onRemove(id)}
                        className="text-gray-400 hover:text-red-500 transition-colors"
                    >
                        <FiTrash2 className="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    );
};

export default CartItem; 