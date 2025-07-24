import React from 'react';
import { Link } from 'react-router-dom';
import { FiHeart } from 'react-icons/fi';

interface Product {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    image: string;
    slug: string;
    rating: number;
    reviewCount: number;
    isBestSeller?: boolean;
}

const products: Product[] = [
    {
        id: 1,
        name: 'Nilkamal Willy 3 Door Wardrobe Without Mirror (New Wenge)',
        price: 14990,
        originalPrice: 25000,
        image: '/images/products/wardrobe-1.jpg',
        slug: 'nilkamal-willy-3-door-wardrobe',
        rating: 4.8,
        reviewCount: 117,
        isBestSeller: true
    },
    {
        id: 2,
        name: 'Nilkamal Arthur Double Bed (Walnut)',
        price: 10490,
        originalPrice: 18500,
        image: '/images/products/bed-1.jpg',
        slug: 'nilkamal-arthur-double-bed',
        rating: 4.9,
        reviewCount: 37,
        isBestSeller: true
    },
    {
        id: 3,
        name: 'Nilkamal Sutlej 4 Seater Dining Set (Antique Cherry)',
        price: 16990,
        originalPrice: 40100,
        image: '/images/products/dining-1.jpg',
        slug: 'nilkamal-sutlej-dining-set',
        rating: 5.0,
        reviewCount: 2
    },
    {
        id: 4,
        name: 'Nilkamal Sierra 1 Seater Manual Recliner Sofa (Brown)',
        price: 15990,
        originalPrice: 47900,
        image: '/images/products/recliner-1.jpg',
        slug: 'nilkamal-sierra-recliner',
        rating: 4.9,
        reviewCount: 51,
        isBestSeller: true
    }
];

const FeaturedProducts: React.FC = () => {
    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(price).replace('₹', '₹ '); // Add space after ₹
    };

    return (
        <section className="py-8">
            <div className="flex justify-between items-center mb-8">
                <h2 className="text-2xl text-gray-700">Best Sellers</h2>
                <Link to="/products" className="text-blue-600 hover:text-blue-800 flex items-center">
                    View All
                    <svg className="w-4 h-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {products.map((product) => (
                    <div key={product.id} className="group bg-white rounded-lg overflow-hidden border border-gray-200">
                        {/* Product Image Container */}
                        <div className="relative">
                            {/* Discount Badge */}
                            <div className="absolute top-2 left-2 bg-red-600 text-white text-sm font-medium px-2 py-1">
                                {product.originalPrice ? `${Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)}% OFF` : ''}
                            </div>

                            {/* Best Seller Badge */}
                            {product.isBestSeller && (
                                <div className="absolute top-2 left-0 bg-red-600 text-white text-xs px-3 py-1">
                                    BEST SELLER
                                </div>
                            )}
                            
                            {/* Wishlist Heart */}
                            <button className="absolute top-2 right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-gray-100 transition-colors">
                                <FiHeart className="w-5 h-5 text-gray-600" />
                            </button>
                            
                            {/* Image */}
                            <Link to={`/product/${product.slug}`} className="block">
                                <img
                                    src={product.image}
                                    alt={product.name}
                                    className="w-full aspect-square object-cover"
                                />
                            </Link>
                        </div>

                        {/* Product Info */}
                        <div className="p-4">
                            {/* Title */}
                            <Link to={`/product/${product.slug}`}>
                                <h3 className="text-sm text-gray-800 hover:text-blue-600 transition-colors line-clamp-2 min-h-[40px]">
                                    {product.name}
                                </h3>
                            </Link>

                            {/* Rating */}
                            <div className="flex items-center mt-2 mb-1">
                                <div className="flex">
                                    {[...Array(5)].map((_, index) => (
                                        <svg
                                            key={index}
                                            className={`w-4 h-4 ${
                                                index < Math.floor(product.rating)
                                                    ? 'text-yellow-400'
                                                    : 'text-gray-300'
                                            }`}
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    ))}
                                </div>
                                <span className="text-sm text-gray-500 ml-1">
                                    {product.reviewCount} {product.reviewCount === 1 ? 'review' : 'reviews'}
                                </span>
                            </div>

                            {/* Price */}
                            <div className="mt-2">
                                <div className="flex items-baseline gap-2">
                                    <span className="text-lg font-semibold">
                                        {formatPrice(product.price)}
                                    </span>
                                    {product.originalPrice && (
                                        <span className="text-sm text-gray-500 line-through">
                                            {formatPrice(product.originalPrice)}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
};

export default FeaturedProducts; 