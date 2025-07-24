import React from 'react';
import { FiPackage, FiHeart, FiMapPin, FiCreditCard } from 'react-icons/fi';

// Mock data - replace with actual data from your backend
const mockUserData = {
    firstName: 'John',
    lastName: 'Doe',
    email: 'john.doe@example.com',
    phone: '+91 9876543210',
    joinDate: 'January 2024',
    stats: {
        orders: 5,
        wishlist: 12,
        addresses: 2,
        savedCards: 1
    }
};

const ProfileOverview: React.FC = () => {
    const stats = [
        { label: 'Orders', value: mockUserData.stats.orders, icon: FiPackage },
        { label: 'Wishlist Items', value: mockUserData.stats.wishlist, icon: FiHeart },
        { label: 'Saved Addresses', value: mockUserData.stats.addresses, icon: FiMapPin },
        { label: 'Saved Cards', value: mockUserData.stats.savedCards, icon: FiCreditCard }
    ];

    return (
        <div className="p-6">
            {/* User Info */}
            <div className="mb-8">
                <div className="flex items-center space-x-4">
                    <div className="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                        <span className="text-2xl font-bold text-gray-600">
                            {mockUserData.firstName[0]}{mockUserData.lastName[0]}
                        </span>
                    </div>
                    <div>
                        <h2 className="text-xl font-bold text-gray-900">
                            {mockUserData.firstName} {mockUserData.lastName}
                        </h2>
                        <p className="text-gray-500">Member since {mockUserData.joinDate}</p>
                    </div>
                </div>

                <div className="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <h3 className="text-sm font-medium text-gray-500">Email Address</h3>
                        <p className="mt-1 text-sm text-gray-900">{mockUserData.email}</p>
                    </div>
                    <div>
                        <h3 className="text-sm font-medium text-gray-500">Phone Number</h3>
                        <p className="mt-1 text-sm text-gray-900">{mockUserData.phone}</p>
                    </div>
                </div>
            </div>

            {/* Quick Stats */}
            <div>
                <h3 className="text-lg font-medium text-gray-900 mb-4">Account Overview</h3>
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    {stats.map(stat => {
                        const Icon = stat.icon;
                        return (
                            <div
                                key={stat.label}
                                className="bg-gray-50 p-4 rounded-lg text-center"
                            >
                                <Icon className="h-6 w-6 mx-auto text-primary" />
                                <div className="mt-2 text-2xl font-semibold text-gray-900">
                                    {stat.value}
                                </div>
                                <div className="mt-1 text-sm text-gray-500">{stat.label}</div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
};

export default ProfileOverview; 