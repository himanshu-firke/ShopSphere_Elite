import React from 'react';
import { Link } from 'react-router-dom';
import { FiPhone, FiMail, FiMapPin, FiClock } from 'react-icons/fi';

const Footer: React.FC = () => {
    return (
        <footer className="bg-gray-900 text-gray-300">
            {/* Main Footer */}
            <div className="container mx-auto px-4 py-12">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    {/* Company Info */}
                    <div>
                        <h3 className="text-xl font-bold text-white mb-4">Kanha Furniture</h3>
                        <p className="text-gray-400 mb-4">
                            Your one-stop destination for premium furniture and home decor. Transform your living spaces with our curated collection.
                        </p>
                        <div className="space-y-2">
                            <p className="text-gray-400 flex items-center">
                                <FiPhone className="w-5 h-5 mr-2" />
                                1800-266-7070
                            </p>
                            <a href="mailto:support@kanhafurniture.com" className="hover:text-white">
                                support@kanhafurniture.com
                            </a>
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div>
                        <h3 className="text-xl font-bold text-white mb-4">Quick Links</h3>
                        <ul className="space-y-2">
                            <li>
                                <Link to="/about" className="hover:text-white">About Us</Link>
                            </li>
                            <li>
                                <Link to="/contact" className="hover:text-white">Contact Us</Link>
                            </li>
                            <li>
                                <Link to="/terms" className="hover:text-white">Terms & Conditions</Link>
                            </li>
                            <li>
                                <Link to="/privacy" className="hover:text-white">Privacy Policy</Link>
                            </li>
                            <li>
                                <Link to="/shipping" className="hover:text-white">Shipping Policy</Link>
                            </li>
                            <li>
                                <Link to="/returns" className="hover:text-white">Returns & Refunds</Link>
                            </li>
                            <li>
                                <Link to="/faq" className="hover:text-white">FAQ</Link>
                            </li>
                        </ul>
                    </div>

                    {/* Categories */}
                    <div>
                        <h3 className="text-xl font-bold text-white mb-4">Categories</h3>
                        <ul className="space-y-2">
                            <li>
                                <Link to="/category/living-room" className="hover:text-white">Living Room</Link>
                            </li>
                            <li>
                                <Link to="/category/bedroom" className="hover:text-white">Bedroom</Link>
                            </li>
                            <li>
                                <Link to="/category/dining-room" className="hover:text-white">Dining Room</Link>
                            </li>
                            <li>
                                <Link to="/category/home-office" className="hover:text-white">Home Office</Link>
                            </li>
                            <li>
                                <Link to="/category/kitchen" className="hover:text-white">Kitchen</Link>
                            </li>
                            <li>
                                <Link to="/category/outdoor" className="hover:text-white">Outdoor</Link>
                            </li>
                            <li>
                                <Link to="/new-arrivals" className="hover:text-white">New Arrivals</Link>
                            </li>
                        </ul>
                    </div>

                    {/* Download App */}
                    <div>
                        <h3 className="text-xl font-bold text-white mb-4">Download Our App</h3>
                        <p className="mb-6">
                            Shop on the go with our mobile app. Get exclusive app-only offers and manage
                            your orders easily.
                        </p>
                        <div className="space-y-4">
                            <a
                                href="#"
                                className="block bg-white text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
                            >
                                Download on App Store
                            </a>
                            <a
                                href="#"
                                className="block bg-white text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
                            >
                                Get it on Google Play
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {/* Bottom Bar */}
            <div className="border-t border-gray-800 pt-8 mt-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <p className="text-center text-gray-400">
                        © {new Date().getFullYear()} Kanha Furniture. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    );
};

export default Footer; 