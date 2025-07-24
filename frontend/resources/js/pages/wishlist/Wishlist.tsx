import React from 'react';
import { Link } from 'react-router-dom';
import { FiShoppingCart, FiTrash2 } from 'react-icons/fi';
import { Helmet } from 'react-helmet-async';

interface WishlistItem {
    id: number;
    name: string;
    image: string;
    price: number;
    originalPrice?: number;
    inStock: boolean;
}

// Mock data - replace with actual data from your backend
const mockWishlistItems: WishlistItem[] = [
    {
        id: 1,
        name: 'Modern Office Chair',
        image: '/images/products/chair-1.jpg',
        price: 6490,
        originalPrice: 9999,
        inStock: true
    },
    {
        id: 2,
        name: 'Luxurious 3-Seater Sofa',
        image: '/images/products/sofa-1.jpg',
        price: 24990,
        originalPrice: 34999,
        inStock: true
    },
    {
        id: 3,
        name: 'Vintage Coffee Table',
        image: '/images/products/table-1.jpg',
        price: 8990,
        originalPrice: 12999,
        inStock: false
    }
];

const Wishlist: React.FC = () => {
    const formatPrice = (amount: number) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(amount).replace('₹', '₹ ');
    };

    const handleAddToCart = (productId: number) => {
        console.log('Adding to cart:', productId);
        // Implement add to cart functionality
    };

    const handleRemoveFromWishlist = (productId: number) => {
        console.log('Removing from wishlist:', productId);
        // Implement remove from wishlist functionality
    };

    return (
        <>
            <Helmet>
                <title>My Wishlist - Kanha Furniture</title>
            </Helmet>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Breadcrumbs */}
                <nav className="mb-8">
                    <ol className="flex items-center space-x-2 text-sm">
                        <li>
                            <Link to="/" className="text-gray-500 hover:text-primary">Home</Link>
                        </li>
                        <li>
                            <span className="text-gray-400 mx-2">/</span>
                            <span className="text-gray-900">Wishlist</span>
                        </li>
                    </ol>
                </nav>

                <h1 className="text-2xl font-bold text-gray-900 mb-8">My Wishlist ({mockWishlistItems.length} items)</h1>

                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {mockWishlistItems.map(item => (
                        <div key={item.id} className="relative bg-white border rounded-lg overflow-hidden">
                            <div className="flex">
                                {/* Product Image */}
                                <div className="flex-shrink-0 w-32 h-32">
                                    <img
                                        src={item.image}
                                        alt={item.name}
                                        className="w-full h-full object-cover"
                                    />
                                </div>

                                {/* Product Details */}
                                <div className="flex-1 p-4">
                                    <Link
                                        to={`/product/${item.id}`}
                                        className="text-sm font-medium text-gray-900 hover:text-primary"
                                    >
                                        {item.name}
                                    </Link>

                                    <div className="mt-2">
                                        <span className="text-lg font-medium text-gray-900">
                                            {formatPrice(item.price)}
                                        </span>
                                        {item.originalPrice && (
                                            <span className="ml-2 text-sm text-gray-500 line-through">
                                                {formatPrice(item.originalPrice)}
                                            </span>
                                        )}
                                    </div>

                                    <div className="mt-2">
                                        <span className={`text-sm ${
                                            item.inStock ? 'text-green-600' : 'text-red-600'
                                        }`}>
                                            {item.inStock ? 'In Stock' : 'Out of Stock'}
                                        </span>
                                    </div>

                                    {/* Action Buttons */}
                                    <div className="mt-4 flex items-center space-x-4">
                                        <button
                                            onClick={() => handleAddToCart(item.id)}
                                            disabled={!item.inStock}
                                            className={`inline-flex items-center px-3 py-1.5 border rounded-md text-sm font-medium ${
                                                item.inStock
                                                    ? 'border-primary text-primary hover:bg-primary hover:text-white'
                                                    : 'border-gray-200 text-gray-400 cursor-not-allowed'
                                            }`}
                                        >
                                            <FiShoppingCart className="w-4 h-4 mr-2" />
                                            Add to Cart
                                        </button>
                                        <button
                                            onClick={() => handleRemoveFromWishlist(item.id)}
                                            className="text-gray-400 hover:text-gray-500"
                                        >
                                            <FiTrash2 className="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {mockWishlistItems.length === 0 && (
                    <div className="text-center py-12">
                        <p className="text-gray-500">Your wishlist is empty</p>
                        <Link
                            to="/products"
                            className="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                        >
                            Continue Shopping
                        </Link>
                    </div>
                )}
            </div>
        </>
    );
};

export default Wishlist; 