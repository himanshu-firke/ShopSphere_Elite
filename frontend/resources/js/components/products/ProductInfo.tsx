import React, { useState } from 'react';
import { FiMinus, FiPlus, FiHeart, FiShare2 } from 'react-icons/fi';

interface ProductVariant {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    inStock: boolean;
    maxQuantity?: number;
}

interface ProductInfoProps {
    name: string;
    sku: string;
    variants: ProductVariant[];
    description: string;
    onAddToCart: (variantId: number, quantity: number) => void;
    onToggleWishlist: () => void;
    isWishlisted: boolean;
}

const ProductInfo: React.FC<ProductInfoProps> = ({
    name,
    sku,
    variants,
    description,
    onAddToCart,
    onToggleWishlist,
    isWishlisted
}) => {
    const [selectedVariant, setSelectedVariant] = useState(variants[0]);
    const [quantity, setQuantity] = useState(1);

    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(price).replace('₹', '₹ ');
    };

    const handleQuantityChange = (delta: number) => {
        const maxQty = selectedVariant.maxQuantity || 10;
        setQuantity(prev => Math.max(1, Math.min(maxQty, prev + delta)));
    };

    const calculateDiscount = () => {
        if (!selectedVariant.originalPrice) return null;
        const discount = ((selectedVariant.originalPrice - selectedVariant.price) / selectedVariant.originalPrice) * 100;
        return Math.round(discount);
    };

    const maxQty = selectedVariant.maxQuantity || 10;
    const stockText = selectedVariant.inStock 
        ? maxQty > 10 
            ? 'In Stock'
            : `Only ${maxQty} left in stock`
        : 'Out of Stock';

    return (
        <div className="space-y-6">
            {/* Product Title */}
            <div>
                <h1 className="text-2xl font-bold text-gray-900">{name}</h1>
                <p className="text-sm text-gray-500 mt-1">SKU: {sku}</p>
            </div>

            {/* Price */}
            <div className="flex items-baseline gap-4">
                <span className="text-3xl font-bold text-gray-900">
                    {formatPrice(selectedVariant.price)}
                </span>
                {selectedVariant.originalPrice && (
                    <>
                        <span className="text-lg text-gray-500 line-through">
                            {formatPrice(selectedVariant.originalPrice)}
                        </span>
                        <span className="text-lg font-semibold text-green-600">
                            {calculateDiscount()}% Off
                        </span>
                    </>
                )}
            </div>

            {/* Variants */}
            {variants.length > 1 && (
                <div>
                    <h3 className="text-sm font-medium text-gray-900 mb-2">Variants</h3>
                    <div className="grid grid-cols-2 gap-2">
                        {variants.map(variant => (
                            <button
                                key={variant.id}
                                onClick={() => {
                                    setSelectedVariant(variant);
                                    setQuantity(1); // Reset quantity when changing variant
                                }}
                                className={`px-4 py-2 text-sm border rounded-md transition-colors ${
                                    selectedVariant.id === variant.id
                                        ? 'border-primary bg-primary/5 text-primary'
                                        : 'border-gray-300 hover:border-primary'
                                } ${!variant.inStock ? 'opacity-50 cursor-not-allowed' : ''}`}
                                disabled={!variant.inStock}
                            >
                                {variant.name}
                                {!variant.inStock && ' (Out of Stock)'}
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {/* Quantity */}
            <div>
                <h3 className="text-sm font-medium text-gray-900 mb-2">Quantity</h3>
                <div className="flex items-center space-x-4">
                    <div className="flex items-center border border-gray-300 rounded-md">
                        <button
                            onClick={() => handleQuantityChange(-1)}
                            className="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:hover:bg-white"
                            disabled={quantity <= 1}
                        >
                            <FiMinus className="w-4 h-4" />
                        </button>
                        <span className="px-4 py-2 text-center min-w-[3rem]">{quantity}</span>
                        <button
                            onClick={() => handleQuantityChange(1)}
                            className="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:hover:bg-white"
                            disabled={quantity >= maxQty}
                        >
                            <FiPlus className="w-4 h-4" />
                        </button>
                    </div>
                    <span className={`text-sm ${selectedVariant.inStock ? 'text-gray-500' : 'text-red-500'}`}>
                        {stockText}
                    </span>
                </div>
            </div>

            {/* Actions */}
            <div className="flex gap-4">
                <button
                    onClick={() => onAddToCart(selectedVariant.id, quantity)}
                    disabled={!selectedVariant.inStock}
                    className="flex-1 bg-primary text-white py-3 px-6 rounded-md hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Add to Cart
                </button>
                <button
                    onClick={onToggleWishlist}
                    className={`p-3 rounded-md border ${
                        isWishlisted
                            ? 'border-red-500 text-red-500'
                            : 'border-gray-300 text-gray-700'
                    } hover:bg-gray-50`}
                >
                    <FiHeart className="w-6 h-6" />
                </button>
                <button
                    onClick={() => {
                        // Implement share functionality
                        navigator.share?.({
                            title: name,
                            text: description,
                            url: window.location.href
                        });
                    }}
                    className="p-3 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
                >
                    <FiShare2 className="w-6 h-6" />
                </button>
            </div>

            {/* Description */}
            <div>
                <h3 className="text-sm font-medium text-gray-900 mb-2">Description</h3>
                <div className="prose prose-sm text-gray-500">
                    {description}
                </div>
            </div>

            {/* Delivery Info */}
            <div className="border-t pt-6">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="text-center">
                        <div className="font-medium text-gray-900">Free Delivery</div>
                        <p className="text-sm text-gray-500">On orders above ₹999</p>
                    </div>
                    <div className="text-center">
                        <div className="font-medium text-gray-900">Easy Returns</div>
                        <p className="text-sm text-gray-500">7 days return policy</p>
                    </div>
                    <div className="text-center">
                        <div className="font-medium text-gray-900">Secure Payments</div>
                        <p className="text-sm text-gray-500">All major cards accepted</p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ProductInfo; 