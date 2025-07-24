import React from 'react';
import { Link } from 'react-router-dom';
import { FiHeart, FiShoppingCart } from 'react-icons/fi';
import Button from './Button';

interface ProductCardProps {
    id: number;
    title: string;
    image: string;
    price: number;
    originalPrice?: number;
    rating: number;
    reviewCount: number;
    link: string;
    isNew?: boolean;
    isBestSeller?: boolean;
    onAddToCart?: () => void;
    onAddToWishlist?: () => void;
}

const ProductCard: React.FC<ProductCardProps> = ({
    id,
    title,
    image,
    price,
    originalPrice,
    rating,
    reviewCount,
    link,
    isNew,
    isBestSeller,
    onAddToCart,
    onAddToWishlist
}) => {
    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount);
    };

    const discount = originalPrice
        ? Math.round(((originalPrice - price) / originalPrice) * 100)
        : 0;

    return (
        <div className="group">
            <div className="bg-white rounded-lg shadow-md overflow-hidden">
                <div className="relative">
                    <Link to={link}>
                        <img
                            src={image}
                            alt={title}
                            className="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                    </Link>

                    {/* Tags */}
                    <div className="absolute top-2 left-2 flex flex-col gap-2">
                        {isNew && (
                            <span className="bg-blue-500 text-white text-sm px-3 py-1 rounded-full">
                                New
                            </span>
                        )}
                        {isBestSeller && (
                            <span className="bg-yellow-500 text-white text-sm px-3 py-1 rounded-full">
                                Best Seller
                            </span>
                        )}
                        {discount > 0 && (
                            <span className="bg-red-500 text-white text-sm px-3 py-1 rounded-full">
                                {discount}% Off
                            </span>
                        )}
                    </div>

                    {/* Quick Actions */}
                    <div className="absolute top-2 right-2 flex flex-col gap-2">
                        <button
                            onClick={onAddToWishlist}
                            className="bg-white p-2 rounded-full shadow-md hover:bg-gray-100 transition-colors"
                        >
                            <FiHeart className="text-gray-600" />
                        </button>
                        <button
                            onClick={onAddToCart}
                            className="bg-white p-2 rounded-full shadow-md hover:bg-gray-100 transition-colors"
                        >
                            <FiShoppingCart className="text-gray-600" />
                        </button>
                    </div>
                </div>

                <div className="p-4">
                    <Link
                        to={link}
                        className="block text-lg font-semibold text-gray-800 hover:text-blue-500 mb-2 truncate"
                    >
                        {title}
                    </Link>

                    <div className="flex items-center mb-2">
                        <div className="flex text-yellow-400">
                            {[...Array(5)].map((_, i) => (
                                <svg
                                    key={i}
                                    className={`w-4 h-4 ${
                                        i < Math.floor(rating)
                                            ? 'fill-current'
                                            : 'text-gray-300'
                                    }`}
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                </svg>
                            ))}
                        </div>
                        <span className="text-sm text-gray-500 ml-2">
                            ({reviewCount})
                        </span>
                    </div>

                    <div className="flex items-center justify-between mb-4">
                        <div>
                            <span className="text-xl font-bold text-gray-800">
                                {formatPrice(price)}
                            </span>
                            {originalPrice && (
                                <span className="text-sm text-gray-500 line-through ml-2">
                                    {formatPrice(originalPrice)}
                                </span>
                            )}
                        </div>
                    </div>

                    <Button
                        variant="primary"
                        fullWidth
                        onClick={onAddToCart}
                    >
                        Add to Cart
                    </Button>
                </div>
            </div>
        </div>
    );
};

export default ProductCard; 