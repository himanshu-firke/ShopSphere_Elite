import React from 'react';
import { Link } from 'react-router-dom';
import { FiShoppingBag } from 'react-icons/fi';

const EmptyCart: React.FC = () => {
    return (
        <div className="text-center py-16">
            <div className="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-6">
                <FiShoppingBag className="w-8 h-8 text-gray-500" />
            </div>
            <h2 className="text-2xl font-bold text-gray-900 mb-4">
                Your cart is empty
            </h2>
            <p className="text-gray-500 mb-8 max-w-md mx-auto">
                Looks like you haven't added anything to your cart yet.
                Browse our products and find something you like!
            </p>
            <Link
                to="/products"
                className="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-primary-dark"
            >
                Start Shopping
            </Link>

            {/* Featured Categories */}
            <div className="mt-16">
                <h3 className="text-lg font-medium text-gray-900 mb-6">
                    Popular Categories
                </h3>
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <Link
                        to="/category/living"
                        className="group block text-center"
                    >
                        <div className="aspect-square bg-gray-100 rounded-lg mb-2 overflow-hidden">
                            <img
                                src="/images/categories/living-room.jpg"
                                alt="Living Room"
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </div>
                        <span className="text-sm text-gray-900 group-hover:text-primary">
                            Living Room
                        </span>
                    </Link>
                    <Link
                        to="/category/bedroom"
                        className="group block text-center"
                    >
                        <div className="aspect-square bg-gray-100 rounded-lg mb-2 overflow-hidden">
                            <img
                                src="/images/categories/bedroom.jpg"
                                alt="Bedroom"
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </div>
                        <span className="text-sm text-gray-900 group-hover:text-primary">
                            Bedroom
                        </span>
                    </Link>
                    <Link
                        to="/category/dining"
                        className="group block text-center"
                    >
                        <div className="aspect-square bg-gray-100 rounded-lg mb-2 overflow-hidden">
                            <img
                                src="/images/categories/dining-room.jpg"
                                alt="Dining Room"
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </div>
                        <span className="text-sm text-gray-900 group-hover:text-primary">
                            Dining Room
                        </span>
                    </Link>
                    <Link
                        to="/category/office"
                        className="group block text-center"
                    >
                        <div className="aspect-square bg-gray-100 rounded-lg mb-2 overflow-hidden">
                            <img
                                src="/images/categories/home-office.jpg"
                                alt="Home Office"
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </div>
                        <span className="text-sm text-gray-900 group-hover:text-primary">
                            Home Office
                        </span>
                    </Link>
                </div>
            </div>
        </div>
    );
};

export default EmptyCart; 