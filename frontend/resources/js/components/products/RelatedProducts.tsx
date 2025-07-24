import React from 'react';
import { Link } from 'react-router-dom';
import { FiHeart } from 'react-icons/fi';

interface RelatedProduct {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    image: string;
    discount?: number;
}

interface RelatedProductsProps {
    products: RelatedProduct[];
    currentProductId: number;
}

const RelatedProducts: React.FC<RelatedProductsProps> = ({ products, currentProductId }) => {
    // Filter out current product and limit to 4 products
    const relatedProducts = products
        .filter(product => product.id !== currentProductId)
        .slice(0, 4);

    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(price).replace('₹', '₹ ');
    };

    return (
        <div className="mt-16">
            <h2 className="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {relatedProducts.map(product => (
                    <div key={product.id} className="group relative">
                        {/* Discount Badge */}
                        {product.discount && (
                            <div className="absolute top-2 left-2 z-10">
                                <span className="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded">
                                    {product.discount}% OFF
                                </span>
                            </div>
                        )}

                        {/* Wishlist Button */}
                        <button className="absolute top-2 right-2 z-10 p-2 rounded-full bg-white shadow-md hover:bg-gray-50 transition-colors duration-200">
                            <FiHeart className="w-5 h-5 text-gray-600" />
                        </button>

                        {/* Product Image */}
                        <Link to={`/product/${product.id}`} className="block relative aspect-square overflow-hidden rounded-lg bg-gray-100">
                            <img
                                src={product.image}
                                alt={product.name}
                                className="w-full h-full object-cover object-center transform group-hover:scale-105 transition-transform duration-300"
                            />
                        </Link>

                        {/* Product Info */}
                        <div className="mt-4">
                            <Link 
                                to={`/product/${product.id}`}
                                className="block text-gray-800 font-medium text-sm hover:text-primary transition-colors duration-200"
                            >
                                {product.name}
                            </Link>

                            <div className="mt-2 flex items-center gap-2">
                                <span className="text-lg font-bold text-gray-900">
                                    {formatPrice(product.price)}
                                </span>
                                {product.originalPrice && product.originalPrice > product.price && (
                                    <span className="text-sm text-gray-500 line-through">
                                        {formatPrice(product.originalPrice)}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default RelatedProducts; 