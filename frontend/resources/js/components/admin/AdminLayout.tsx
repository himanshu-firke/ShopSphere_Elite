import React, { useState } from 'react';
import { Link, Outlet, useLocation, Navigate } from 'react-router-dom';
import {
    FiHome,
    FiBox,
    FiShoppingBag,
    FiUsers,
    FiSettings,
    FiGrid,
    FiLogOut,
    FiMenu,
    FiX,
    FiBell,
    FiChevronDown
} from 'react-icons/fi';

// Mock admin user - replace with actual auth
const mockAdmin = {
    name: 'Admin User',
    email: 'admin@example.com',
    avatar: '/images/avatar.jpg'
};

const navigation = [
    { name: 'Dashboard', href: '/admin/dashboard', icon: FiHome },
    { name: 'Products', href: '/admin/products', icon: FiBox },
    { name: 'Categories', href: '/admin/categories', icon: FiGrid },
    { name: 'Orders', href: '/admin/orders', icon: FiShoppingBag },
    { name: 'Customers', href: '/admin/customers', icon: FiUsers },
    { name: 'Settings', href: '/admin/settings', icon: FiSettings }
];

const AdminLayout: React.FC = () => {
    const location = useLocation();
    const [isSidebarOpen, setIsSidebarOpen] = useState(true);
    const [isProfileMenuOpen, setIsProfileMenuOpen] = useState(false);
    const [isNotificationsOpen, setIsNotificationsOpen] = useState(false);

    // Mock auth check - replace with actual auth logic
    const isAuthenticated = sessionStorage.getItem('adminAuth') === 'true';
    if (!isAuthenticated) {
        // Save the attempted URL
        sessionStorage.setItem('adminRedirectUrl', location.pathname);
        return <Navigate to="/admin/login" replace />;
    }

    const toggleSidebar = () => {
        setIsSidebarOpen(!isSidebarOpen);
    };

    const handleLogout = () => {
        sessionStorage.removeItem('adminAuth');
        window.location.href = '/admin/login';
    };

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Sidebar */}
            <aside
                className={`fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-300 ease-in-out ${
                    isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
                } lg:translate-x-0 lg:static lg:inset-auto`}
            >
                <div className="flex items-center justify-between h-16 px-4 bg-gray-800">
                    <Link to="/admin/dashboard" className="text-white text-xl font-bold">
                        Kanha Admin
                    </Link>
                    <button
                        className="lg:hidden text-gray-400 hover:text-white"
                        onClick={toggleSidebar}
                    >
                        <FiX className="w-6 h-6" />
                    </button>
                </div>

                <nav className="mt-4 px-2">
                    {navigation.map((item) => {
                        const isActive = location.pathname === item.href;
                        return (
                            <Link
                                key={item.name}
                                to={item.href}
                                className={`flex items-center px-4 py-3 text-sm font-medium rounded-md mb-1 ${
                                    isActive
                                        ? 'bg-gray-800 text-white'
                                        : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                                }`}
                            >
                                <item.icon className="w-5 h-5 mr-3" />
                                {item.name}
                            </Link>
                        );
                    })}
                </nav>
            </aside>

            {/* Main Content */}
            <div className={`flex-1 ${isSidebarOpen ? 'lg:ml-64' : ''}`}>
                {/* Header */}
                <header className="bg-white shadow-sm">
                    <div className="flex items-center justify-between h-16 px-4 lg:px-8">
                        <button
                            className="lg:hidden text-gray-500 hover:text-gray-700"
                            onClick={toggleSidebar}
                        >
                            <FiMenu className="w-6 h-6" />
                        </button>

                        <div className="flex items-center space-x-4">
                            {/* Notifications */}
                            <div className="relative">
                                <button
                                    className="text-gray-500 hover:text-gray-700"
                                    onClick={() => setIsNotificationsOpen(!isNotificationsOpen)}
                                >
                                    <FiBell className="w-6 h-6" />
                                    <span className="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full" />
                                </button>

                                {isNotificationsOpen && (
                                    <div className="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50">
                                        <div className="px-4 py-2 border-b">
                                            <h3 className="text-sm font-medium text-gray-900">Notifications</h3>
                                        </div>
                                        <div className="max-h-64 overflow-y-auto">
                                            {/* Mock notifications */}
                                            <div className="px-4 py-2 hover:bg-gray-50">
                                                <p className="text-sm text-gray-900">New order received</p>
                                                <p className="text-xs text-gray-500">2 minutes ago</p>
                                            </div>
                                            <div className="px-4 py-2 hover:bg-gray-50">
                                                <p className="text-sm text-gray-900">Low stock alert: Office Chair</p>
                                                <p className="text-xs text-gray-500">1 hour ago</p>
                                            </div>
                                        </div>
                                        <div className="px-4 py-2 border-t">
                                            <Link
                                                to="/admin/notifications"
                                                className="text-sm text-primary hover:text-primary-dark"
                                            >
                                                View all notifications
                                            </Link>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Profile Dropdown */}
                            <div className="relative">
                                <button
                                    className="flex items-center space-x-2"
                                    onClick={() => setIsProfileMenuOpen(!isProfileMenuOpen)}
                                >
                                    <img
                                        src={mockAdmin.avatar}
                                        alt={mockAdmin.name}
                                        className="w-8 h-8 rounded-full"
                                    />
                                    <span className="hidden lg:block text-sm font-medium text-gray-700">
                                        {mockAdmin.name}
                                    </span>
                                    <FiChevronDown className="w-4 h-4 text-gray-500" />
                                </button>

                                {isProfileMenuOpen && (
                                    <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                        <Link
                                            to="/admin/profile"
                                            className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            Your Profile
                                        </Link>
                                        <Link
                                            to="/admin/settings"
                                            className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            Settings
                                        </Link>
                                        <button
                                            onClick={handleLogout}
                                            className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        >
                                            Sign out
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </header>

                {/* Page Content */}
                <main className="p-4 lg:p-8">
                    <Outlet />
                </main>
            </div>
        </div>
    );
};

export default AdminLayout; 