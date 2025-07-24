import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { FiHeart, FiShoppingCart } from 'react-icons/fi';
import { AiFillHeart } from 'react-icons/ai';

interface ProductCardProps {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    image: string;
    rating: number;
    isNew?: boolean;
    discount?: number;
    onAddToCart: (id: number) => void;
    onToggleWishlist: (id: number) => void;
    isWishlisted?: boolean;
}

const ProductCard: React.FC<ProductCardProps> = ({
    id,
    name,
    price,
    originalPrice,
    image,
    rating,
    isNew,
    discount,
    onAddToCart,
    onToggleWishlist,
    isWishlisted = false
}) => {
    const [isHovered, setIsHovered] = useState(false);

    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    return (
        <div 
            className="group relative bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300"
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
        >
            {/* Badges */}
            <div className="absolute top-2 left-2 z-10 flex flex-col gap-2">
                {isNew && (
                    <span className="bg-primary text-white text-xs font-semibold px-2 py-1 rounded">
                        NEW
                    </span>
                )}
                {discount && discount > 0 && (
                    <span className="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded">
                        {discount}% OFF
                    </span>
                )}
            </div>

            {/* Wishlist Button */}
            <button
                onClick={() => onToggleWishlist(id)}
                className="absolute top-2 right-2 z-10 p-2 rounded-full bg-white shadow-md hover:bg-gray-50 transition-colors duration-200"
            >
                {isWishlisted ? (
                    <AiFillHeart className="w-5 h-5 text-red-500" />
                ) : (
                    <FiHeart className="w-5 h-5 text-gray-600" />
                )}
            </button>

            {/* Product Image */}
            <Link to={`/product/${id}`} className="block relative aspect-square overflow-hidden rounded-t-lg">
                <img
                    src={image}
                    alt={name}
                    className="w-full h-full object-cover object-center transform group-hover:scale-105 transition-transform duration-300"
                />
            </Link>

            {/* Product Info */}
            <div className="p-4">
                <Link 
                    to={`/product/${id}`}
                    className="block text-gray-800 font-medium text-sm mb-1 hover:text-primary transition-colors duration-200"
                >
                    {name}
                </Link>

                <div className="flex items-center gap-2 mb-2">
                    <span className="text-lg font-bold text-gray-900">
                        {formatPrice(price)}
                    </span>
                    {originalPrice && originalPrice > price && (
                        <span className="text-sm text-gray-500 line-through">
                            {formatPrice(originalPrice)}
                        </span>
                    )}
                </div>

                {/* Rating */}
                <div className="flex items-center gap-1 mb-3">
                    {[...Array(5)].map((_, index) => (
                        <svg
                            key={index}
                            className={`w-4 h-4 ${
                                index < rating ? 'text-yellow-400' : 'text-gray-300'
                            }`}
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    ))}
                </div>

                {/* Add to Cart Button */}
                <button
                    onClick={() => onAddToCart(id)}
                    className="w-full bg-primary text-white py-2 rounded-md hover:bg-primary-dark transition-colors duration-200 flex items-center justify-center gap-2"
                >
                    <FiShoppingCart className="w-4 h-4" />
                    <span>Add to Cart</span>
                </button>
            </div>
        </div>
    );
};

export default ProductCard; 