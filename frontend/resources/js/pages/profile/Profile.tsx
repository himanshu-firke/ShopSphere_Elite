import React, { useState } from 'react';
import { useLocation } from 'react-router-dom';
import { FiUser, FiPackage, FiHeart, FiMapPin, FiSettings, FiCreditCard } from 'react-icons/fi';
import { Helmet } from 'react-helmet-async';
import ProfileOverview from '../../components/profile/ProfileOverview';
import OrderHistory from '../../components/profile/OrderHistory';
import AddressBook from '../../components/profile/AddressBook';
import AccountSettings from '../../components/profile/AccountSettings';
import Wishlist from '../../components/profile/Wishlist';
import SavedCards from '../../components/profile/SavedCards';

type ProfileTab = 'overview' | 'orders' | 'wishlist' | 'addresses' | 'cards' | 'settings';

const Profile: React.FC = () => {
    const location = useLocation();
    const searchParams = new URLSearchParams(location.search);
    const defaultTab = (searchParams.get('tab') as ProfileTab) || 'overview';
    const [activeTab, setActiveTab] = useState<ProfileTab>(defaultTab);

    const tabs = [
        { id: 'overview' as const, label: 'Profile Overview', icon: FiUser },
        { id: 'orders' as const, label: 'Order History', icon: FiPackage },
        { id: 'wishlist' as const, label: 'Wishlist', icon: FiHeart },
        { id: 'addresses' as const, label: 'Address Book', icon: FiMapPin },
        { id: 'cards' as const, label: 'Saved Cards', icon: FiCreditCard },
        { id: 'settings' as const, label: 'Account Settings', icon: FiSettings }
    ];

    const getTabTitle = () => {
        const tab = tabs.find(t => t.id === activeTab);
        return tab ? tab.label : 'Profile';
    };

    const renderContent = () => {
        switch (activeTab) {
            case 'overview':
                return <ProfileOverview />;
            case 'orders':
                return <OrderHistory />;
            case 'wishlist':
                return <Wishlist />;
            case 'addresses':
                return <AddressBook />;
            case 'cards':
                return <SavedCards />;
            case 'settings':
                return <AccountSettings />;
            default:
                return <ProfileOverview />;
        }
    };

    return (
        <>
            <Helmet>
                <title>{getTabTitle()} - Kanha Furniture</title>
                <meta name="description" content={`Manage your ${getTabTitle().toLowerCase()} and account settings at Kanha Furniture.`} />
            </Helmet>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="flex flex-col md:flex-row gap-8">
                    {/* Sidebar */}
                    <div className="w-full md:w-64 flex-shrink-0">
                        <nav className="space-y-1">
                            {tabs.map((tab) => {
                                const Icon = tab.icon;
                                return (
                                    <button
                                        key={tab.id}
                                        onClick={() => setActiveTab(tab.id)}
                                        className={`w-full flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-md transition-colors ${
                                            activeTab === tab.id
                                                ? 'bg-primary text-white'
                                                : 'text-gray-900 hover:bg-gray-50'
                                        }`}
                                    >
                                        <Icon className="w-5 h-5" />
                                        {tab.label}
                                    </button>
                                );
                            })}
                        </nav>
                    </div>

                    {/* Content */}
                    <div className="flex-1 min-w-0">
                        <div className="bg-white rounded-lg shadow">
                            <div className="p-6">
                                {renderContent()}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default Profile; 