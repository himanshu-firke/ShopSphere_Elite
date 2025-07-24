import React, { useState } from 'react';
import { FiLock, FiMail, FiBell } from 'react-icons/fi';

interface UserSettings {
    email: string;
    emailNotifications: {
        orderUpdates: boolean;
        promotions: boolean;
        newsletter: boolean;
    };
    pushNotifications: {
        orderUpdates: boolean;
        promotions: boolean;
    };
}

// Mock data - replace with actual data from your backend
const mockSettings: UserSettings = {
    email: 'john.doe@example.com',
    emailNotifications: {
        orderUpdates: true,
        promotions: false,
        newsletter: true
    },
    pushNotifications: {
        orderUpdates: true,
        promotions: false
    }
};

const AccountSettings: React.FC = () => {
    const [settings, setSettings] = useState<UserSettings>(mockSettings);
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [passwordError, setPasswordError] = useState('');

    const handleEmailNotificationChange = (key: keyof UserSettings['emailNotifications']) => {
        setSettings(prev => ({
            ...prev,
            emailNotifications: {
                ...prev.emailNotifications,
                [key]: !prev.emailNotifications[key]
            }
        }));
    };

    const handlePushNotificationChange = (key: keyof UserSettings['pushNotifications']) => {
        setSettings(prev => ({
            ...prev,
            pushNotifications: {
                ...prev.pushNotifications,
                [key]: !prev.pushNotifications[key]
            }
        }));
    };

    const handlePasswordChange = async (e: React.FormEvent) => {
        e.preventDefault();
        setPasswordError('');

        if (newPassword !== confirmPassword) {
            setPasswordError('New passwords do not match');
            return;
        }

        if (newPassword.length < 8) {
            setPasswordError('Password must be at least 8 characters long');
            return;
        }

        try {
            // TODO: Implement password change API call
            console.log('Changing password:', { currentPassword, newPassword });
            
            // Clear form
            setCurrentPassword('');
            setNewPassword('');
            setConfirmPassword('');
        } catch (error) {
            setPasswordError('Failed to change password. Please try again.');
        }
    };

    return (
        <div className="p-6 space-y-8">
            {/* Email Settings */}
            <section>
                <div className="flex items-center gap-2 mb-4">
                    <FiMail className="h-5 w-5 text-gray-400" />
                    <h2 className="text-lg font-medium text-gray-900">Email Settings</h2>
                </div>
                <div className="bg-gray-50 p-4 rounded-lg">
                    <p className="text-sm text-gray-500 mb-4">
                        Your current email address is <strong>{settings.email}</strong>
                    </p>
                    <button
                        type="button"
                        className="text-primary hover:text-primary-dark text-sm font-medium"
                    >
                        Change Email Address
                    </button>
                </div>
            </section>

            {/* Password Settings */}
            <section>
                <div className="flex items-center gap-2 mb-4">
                    <FiLock className="h-5 w-5 text-gray-400" />
                    <h2 className="text-lg font-medium text-gray-900">Password Settings</h2>
                </div>
                <form onSubmit={handlePasswordChange} className="space-y-4">
                    <div>
                        <label htmlFor="currentPassword" className="block text-sm font-medium text-gray-700">
                            Current Password
                        </label>
                        <input
                            type="password"
                            id="currentPassword"
                            value={currentPassword}
                            onChange={(e) => setCurrentPassword(e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                            required
                        />
                    </div>
                    <div>
                        <label htmlFor="newPassword" className="block text-sm font-medium text-gray-700">
                            New Password
                        </label>
                        <input
                            type="password"
                            id="newPassword"
                            value={newPassword}
                            onChange={(e) => setNewPassword(e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                            required
                        />
                    </div>
                    <div>
                        <label htmlFor="confirmPassword" className="block text-sm font-medium text-gray-700">
                            Confirm New Password
                        </label>
                        <input
                            type="password"
                            id="confirmPassword"
                            value={confirmPassword}
                            onChange={(e) => setConfirmPassword(e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                            required
                        />
                    </div>
                    {passwordError && (
                        <p className="text-sm text-red-600">{passwordError}</p>
                    )}
                    <button
                        type="submit"
                        className="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                    >
                        Change Password
                    </button>
                </form>
            </section>

            {/* Notification Settings */}
            <section>
                <div className="flex items-center gap-2 mb-4">
                    <FiBell className="h-5 w-5 text-gray-400" />
                    <h2 className="text-lg font-medium text-gray-900">Notification Settings</h2>
                </div>
                
                <div className="space-y-6">
                    {/* Email Notifications */}
                    <div>
                        <h3 className="text-sm font-medium text-gray-900 mb-4">Email Notifications</h3>
                        <div className="space-y-4">
                            <div className="flex items-start">
                                <div className="flex items-center h-5">
                                    <input
                                        id="orderUpdates"
                                        type="checkbox"
                                        checked={settings.emailNotifications.orderUpdates}
                                        onChange={() => handleEmailNotificationChange('orderUpdates')}
                                        className="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                    />
                                </div>
                                <div className="ml-3">
                                    <label htmlFor="orderUpdates" className="text-sm font-medium text-gray-700">
                                        Order Updates
                                    </label>
                                    <p className="text-sm text-gray-500">
                                        Receive updates about your order status
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start">
                                <div className="flex items-center h-5">
                                    <input
                                        id="promotions"
                                        type="checkbox"
                                        checked={settings.emailNotifications.promotions}
                                        onChange={() => handleEmailNotificationChange('promotions')}
                                        className="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                    />
                                </div>
                                <div className="ml-3">
                                    <label htmlFor="promotions" className="text-sm font-medium text-gray-700">
                                        Promotions
                                    </label>
                                    <p className="text-sm text-gray-500">
                                        Receive emails about new promotions and deals
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start">
                                <div className="flex items-center h-5">
                                    <input
                                        id="newsletter"
                                        type="checkbox"
                                        checked={settings.emailNotifications.newsletter}
                                        onChange={() => handleEmailNotificationChange('newsletter')}
                                        className="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                    />
                                </div>
                                <div className="ml-3">
                                    <label htmlFor="newsletter" className="text-sm font-medium text-gray-700">
                                        Newsletter
                                    </label>
                                    <p className="text-sm text-gray-500">
                                        Receive our weekly newsletter
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Push Notifications */}
                    <div>
                        <h3 className="text-sm font-medium text-gray-900 mb-4">Push Notifications</h3>
                        <div className="space-y-4">
                            <div className="flex items-start">
                                <div className="flex items-center h-5">
                                    <input
                                        id="pushOrderUpdates"
                                        type="checkbox"
                                        checked={settings.pushNotifications.orderUpdates}
                                        onChange={() => handlePushNotificationChange('orderUpdates')}
                                        className="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                    />
                                </div>
                                <div className="ml-3">
                                    <label htmlFor="pushOrderUpdates" className="text-sm font-medium text-gray-700">
                                        Order Updates
                                    </label>
                                    <p className="text-sm text-gray-500">
                                        Receive push notifications about your order status
                                    </p>
                                </div>
                            </div>
                            <div className="flex items-start">
                                <div className="flex items-center h-5">
                                    <input
                                        id="pushPromotions"
                                        type="checkbox"
                                        checked={settings.pushNotifications.promotions}
                                        onChange={() => handlePushNotificationChange('promotions')}
                                        className="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary"
                                    />
                                </div>
                                <div className="ml-3">
                                    <label htmlFor="pushPromotions" className="text-sm font-medium text-gray-700">
                                        Promotions
                                    </label>
                                    <p className="text-sm text-gray-500">
                                        Receive push notifications about new promotions
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    );
};

export default AccountSettings; 